<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Contact;

class ContactsSeeder extends Seeder
{
    public function run(): void
    {
        $requests = [
            [
                'name' => 'Michael Scott',
                'email' => 'michael@dundermifflin.com',
                'subject' => 'New Paper Management System',
                'message' => 'I would like to discuss a custom ERP solution for my paper company.',
            ],
            [
                'name' => 'Bruce Wayne',
                'email' => 'bruce@wayneent.com',
                'subject' => 'Security Audit',
                'message' => 'We need a full security audit of our satellite network.',
            ],
            [
                'name' => 'Tony Stark',
                'email' => 'tony@stark.com',
                'subject' => 'AI Collaboration',
                'message' => 'Interested in your machine learning models for renewable energy optimization.',
            ],
        ];

        foreach ($requests as $request) {
            Contact::create($request);
        }
    }
}
