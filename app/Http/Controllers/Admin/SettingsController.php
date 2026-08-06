<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Http\Resources\SettingsResource;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Helpers\StorageHelper;

class SettingsController extends Controller
{
    use ResponseTrait;

    public function index()
    {
        $this->authorize('view_settings');
        $settings = Setting::firstOrCreate(['id' => 1]);
        return $this->success(new SettingsResource($settings), 'Settings retrieved successfully');
    }

    public function update(Request $request)
    {
        $this->authorize('edit_settings');
        $validator = Validator::make($request->all(), [
            'our_mission' => 'nullable|string',
            'our_vision' => 'nullable|string',
            'years' => 'required|integer',
            'projects' => 'required|integer',
            'clients' => 'required|integer',
            'engineers' => 'required|integer',
            'story_title' => 'nullable|string|max:255',
            'story_subtitle' => 'nullable|string|max:255',
            'story_description' => 'nullable|string',
            'story_bullets' => 'nullable|array',
            'email' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'business_hours' => 'nullable|array',
            'emergency_support' => 'nullable|string|max:255',
            'translations' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), 422);
        }

        try {
            $data = $validator->validated();
            unset($data['translations']); // Don't try to save translations field into settings table
            
            $setting = Setting::updateOrCreate(['id' => 1], $data);

            // Handle translations
            if ($request->has('translations')) {
                foreach ($request->translations as $locale => $fields) {
                    foreach ($fields as $field => $value) {
                        $setting->setTranslation($field, $locale, $value);
                    }
                }
            }

            return $this->success(new SettingsResource($setting->fresh()), 'Setting updated successfully');
        } catch (\Exception $e) {
            Log::error('Settings update failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return $this->error('Operation failed', 500);
        }
    }



}