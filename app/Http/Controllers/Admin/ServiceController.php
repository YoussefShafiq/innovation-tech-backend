<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Helpers\StorageHelper;


class ServiceController extends Controller
{
    use ResponseTrait;

    /**
     * Create a new ServiceController instance.
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Get all services
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $this->authorize('view_services');

        $services = Service::with(['author'])->get();

        return $this->success(ServiceResource::collection($services), 'Services retrieved successfully');
    }

    /**
     * Get a specific service
     *
     * @param Service $service
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($encodedId)
    {
        $this->authorize('view_services');

        $service = Service::findByEncodedIdOrFail($encodedId);
        return $this->success(new ServiceResource($service->load(['author'])), 'Service retrieved successfully');
    }

    /**
     * Get service by slug
     *
     * @param string $slug
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBySlug($slug)
    {
        $this->authorize('view_services');

        $service = Service::with(['author'])->bySlug($slug)->first();

        if (!$service) {
            Log::warning('Service not found by slug', ['slug' => $slug]);
            return $this->error('Resource not found', 404);
        }

        return $this->success(new ServiceResource($service), 'Service retrieved successfully');
    }

    /**
     * Create a new service
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {

        $this->authorize('create_services');

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'slug' => 'required|string|max:255|unique:services,slug',
            'icon' => 'nullable|string|max:255',
            'tags' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), 422);
        }

        try {
            $data = $validator->validated();
            $data['created_by'] = Auth::id();

            $service = Service::create($data);

            // Handle translations
            if ($request->has('translations')) {
                foreach ($request->translations as $locale => $fields) {
                    foreach ($fields as $field => $value) {
                        $service->setTranslation($field, $locale, $value);
                    }
                }
            }

            return $this->success(new ServiceResource($service->load(['author'])), 'Service created successfully', 201);
        } catch (\Exception $e) {
            Log::error('Service creation failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return $this->error('Operation failed', 500);
        }
    }

    /**
     * Update a service
     *
     * @param Request $request
     * @param Service $service
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $encodedId)
    {
        $this->authorize('edit_services');

        $service = Service::findByEncodedIdOrFail($encodedId);
        
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'slug' => 'sometimes|required|string|max:255',
            'icon' => 'nullable|string|max:255',
            'tags' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
            'translations' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            Log::error('Service validation failed', ['errors' => $validator->errors()->toArray()]);
            return $this->error('Unable to process request', 422);
        }

        try {
            $data = $validator->validated();
            unset($data['translations']);

            $service->update($data);

            // Handle translations
            if ($request->has('translations')) {
                foreach ($request->translations as $locale => $fields) {
                    foreach ($fields as $field => $value) {
                        $service->setTranslation($field, $locale, $value);
                    }
                }
            }

            return $this->success(new ServiceResource($service->fresh()->load(['author'])), 'Service updated successfully');
        } catch (\Exception $e) {
            Log::error('Service update failed', ['error' => $e->getMessage(), 'service_id' => $encodedId, 'trace' => $e->getTraceAsString()]);
            return $this->error('Operation failed', 500);
        }
    }

    /**
     * Delete a service
     *
     * @param Service $service
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($encodedId)
    {
        $this->authorize('delete_services');

        try {
            $service = Service::findByEncodedIdOrFail($encodedId);
            $service->delete();
            return $this->success(null, 'Service deleted successfully');
        } catch (\Exception $e) {
            Log::error('Service deletion failed', ['error' => $e->getMessage(), 'service_id' => $encodedId]);
            return $this->error('Operation failed', 500);
        }
    }

    /**
     * Toggle service active status
     *
     * @param Service $service
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleActive($encodedId)
    {
        $this->authorize('edit_services');

        try {
            $service = Service::findByEncodedIdOrFail($encodedId);
            $service->update(['is_active' => !$service->is_active]);
            
            $status = $service->is_active ? 'activated' : 'deactivated';
            return $this->success([
                'service' => [
                    'id' => $service->encoded_id,
                    'title' => $service->title,
                    'is_active' => $service->is_active,
                ]
            ], "Service {$status} successfully");
        } catch (\Exception $e) {
            Log::error('Service status toggle failed', ['error' => $e->getMessage(), 'service_id' => $encodedId]);
            return $this->error('Operation failed', 500);
        }
    }

    /**
     * Bulk delete services
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkDelete(Request $request)
    {
        $this->authorize('delete_services');

        $validator = Validator::make($request->all(), [
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|string'
        ]);

        if ($validator->fails()) {
            Log::error('Service validation failed', ['errors' => $validator->errors()->toArray()]);
            return $this->error('Unable to process request', 422);
        }

        try {
            $deletedCount = 0;
            $errors = [];

            foreach ($request->ids as $encodedId) {
                try {
                    $service = Service::findByEncodedId($encodedId);
                    if ($service) {
                        $service->delete();
                        $deletedCount++;
                    }
                } catch (\Exception $e) {
                    Log::error('Service bulk delete item failed', ['error' => $e->getMessage(), 'service_id' => $encodedId]);
                    $errors[] = "Failed to delete service";
                }
            }

            return $this->success([
                'deleted_count' => $deletedCount,
                'errors' => $errors
            ], "{$deletedCount} service(s) deleted successfully");
        } catch (\Exception $e) {
            Log::error('Service bulk delete failed', ['error' => $e->getMessage()]);
            return $this->error('Operation failed', 500);
        }
    }

    /**
     * Bulk update service status
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkUpdateStatus(Request $request)
    {
        $this->authorize('edit_services');

        $validator = Validator::make($request->all(), [
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|string',
            'status' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            Log::error('Service validation failed', ['errors' => $validator->errors()->toArray()]);
            return $this->error('Unable to process request', 422);
        }

        try {
            $updatedCount = 0;
            $errors = [];

            foreach ($request->ids as $encodedId) {
                try {
                    $service = Service::findByEncodedId($encodedId);
                    if ($service) {
                        $service->is_active = $request->status;
                        $service->save();
                        $updatedCount++;
                    }
                } catch (\Exception $e) {
                    Log::error('Service bulk status update item failed', ['error' => $e->getMessage(), 'service_id' => $encodedId]);
                    $errors[] = "Failed to update service";
                }
            }

            $statusText = $request->status ? 'activated' : 'deactivated';
            return $this->success([
                'updated_count' => $updatedCount,
                'errors' => $errors
            ], "{$updatedCount} service(s) {$statusText} successfully");
        } catch (\Exception $e) {
            Log::error('Service bulk status update failed', ['error' => $e->getMessage()]);
            return $this->error('Operation failed', 500);
        }
    }
}
