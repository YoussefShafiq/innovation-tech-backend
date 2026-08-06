<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceResource;
use App\Models\PageContent;
use App\Models\Service;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ServiceController extends Controller
{
    use ResponseTrait;

    /**
     * Services landing page: CMS sections + active service entities.
     */
    public function index(Request $request)
    {
        $locale = $request->query('locale', 'en');
        $services = Service::with('author')->active()->get();

        $page = PageContent::findByKey('services');
        $sections = [];
        if ($page) {
            $sections = $page->getTranslation('content', $locale) ?? [];
            if (is_string($sections)) {
                $decoded = json_decode($sections, true);
                $sections = is_array($decoded) ? $decoded : [];
            }
        }

        return $this->success([
            'sections' => $sections,
            'services' => ServiceResource::collection($services),
        ], 'Services retrieved successfully');
    }

    /**
     * Get service by slug
     *
     * @param string $slug
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBySlug($slug)
    {
        $service = Service::with('author')->active()->bySlug($slug)->first();

        if (!$service) {
            Log::warning('Public service not found', ['slug' => $slug]);
            return $this->error('Resource not found', 404);
        }

        return $this->success(new ServiceResource($service), 'Service retrieved successfully');
    }
}
