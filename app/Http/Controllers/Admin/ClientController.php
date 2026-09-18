<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientResource;
use App\Helpers\StorageHelper;
use App\Models\Client;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    use ResponseTrait;

    public function index()
    {
        $this->authorize('view_clients');
        $clients = Client::ordered()->get();
        return $this->success(ClientResource::collection($clients), 'Clients retrieved successfully');
    }

    public function store(Request $request)
    {
        $this->authorize('create_clients');

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
                $data['logo'] = $request->file('logo')->store('clients', 'public');
                StorageHelper::syncToPublic($data['logo']);
            }

            $client = Client::create($data);

            return $this->success(new ClientResource($client), 'Client created successfully', 201);
        } catch (\Exception $e) {
            Log::error('Client creation failed', ['error' => $e->getMessage()]);
            return $this->error('Operation failed', 500);
        }
    }

    public function update(Request $request, string $encodedId)
    {
        $this->authorize('edit_clients');

        $client = Client::findByEncodedIdOrFail($encodedId);

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
                if ($client->logo) {
                    StorageHelper::deleteFromDirectory($client->logo);
                }
                $data['logo'] = $request->file('logo')->store('clients', 'public');
                StorageHelper::syncToPublic($data['logo']);
            }

            $client->update($data);

            return $this->success(new ClientResource($client->fresh()), 'Client updated successfully');
        } catch (\Exception $e) {
            Log::error('Client update failed', ['error' => $e->getMessage()]);
            return $this->error('Operation failed', 500);
        }
    }

    public function destroy(string $encodedId)
    {
        $this->authorize('delete_clients');

        $client = Client::findByEncodedIdOrFail($encodedId);

        try {
            if ($client->logo) {
                StorageHelper::deleteFromDirectory($client->logo);
            }
            $client->delete();
            return $this->success(null, 'Client deleted successfully');
        } catch (\Exception $e) {
            Log::error('Client delete failed', ['error' => $e->getMessage()]);
            return $this->error('Operation failed', 500);
        }
    }

    public function toggleActive(string $encodedId)
    {
        $this->authorize('edit_clients');

        $client = Client::findByEncodedIdOrFail($encodedId);
        $client->is_active = !$client->is_active;
        $client->save();

        return $this->success(new ClientResource($client), 'Client status updated successfully');
    }
}
