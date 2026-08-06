<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;
use App\Models\User;

class ServicesSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@innovation-tech.com')->first();
        $adminId = $admin ? $admin->id : 1;

        $services = [
            [
                'title' => 'Software Development',
                'description' => 'Custom software solutions tailored to your business needs, from enterprise applications to agile startups.',
                'icon' => 'MdDevices',
                'tags' => 'Web, Mobile, Enterprise',
                'is_active' => true,
                'created_by' => $adminId,
                'slug' => 'software-development'
            ],
            [
                'title' => 'Cloud Solutions',
                'description' => 'Scalable cloud infrastructure and migration services to help your business reach new heights securely.',
                'icon' => 'MdCloudDone',
                'tags' => 'AWS, Azure, DevOps',
                'is_active' => true,
                'created_by' => $adminId,
                'slug' => 'cloud-solutions'
            ],
            [
                'title' => 'Cybersecurity',
                'description' => 'Robust security audits and implementation to protect your data and stay compliant with industry standards.',
                'icon' => 'MdSecurity',
                'tags' => 'ISO 27001, SOC 2, Audit',
                'is_active' => true,
                'created_by' => $adminId,
                'slug' => 'cybersecurity'
            ],
            [
                'title' => 'AI & Data Analytics',
                'description' => 'Unlocking the power of your data with advanced machine learning models and business intelligence.',
                'icon' => 'MdAutoGraph',
                'tags' => 'Machine Learning, BI, Python',
                'is_active' => true,
                'created_by' => $adminId,
                'slug' => 'ai-data-analytics'
            ],
        ];

        foreach ($services as $serviceData) {
            $service = Service::updateOrCreate(['slug' => $serviceData['slug']], $serviceData);
            
            // Add Arabic translation
            $arData = [];
            if ($service->title === 'Software Development') {
                $arData = ['title' => 'تطوير البرمجيات', 'description' => 'حلول برمجية مخصصة مصممة لتناسب احتياجات عملك.'];
            } elseif ($service->title === 'Cloud Solutions') {
                $arData = ['title' => 'حلول السحاب', 'description' => 'بنية تحتية سحابية قابلة للتوسع وخدمات هجرة لمساعدة عملك.'];
            } elseif ($service->title === 'Cybersecurity') {
                $arData = ['title' => 'الأمن السيبراني', 'description' => 'عمليات تدقيق أمنية صارمة وتنفيذ لحماية بياناتك.'];
            } elseif ($service->title === 'AI & Data Analytics') {
                $arData = ['title' => 'الذكاء الاصطناعي وتحليل البيانات', 'description' => 'إطلاق العنان لقوة بياناتك مع نماذج تعلم الآلة المتقدمة.'];
            }

            if (!empty($arData)) {
                $service->translations()->updateOrCreate(
                    ['locale' => 'ar', 'field' => 'title'],
                    ['value' => $arData['title']]
                );
                $service->translations()->updateOrCreate(
                    ['locale' => 'ar', 'field' => 'description'],
                    ['value' => $arData['description']]
                );
            }
        }
    }
}
