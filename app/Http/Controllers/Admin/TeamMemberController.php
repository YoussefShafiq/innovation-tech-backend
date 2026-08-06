<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\TeamMemberResource;
use App\Models\TeamMember;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Helpers\StorageHelper;

class TeamMemberController extends Controller
{
    use ResponseTrait;

    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index()
    {
        $this->authorize('view_team');
        $members = TeamMember::orderBy('created_at', 'desc')->get();
        return $this->success(TeamMemberResource::collection($members), 'Team members retrieved successfully');
    }

    public function store(Request $request)
    {
        $this->authorize('create_team');

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'image' => 'nullable|image|max:4096',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), 422);
        }

        try {
            $data = $validator->safe()->only(['name', 'title', 'is_active']);

            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('team', 'public');
                StorageHelper::syncToPublic($data['image']);
            }

            $member = TeamMember::create($data);

            // Handle translations
            if ($request->has('translations')) {
                foreach ($request->translations as $locale => $fields) {
                    foreach ($fields as $field => $value) {
                        $member->setTranslation($field, $locale, $value);
                    }
                }
            }

            return $this->success(new TeamMemberResource($member), 'Team member created successfully', 201);
        } catch (\Exception $e) {
            Log::error('Team member creation failed', ['error' => $e->getMessage()]);
            return $this->error('Operation failed', 500);
        }
    }

    public function update(Request $request, $encodedId)
    {
        $this->authorize('edit_team');

        $member = TeamMember::findByEncodedIdOrFail($encodedId);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'title' => 'sometimes|required|string|max:255',
            'image' => 'nullable|image|max:4096',
            'is_active' => 'sometimes|boolean',
            'translations' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), 422);
        }

        try {
            $data = $validator->safe()->only(['name', 'title', 'is_active']);

            if ($request->hasFile('image')) {
                // Delete old image
                if ($member->image) {
                    StorageHelper::deleteFromDirectory($member->image);
                }
                $data['image'] = $request->file('image')->store('team', 'public');
                StorageHelper::syncToPublic($data['image']);
            }

            $member->update($data);

            // Handle translations
            if ($request->has('translations')) {
                foreach ($request->translations as $locale => $fields) {
                    foreach ($fields as $field => $value) {
                        $member->setTranslation($field, $locale, $value);
                    }
                }
            }

            return $this->success(new TeamMemberResource($member->fresh()), 'Team member updated successfully');
        } catch (\Exception $e) {
            Log::error('Team member update failed', ['error' => $e->getMessage()]);
            return $this->error('Operation failed', 500);
        }
    }

    public function destroy($encodedId)
    {
        $this->authorize('delete_team');

        try {
            $member = TeamMember::findByEncodedIdOrFail($encodedId);
            if ($member->image) {
                StorageHelper::deleteFromDirectory($member->image);
            }
            $member->delete();
            return $this->success(null, 'Team member deleted successfully');
        } catch (\Exception $e) {
            Log::error('Team member deletion failed', ['error' => $e->getMessage()]);
            return $this->error('Operation failed', 500);
        }
    }

    public function toggleActive($encodedId)
    {
        $this->authorize('edit_team');

        try {
            $member = TeamMember::findByEncodedIdOrFail($encodedId);
            $member->update(['is_active' => !$member->is_active]);
            $status = $member->is_active ? 'activated' : 'deactivated';
            return $this->success(new TeamMemberResource($member), "Team member {$status} successfully");
        } catch (\Exception $e) {
            Log::error('Team member toggle failed', ['error' => $e->getMessage()]);
            return $this->error('Operation failed', 500);
        }
    }

    public function bulkDelete(Request $request)
    {
        $this->authorize('delete_team');

        $validator = Validator::make($request->all(), [
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), 422);
        }

        try {
            $deletedCount = 0;
            foreach ($request->ids as $encodedId) {
                $member = TeamMember::findByEncodedId($encodedId);
                if ($member) {
                    if ($member->image) {
                        StorageHelper::deleteFromDirectory($member->image);
                    }
                    $member->delete();
                    $deletedCount++;
                }
            }
            return $this->success(['deleted_count' => $deletedCount], "{$deletedCount} team member(s) deleted successfully");
        } catch (\Exception $e) {
            Log::error('Team member bulk delete failed', ['error' => $e->getMessage()]);
            return $this->error('Operation failed', 500);
        }
    }
}
