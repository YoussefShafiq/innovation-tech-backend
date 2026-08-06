<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\PageContent;
use App\Models\Setting;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    use ResponseTrait;

    /**
     * Contact landing page: CMS sections + Settings contact info.
     */
    public function index(Request $request)
    {
        $locale = $request->query('locale', 'en');

        $page = PageContent::findByKey('contact');
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
        ], 'Contact page data retrieved successfully');
    }

    /**
     * Store a newly created contact message.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string',
        ]);

        Contact::create($validated);

        return $this->success(null, 'Contact message sent successfully');
    }
}
