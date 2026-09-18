<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientsSeeder extends Seeder
{
    public function run(): void
    {
        $clients = [
            ['name' => 'Acme Corp', 'order' => 1, 'is_active' => true],
            ['name' => 'Globex', 'order' => 2, 'is_active' => true],
            ['name' => 'Initech', 'order' => 3, 'is_active' => true],
            ['name' => 'Umbrella', 'order' => 4, 'is_active' => true],
            ['name' => 'Stark Industries', 'order' => 5, 'is_active' => true],
            ['name' => 'Wayne Enterprises', 'order' => 6, 'is_active' => true],
        ];

        foreach ($clients as $row) {
            Client::updateOrCreate(
                ['name' => $row['name']],
                [
                    'order' => $row['order'],
                    'is_active' => $row['is_active'],
                    'logo' => null,
                ]
            );
        }

        $this->command?->info('Clients seeded (names only — upload logos from dashboard).');
    }
}
