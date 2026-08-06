<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PartnerResource;
use App\Http\Resources\ServiceResource;
use App\Models\PageContent;
use App\Models\Partner;
use App\Models\Service;
use App\Models\Setting;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    use ResponseTrait;

    public function index(Request $request)
    {
        $locale = $request->query('locale', 'en');
        $settings = Setting::first();
        $services = Service::active()->limit(3)->get();
        $partners = Partner::active()->ordered()->get();

        $page = PageContent::findByKey('home');
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
            'stats' => [
                'projects_delivered' => $settings->projects ?? 0,
                'client_satisfaction' => $settings->clients ?? 0,
                'years_of_experience' => $settings->years ?? 0,
                'expert_engineers' => $settings->engineers ?? 0,
            ],
            'services' => ServiceResource::collection($services),
            'partners' => PartnerResource::collection($partners),
        ], 'Home page data retrieved successfully');
    }
}
