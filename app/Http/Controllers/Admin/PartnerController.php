<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PartnerResource;
use App\Helpers\StorageHelper;
use App\Models\Partner;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PartnerController extends Controller
{
    use ResponseTrait;

    public function index()
    {
        $this->authorize('view_partners');
        $partners = Partner::ordered()->get();
        return $this->success(PartnerResource::collection($partners), 'Partners retrieved successfully');
    }

    public function store(Request $request)
    {
        $this->authorize('create_partners');

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'sometimes|boolean',
            'logo' => 'nullable|image|max:4096',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), 422);
        }

        try {
            $data = [
                'name' => $request->input('name'),
                'order' => (int) ($request->input('order') ?? 0),
                'is_active' => $request->boolean('is_active', true),
            ];

            if ($request->hasFile('logo')) {
                $data['logo'] = $request->file('logo')->store('partners', 'public');
                StorageHelper::syncToPublic($data['logo']);
            }

            $partner = Partner::create($data);

            return $this->success(new PartnerResource($partner), 'Partner created successfully', 201);
        } catch (\Exception $e) {
            Log::error('Partner creation failed', ['error' => $e->getMessage()]);
            return $this->error('Operation failed', 500);
        }
    }

    public function update(Request $request, string $encodedId)
    {
        $this->authorize('edit_partners');

        $partner = Partner::findByEncodedIdOrFail($encodedId);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'sometimes|boolean',
            'logo' => 'nullable|image|max:4096',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), 422);
        }

        try {
            $data = [];
            if ($request->has('name')) {
                $data['name'] = $request->input('name');
            }
            if ($request->has('order')) {
                $data['order'] = (int) $request->input('order');
            }
            if ($request->has('is_active')) {
                $data['is_active'] = $request->boolean('is_active');
            }

            if ($request->hasFile('logo')) {
                if ($partner->logo) {
                    StorageHelper::deleteFromDirectory($partner->logo);
                }
                $data['logo'] = $request->file('logo')->store('partners', 'public');
                StorageHelper::syncToPublic($data['logo']);
            }

            $partner->update($data);

            return $this->success(new PartnerResource($partner->fresh()), 'Partner updated successfully');
        } catch (\Exception $e) {
            Log::error('Partner update failed', ['error' => $e->getMessage()]);
            return $this->error('Operation failed', 500);
        }
    }

    public function destroy(string $encodedId)
    {
        $this->authorize('delete_partners');

        $partner = Partner::findByEncodedIdOrFail($encodedId);

        try {
            if ($partner->logo) {
                StorageHelper::deleteFromDirectory($partner->logo);
            }
            $partner->delete();
            return $this->success(null, 'Partner deleted successfully');
        } catch (\Exception $e) {
            Log::error('Partner delete failed', ['error' => $e->getMessage()]);
            return $this->error('Operation failed', 500);
        }
    }

    public function toggleActive(string $encodedId)
    {
        $this->authorize('edit_partners');

        $partner = Partner::findByEncodedIdOrFail($encodedId);
        $partner->is_active = !$partner->is_active;
        $partner->save();

        return $this->success(new PartnerResource($partner), 'Partner status updated successfully');
    }
}
