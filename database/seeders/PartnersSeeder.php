<?php

namespace Database\Seeders;

use App\Models\Partner;
use Illuminate\Database\Seeder;

class PartnersSeeder extends Seeder
{
    public function run(): void
    {
        $partners = [
            ['name' => 'Microsoft', 'order' => 1, 'is_active' => true],
            ['name' => 'Dell Technologies', 'order' => 2, 'is_active' => true],
            ['name' => 'Cisco', 'order' => 3, 'is_active' => true],
            ['name' => 'HP Enterprise', 'order' => 4, 'is_active' => true],
            ['name' => 'VMware', 'order' => 5, 'is_active' => true],
            ['name' => 'Fortinet', 'order' => 6, 'is_active' => true],
        ];

        foreach ($partners as $row) {
            Partner::updateOrCreate(
                ['name' => $row['name']],
                [
                    'order' => $row['order'],
                    'is_active' => $row['is_active'],
                    'logo' => null,
                ]
            );
        }

        $this->command?->info('Partners seeded (names only — upload logos from dashboard).');
    }
}
