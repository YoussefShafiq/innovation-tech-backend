<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TeamMember;

class TeamMembersSeeder extends Seeder
{
    public function run(): void
    {
        $members = [
            [
                'name' => 'Sarah Johnson',
                'title' => 'Chief Executive Officer',
                'image' => 'team/ceo.jpg',
                'is_active' => true,
            ],
            [
                'name' => 'David Chen',
                'title' => 'Chief Technology Officer',
                'image' => 'team/cto.jpg',
                'is_active' => true,
            ],
            [
                'name' => 'Alex Martinez',
                'title' => 'Lead Software Engineer',
                'image' => 'team/lead.jpg',
                'is_active' => true,
            ],
            [
                'name' => 'Emily White',
                'title' => 'Security Architect',
                'image' => 'team/security.jpg',
                'is_active' => true,
            ],
        ];

        foreach ($members as $memberData) {
            $member = TeamMember::create($memberData);
            
            // Add Arabic translation
            $arData = [];
            if ($member->name === 'Sarah Johnson') {
                $arData = ['name' => 'سارة جونسون', 'title' => 'الرئيس التنفيذي'];
            } elseif ($member->name === 'David Chen') {
                $arData = ['name' => 'ديفيد تشين', 'title' => 'المدير التقني'];
            } elseif ($member->name === 'Alex Martinez') {
                $arData = ['name' => 'أليكس مارتينيز', 'title' => 'كبير مهندسي البرمجيات'];
            } elseif ($member->name === 'Emily White') {
                $arData = ['name' => 'إميلي وايت', 'title' => 'مهندس أمن سيبراني'];
            }

            if (!empty($arData)) {
                $member->translations()->create([
                    'locale' => 'ar',
                    'field' => 'name',
                    'value' => $arData['name']
                ]);
                $member->translations()->create([
                    'locale' => 'ar',
                    'field' => 'title',
                    'value' => $arData['title']
                ]);
            }
        }
    }
}
