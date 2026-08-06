<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;
use App\Helpers\ThemeColors;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $setting = Setting::updateOrCreate(
            ['id' => 1],
            [
                'years' => 12,
                'projects' => 150,
                'clients' => 98,
                'engineers' => 45,
                'story_title' => 'Our Story',
                'story_subtitle' => 'Built on Trust, Driven by Results',
                'story_description' => "Founded in 2012, Innovation Tech started as a small consultancy with a big ambition — to make enterprise-grade technology accessible to every business.\n\nOver the past decade, we've grown into a full-service technology partner serving clients across finance, healthcare, retail, and logistics. Our approach combines deep technical expertise with genuine business acumen — we don't just build systems, we solve problems.",
                'story_bullets' => [
                    '12+ years of industry experience',
                    'Team of 40+ certified engineers',
                    'Offices in 3 countries',
                    'ISO 27001 & SOC 2 certified',
                    'Agile & DevOps-first delivery',
                    'Dedicated account managers'
                ],
                'our_mission' => 'To empower enterprises with transformative technology solutions that catalyze growth, optimize operations, and redefine industry standards.',
                'our_vision' => 'To be the global benchmark for excellence in enterprise technology, bridging the gap between complex innovation and practical business success for organizations worldwide.',
                'theme_colors' => ThemeColors::defaults(),
            ]
        );

        $arData = [
            'story_title' => 'قصتنا',
            'story_subtitle' => 'بنيت على الثقة، مدفوعة بالنتائج',
            'story_description' => "تأسست إنوفيشين تيك في عام 2012 كشركة استشارية صغيرة بطموح كبير - لجعل التكنولوجيا على مستوى المؤسسات متاحة لكل عمل تجاري. على مدار العقد الماضي، نمونا لنصبح شريكاً تكنولوجياً متكامل الخدمات نخدم العملاء عبر مجالات التمويل والرعاية الصحية والتجزئة والخدمات اللوجستية.",
            'story_bullets' => json_encode([
                'أكثر من 12 عاماً من الخبرة في الصناعة',
                'فريق من 40+ مهندساً معتمداً',
                'مكاتب في 3 دول',
                'حاصلون على شهادات ISO 27001 و SOC 2',
                'تسليم يركز على Agile و DevOps',
                'مدراء حسابات مخصصون'
            ]),
            'our_mission' => 'تمكين الشركات والمؤسسات من خلال حلول تقنية تحويلية تحفز النمو، وتحسن العمليات، وتعيد تعريف معايير الصناعة.',
            'our_vision' => 'أن نكون المرجع العالمي للتميز في تكنولوجيا المؤسسات، وسد الفجوة بين الابتكار المعقد والنجاح التجاري العملي للمنظمات في جميع أنحاء العالم.'
        ];

        // Clear existing AR translations for this setting to avoid duplicates
        $setting->translations()->where('locale', 'ar')->delete();

        foreach ($arData as $field => $content) {
            $setting->translations()->create([
                'locale' => 'ar',
                'field' => $field,
                'value' => $content
            ]);
        }
    }
}
