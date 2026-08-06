<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use App\Models\Contact;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Helpers\StorageHelper;
use App\Http\Resources\ContactResource;

class ContactController extends Controller
{

    use ResponseTrait;

    public function index()
    {
        $this->authorize('view_contacts');
        $contacts = Contact::all();
        return $this->success(ContactResource::collection($contacts), 'Contacts retrieved successfully');
    }

    public function store(Request $request)
    {
        $this->authorize('create_contacts');
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), 422);
        }

        try {
            $data = $validator->validated();
            $contact = Contact::create($data);
            return $this->success(new ContactResource($contact), 'Contact created successfully', 201);
        } catch (\Exception $e) {
            Log::error('Contact creation failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return $this->error('Operation failed', 500);
        }
    }


    public function update(Request $request, $encodedId)
    {
        $this->authorize('edit_contacts');
        $contact = Contact::findByEncodedId($encodedId);

        if (!$contact) {
            Log::warning('Contact not found', ['contact_id' => $encodedId]);
            return $this->error('Resource not found', 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), 422);
        }

        try {
            $data = $validator->validated();
            $contact->update($data);
            return $this->success(new ContactResource($contact), 'Contact updated successfully');
        } catch (\Exception $e) {
            Log::error('Contact update failed', ['error' => $e->getMessage(), 'contact_id' => $encodedId]);
            return $this->error('Operation failed', 500);
        }
    }

    public function destroy($encodedId)
    {
        $this->authorize('delete_contacts');
        $contact = Contact::findByEncodedId($encodedId);

        if (!$contact) {
            Log::warning('Contact not found', ['contact_id' => $encodedId]);
            return $this->error('Resource not found', 404);
        }

        $contact->delete();
        return $this->success(null, 'Contact deleted successfully');
    }

    /**
     * Bulk delete contacts
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkDelete(Request $request)
    {
        $this->authorize('delete_contacts');

        $validator = Validator::make($request->all(), [
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), 422);
        }

        try {
            $deletedCount = 0;
            $errors = [];

            foreach ($request->ids as $encodedId) {
                try {
                    $contact = Contact::findByEncodedId($encodedId);
                    if ($contact) {
                        $contact->delete();
                        $deletedCount++;
                    }
                } catch (\Exception $e) {
                    Log::error('Contact bulk delete item failed', ['error' => $e->getMessage(), 'contact_id' => $encodedId]);
                    $errors[] = "Failed to delete contact";
                }
            }

            return $this->success([
                'deleted_count' => $deletedCount,
                'errors' => $errors
            ], "{$deletedCount} contact(s) deleted successfully");
        } catch (\Exception $e) {
            Log::error('Contact bulk delete failed', ['error' => $e->getMessage()]);
            return $this->error('Operation failed', 500);
        }
    }
}
