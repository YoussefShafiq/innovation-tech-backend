<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\ThemeColors;
use App\Models\PageContent;
use App\Models\Setting;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class LayoutController extends Controller
{
    use ResponseTrait;

    /**
     * Site chrome (navbar + footer): CMS sections + Settings contact info.
     */
    public function index(Request $request)
    {
        $locale = $request->query('locale', 'en');

        $page = PageContent::findByKey('layout');
        $sections = [];
        if ($page) {
            $sections = $page->getTranslation('content', $locale) ?? [];
            if (is_string($sections)) {
                $decoded = json_decode($sections, true);
                $sections = is_array($decoded) ? $decoded : [];
            }
        }

        $settings = Setting::query()->first();
        $address = '';
        if ($settings) {
            $address = $settings->getTranslation('address', $locale) ?? '';
            if (is_array($address)) {
                $address = '';
            }
            $address = is_string($address) ? $address : '';
        }

        return $this->success([
            'sections' => $sections,
            'info' => [
                'email' => $settings?->email ?? '',
                'phone' => $settings?->phone ?? '',
                'address' => $address,
            ],
            'theme' => ThemeColors::normalize($settings?->theme_colors),
            'logo' => $settings?->logo ? url('/storage/' . $settings->logo) : null,
        ], 'Layout data retrieved successfully');
    }
}
