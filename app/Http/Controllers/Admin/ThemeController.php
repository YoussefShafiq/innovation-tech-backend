<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\StorageHelper;
use App\Helpers\ThemeColors;
use App\Models\Setting;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ThemeController extends Controller
{
    use ResponseTrait;

    private function logoUrl(?Setting $settings): ?string
    {
        if (!$settings?->logo) {
            return null;
        }

        return url('/storage/' . $settings->logo);
    }

    public function show()
    {
        $this->authorize('view_settings');

        $settings = Setting::firstOrCreate(['id' => 1]);

        return $this->success([
            'theme' => ThemeColors::normalize($settings->theme_colors),
            'logo' => $this->logoUrl($settings),
        ], 'Theme retrieved successfully');
    }

    public function update(Request $request)
    {
        $this->authorize('edit_settings');

        $input = $request->input('theme', $request->all());
        if (!is_array($input)) {
            return $this->error('Invalid theme payload.', 422);
        }

        $validated = ThemeColors::validatePayload($input);
        if (!$validated['ok']) {
            return $this->error($validated['message'], 422);
        }

        try {
            $settings = Setting::firstOrCreate(['id' => 1]);
            $settings->theme_colors = $validated['theme'];
            $settings->save();

            return $this->success([
                'theme' => ThemeColors::normalize($settings->theme_colors),
                'logo' => $this->logoUrl($settings),
            ], 'Theme updated successfully');
        } catch (\Exception $e) {
            Log::error('Theme update failed', ['error' => $e->getMessage()]);
            return $this->error('Operation failed', 500);
        }
    }

    public function reset()
    {
        $this->authorize('edit_settings');

        try {
            $settings = Setting::firstOrCreate(['id' => 1]);
            $settings->theme_colors = ThemeColors::defaults();
            $settings->save();

            return $this->success([
                'theme' => ThemeColors::defaults(),
                'logo' => $this->logoUrl($settings),
            ], 'Theme reset to defaults successfully');
        } catch (\Exception $e) {
            Log::error('Theme reset failed', ['error' => $e->getMessage()]);
            return $this->error('Operation failed', 500);
        }
    }

    public function uploadLogo(Request $request)
    {
        $this->authorize('edit_settings');

        $validator = Validator::make($request->all(), [
            'logo' => 'required|image|max:4096',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), 422);
        }

        try {
            $settings = Setting::firstOrCreate(['id' => 1]);

            if ($settings->logo) {
                StorageHelper::deleteFromDirectory($settings->logo);
            }

            $path = $request->file('logo')->store('settings', 'public');
            StorageHelper::syncToPublic($path);

            $settings->logo = $path;
            $settings->save();

            return $this->success([
                'logo' => $this->logoUrl($settings),
            ], 'Logo uploaded successfully');
        } catch (\Exception $e) {
            Log::error('Logo upload failed', ['error' => $e->getMessage()]);
            return $this->error('Operation failed', 500);
        }
    }

    public function deleteLogo()
    {
        $this->authorize('edit_settings');

        try {
            $settings = Setting::firstOrCreate(['id' => 1]);

            if ($settings->logo) {
                StorageHelper::deleteFromDirectory($settings->logo);
                $settings->logo = null;
                $settings->save();
            }

            return $this->success([
                'logo' => null,
            ], 'Logo removed successfully');
        } catch (\Exception $e) {
            Log::error('Logo delete failed', ['error' => $e->getMessage()]);
            return $this->error('Operation failed', 500);
        }
    }
}
