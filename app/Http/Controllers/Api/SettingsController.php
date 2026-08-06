<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SettingsResource;
use App\Http\Resources\TeamMemberResource;
use App\Models\PageContent;
use App\Models\Setting;
use App\Models\TeamMember;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    use ResponseTrait;

    public function index(Request $request)
    {
        $locale = $request->query('locale', 'en');
        $settings = Setting::first();
        $team = TeamMember::active()->get();

        $page = PageContent::findByKey('about');
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
            'story' => $settings ? new SettingsResource($settings) : null,
            'team' => TeamMemberResource::collection($team),
        ], 'About page data retrieved successfully');
    }
}
