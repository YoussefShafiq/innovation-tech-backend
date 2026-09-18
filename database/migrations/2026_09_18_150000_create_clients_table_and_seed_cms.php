<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    private array $clientPermissions = [
        'view_clients',
        'create_clients',
        'edit_clients',
        'delete_clients',
    ];

    private array $defaultClientsEn = [
        'tag' => 'Clients',
        'title' => 'Our clients',
        'subtitle' => 'Businesses and organizations we deliver for — across industries and regions.',
    ];

    private array $defaultClientsAr = [
        'tag' => 'العملاء',
        'title' => 'عملاؤنا',
        'subtitle' => 'شركات ومؤسسات نعمل معها عبر قطاعات ومناطق مختلفة.',
    ];

    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('logo')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $this->seedPermissions();
        $this->mergeHomeClientsSection();
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');

        foreach ($this->clientPermissions as $name) {
            Permission::where('name', $name)->where('guard_name', 'api')->delete();
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    private function seedPermissions(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $created = [];
        foreach ($this->clientPermissions as $name) {
            $created[] = Permission::firstOrCreate(['name' => $name, 'guard_name' => 'api']);
        }

        // Attach to any role that already has view_partners
        $partnerPerm = Permission::where('name', 'view_partners')->where('guard_name', 'api')->first();
        if ($partnerPerm) {
            $roleIds = DB::table('role_has_permissions')
                ->where('permission_id', $partnerPerm->id)
                ->pluck('role_id');

            foreach ($roleIds as $roleId) {
                $role = Role::find($roleId);
                if ($role) {
                    foreach ($created as $perm) {
                        $role->givePermissionTo($perm);
                    }
                }
            }
        }

        // Users who have view_partners directly also get client permissions
        $userIds = DB::table('model_has_permissions')
            ->where('permission_id', optional($partnerPerm)->id)
            ->where('model_type', 'App\\Models\\User')
            ->pluck('model_id');

        foreach ($userIds as $userId) {
            foreach ($created as $perm) {
                DB::table('model_has_permissions')->updateOrInsert(
                    [
                        'permission_id' => $perm->id,
                        'model_type' => 'App\\Models\\User',
                        'model_id' => $userId,
                    ],
                    []
                );
            }
        }

        // Also grant all client perms to users who already have all partner perms (super admins often sync all)
        $createPartners = Permission::where('name', 'create_partners')->where('guard_name', 'api')->first();
        if ($createPartners) {
            $fullPartnerUserIds = DB::table('model_has_permissions')
                ->where('permission_id', $createPartners->id)
                ->where('model_type', 'App\\Models\\User')
                ->pluck('model_id');

            foreach ($fullPartnerUserIds as $userId) {
                foreach ($created as $perm) {
                    DB::table('model_has_permissions')->updateOrInsert(
                        [
                            'permission_id' => $perm->id,
                            'model_type' => 'App\\Models\\User',
                            'model_id' => $userId,
                        ],
                        []
                    );
                }
            }
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    private function mergeHomeClientsSection(): void
    {
        $page = DB::table('page_contents')->where('page_key', 'home')->first();
        if (!$page) {
            return;
        }

        $content = json_decode($page->content ?? '{}', true);
        if (!is_array($content)) {
            $content = [];
        }

        if (!isset($content['clients']) || !is_array($content['clients'])) {
            $content['clients'] = $this->defaultClientsEn;
            DB::table('page_contents')->where('id', $page->id)->update([
                'content' => json_encode($content, JSON_UNESCAPED_UNICODE),
                'updated_at' => now(),
            ]);
        }

        $translation = DB::table('translations')
            ->where('translatable_type', 'App\\Models\\PageContent')
            ->where('translatable_id', $page->id)
            ->where('locale', 'ar')
            ->where('field', 'content')
            ->first();

        if ($translation) {
            $arContent = json_decode($translation->value ?? '{}', true);
            if (!is_array($arContent)) {
                $arContent = [];
            }
            if (!isset($arContent['clients']) || !is_array($arContent['clients'])) {
                $arContent['clients'] = $this->defaultClientsAr;
                DB::table('translations')->where('id', $translation->id)->update([
                    'value' => json_encode($arContent, JSON_UNESCAPED_UNICODE),
                    'updated_at' => now(),
                ]);
            }
        } else {
            DB::table('translations')->insert([
                'translatable_type' => 'App\\Models\\PageContent',
                'translatable_id' => $page->id,
                'locale' => 'ar',
                'field' => 'content',
                'value' => json_encode(['clients' => $this->defaultClientsAr], JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
};
