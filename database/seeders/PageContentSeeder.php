<?php

namespace Database\Seeders;

use App\Models\PageContent;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class PageContentSeeder extends Seeder
{
    private const HERO_IMAGES = [
        'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=900&h=1100&fit=crop&q=80',
        'https://images.unsplash.com/photo-1551434678-e076c223a692?w=900&h=1100&fit=crop&q=80',
        'https://images.unsplash.com/photo-1563986768609-322da13575f3?w=900&h=1100&fit=crop&q=80',
        'https://images.unsplash.com/photo-1518770660439-4636190af475?w=900&h=1100&fit=crop&q=80',
        'https://images.unsplash.com/photo-1485827404703-89b55fcc595e?w=900&h=1100&fit=crop&q=80',
        'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=900&h=1100&fit=crop&q=80',
        'https://images.unsplash.com/photo-1504384308090-c894fdcc538d?w=900&h=1100&fit=crop&q=80',
        'https://images.unsplash.com/photo-1531482615713-2afd69097998?w=900&h=1100&fit=crop&q=80',
    ];

    public function run(): void
    {
        $this->seedHome();
        $this->seedAbout();
        $this->seedServices();
        $this->seedContact();
        $this->seedLayout();
    }

    private function seedHome(): void
    {
        $enHome = $this->loadLocaleSection('en', 'home') ?: $this->fallbackHome('en');
        $arHome = $this->loadLocaleSection('ar', 'home') ?: $this->fallbackHome('ar');

        if (isset($enHome['hero']) && is_array($enHome['hero'])) {
            $enHome['hero']['hero_images'] = self::HERO_IMAGES;
        }
        if (isset($arHome['hero']) && is_array($arHome['hero'])) {
            $arHome['hero']['hero_images'] = self::HERO_IMAGES;
        }

        $this->upsertPage('home', $enHome, $arHome);
        $this->command?->info('Home page content seeded (EN + AR).');
    }

    private function seedAbout(): void
    {
        $enAbout = $this->mapAboutFromLocale($this->loadLocaleSection('en', 'about'), 'en');
        $arAbout = $this->mapAboutFromLocale($this->loadLocaleSection('ar', 'about'), 'ar');
        $this->mergeMissionVisionBodies($enAbout, $arAbout);

        $this->upsertPage('about', $enAbout, $arAbout);
        $this->command?->info('About page content seeded (EN + AR).');
    }

    private function seedServices(): void
    {
        $enServices = $this->mapServicesFromLocale($this->loadLocaleSection('en', 'services'), 'en');
        $arServices = $this->mapServicesFromLocale($this->loadLocaleSection('ar', 'services'), 'ar');

        $this->upsertPage('services', $enServices, $arServices);
        $this->command?->info('Services page content seeded (EN + AR).');
    }

    private function mapServicesFromLocale(?array $services, string $locale): array
    {
        if (!is_array($services)) {
            return $this->fallbackServices($locale);
        }

        $pillarItems = [];
        foreach (is_array($services['profile_pillars']['items'] ?? null) ? $services['profile_pillars']['items'] : [] as $item) {
            $pillarItems[] = [
                'title' => $item['title'] ?? '',
                'desc' => $item['desc'] ?? '',
                'tag' => $item['tag'] ?? '',
            ];
        }

        $steps = [];
        if (is_array($services['process']['steps'] ?? null)) {
            foreach ($services['process']['steps'] as $i => $step) {
                $steps[] = [
                    'num' => (string) ($step['num'] ?? $step['n'] ?? str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)),
                    'title' => $step['title'] ?? '',
                    'desc' => $step['desc'] ?? '',
                ];
            }
        } else {
            for ($n = 1; $n <= 4; $n++) {
                $step = $services['process']["step{$n}"] ?? null;
                if (!is_array($step)) {
                    continue;
                }
                $steps[] = [
                    'num' => str_pad((string) $n, 2, '0', STR_PAD_LEFT),
                    'title' => $step['title'] ?? '',
                    'desc' => $step['desc'] ?? '',
                ];
            }
        }

        $why = is_array($services['why'] ?? null) ? $services['why'] : [];
        $points = [];
        if (is_array($why['points'] ?? null)) {
            foreach ($why['points'] as $p) {
                if (is_string($p) && trim($p) !== '') {
                    $points[] = $p;
                }
            }
        } else {
            for ($n = 1; $n <= 6; $n++) {
                $p = $why["point{$n}"] ?? null;
                if (is_string($p) && trim($p) !== '') {
                    $points[] = $p;
                }
            }
        }

        $defaultStats = [
            ['value' => '340%', 'labelKey' => 'roi', 'subKey' => 'roi_sub'],
            ['value' => '96%', 'labelKey' => 'delivery', 'subKey' => 'delivery_sub'],
            ['value' => '89%', 'labelKey' => 'retention', 'subKey' => 'retention_sub'],
        ];
        $stats = [];
        if (is_array($why['stats'] ?? null) && count($why['stats']) > 0) {
            foreach ($why['stats'] as $stat) {
                $stats[] = [
                    'label' => $stat['label'] ?? '',
                    'value' => $stat['value'] ?? '',
                    'sub' => $stat['sub'] ?? '',
                ];
            }
        } else {
            foreach ($defaultStats as $def) {
                $stats[] = [
                    'label' => $why[$def['labelKey']] ?? '',
                    'value' => $def['value'],
                    'sub' => $why[$def['subKey']] ?? '',
                ];
            }
        }

        return [
            'hero' => [
                'tag' => $services['hero']['tag'] ?? '',
                'title' => $services['hero']['title'] ?? '',
                'description' => $services['hero']['description'] ?? '',
            ],
            'grid' => [
                'tag' => $services['grid']['tag'] ?? '',
                'title' => $services['grid']['title'] ?? '',
                'subtitle' => $services['grid']['subtitle'] ?? '',
            ],
            'profile_pillars' => [
                'tag' => $services['profile_pillars']['tag'] ?? '',
                'title' => $services['profile_pillars']['title'] ?? '',
                'subtitle' => $services['profile_pillars']['subtitle'] ?? '',
                'items' => $pillarItems,
            ],
            'process' => [
                'tag' => $services['process']['tag'] ?? '',
                'title' => $services['process']['title'] ?? '',
                'subtitle' => $services['process']['subtitle'] ?? '',
                'steps' => $steps,
            ],
            'why' => [
                'tag' => $why['tag'] ?? '',
                'title' => $why['title'] ?? '',
                'subtitle' => $why['subtitle'] ?? '',
                'cta' => $why['cta'] ?? '',
                'points' => $points,
                'stats' => $stats,
            ],
        ];
    }

    private function fallbackServices(string $locale): array
    {
        if ($locale === 'ar') {
            return [
                'hero' => [
                    'tag' => 'ماذا نقدم',
                    'title' => 'خدماتنا',
                    'description' => 'خدمات تكنولوجية شاملة مصممة لتسريع تحولك الرقمي.',
                ],
                'grid' => [
                    'tag' => 'جميع الخدمات',
                    'title' => 'كل ما تحتاجه للنجاح',
                    'subtitle' => 'خدمات تكنولوجية شاملة تغطي دورة الحياة الرقمية لعملك.',
                ],
                'profile_pillars' => [
                    'tag' => 'الخبرة الأساسية',
                    'title' => 'ركائز الخدمة',
                    'subtitle' => 'أساس محفظتنا.',
                    'items' => [
                        ['title' => 'حلول سحابية', 'desc' => 'تصميم ونشر وهجرة للسحابة.', 'tag' => 'IaaS / PaaS / SaaS'],
                        ['title' => 'الأمن السيبراني', 'desc' => 'حماية شاملة للشبكة والأجهزة والبيانات.', 'tag' => 'الحماية والامتثال'],
                    ],
                ],
                'process' => [
                    'tag' => 'كيف نعمل',
                    'title' => 'عمليتنا المثبتة',
                    'subtitle' => 'نهج مهيكل وشفاف.',
                    'steps' => [
                        ['num' => '01', 'title' => 'الاكتشاف', 'desc' => 'نغوص في أهداف عملك وتحدياتك.'],
                        ['num' => '02', 'title' => 'الاستراتيجية', 'desc' => 'خارطة طريق مخصصة بمعالم واضحة.'],
                        ['num' => '03', 'title' => 'البناء', 'desc' => 'تنفيذ مرن مع متابعة مستمرة.'],
                        ['num' => '04', 'title' => 'الإطلاق والنمو', 'desc' => 'نشر ودعم ثم توسّع.'],
                    ],
                ],
                'why' => [
                    'tag' => 'لماذا تختارنا',
                    'title' => 'الفرق يكمن في التفاصيل',
                    'subtitle' => 'شريكك التكنولوجي على المدى الطويل.',
                    'cta' => 'ابدأ محادثة',
                    'points' => [
                        'مدير مشروع مخصص من اليوم الأول',
                        'تقارير تقدم وعروض أسبوعية',
                        'ملكية الكود المصدري لك دائماً',
                    ],
                    'stats' => [
                        ['label' => 'متوسط عائد المشروع', 'value' => '340%', 'sub' => 'بناءً على استطلاعات 2023'],
                        ['label' => 'معدل التسليم في الوقت', 'value' => '96%', 'sub' => 'عبر جميع أنواع المشاريع'],
                        ['label' => 'معدل الاحتفاظ بالعملاء', 'value' => '89%', 'sub' => 'العملاء يعودون لمزيد من المشاريع'],
                    ],
                ],
            ];
        }

        return [
            'hero' => [
                'tag' => 'What We Offer',
                'title' => 'Our Services',
                'description' => 'End-to-end technology services designed to accelerate your digital transformation.',
            ],
            'grid' => [
                'tag' => 'All Services',
                'title' => 'Everything You Need to Succeed',
                'subtitle' => 'Comprehensive technology services that cover the entire digital lifecycle of your business.',
            ],
            'profile_pillars' => [
                'tag' => 'Core Expertise',
                'title' => 'Service Pillars from IN Tech Profile',
                'subtitle' => 'These are the foundation of our portfolio as outlined in the official company profile.',
                'items' => [
                    ['title' => 'Cloud Solutions', 'desc' => 'Design, deployment, and migration for Microsoft-based cloud environments.', 'tag' => 'IaaS / PaaS / SaaS'],
                    ['title' => 'Cyber Security', 'desc' => 'End-to-end protection for your network, endpoints, and data.', 'tag' => 'Protection & Compliance'],
                ],
            ],
            'process' => [
                'tag' => 'How We Work',
                'title' => 'Our Proven Process',
                'subtitle' => 'A structured, transparent approach that delivers on time and on budget.',
                'steps' => [
                    ['num' => '01', 'title' => 'Discovery', 'desc' => 'We deep-dive into your business goals and challenges.'],
                    ['num' => '02', 'title' => 'Strategy', 'desc' => 'A tailored solution roadmap with clear milestones.'],
                    ['num' => '03', 'title' => 'Build', 'desc' => 'Agile delivery with regular demos.'],
                    ['num' => '04', 'title' => 'Launch & Grow', 'desc' => 'Deploy, monitor, support, then scale.'],
                ],
            ],
            'why' => [
                'tag' => 'Why Choose Us',
                'title' => 'The Difference is in the Details',
                'subtitle' => 'We become your technology partner for the long haul.',
                'cta' => 'Start a Conversation',
                'points' => [
                    'Dedicated project manager from day one',
                    'Weekly progress reports & demos',
                    'Source code ownership — always yours',
                ],
                'stats' => [
                    ['label' => 'Average Project ROI', 'value' => '340%', 'sub' => 'Based on 2023 client surveys'],
                    ['label' => 'On-time Delivery Rate', 'value' => '96%', 'sub' => 'Across all project types'],
                    ['label' => 'Client Retention Rate', 'value' => '89%', 'sub' => 'Clients return for more projects'],
                ],
            ],
        ];
    }

    private function seedContact(): void
    {
        $enContact = $this->mapContactFromLocale($this->loadLocaleSection('en', 'contact'), 'en');
        $arContact = $this->mapContactFromLocale($this->loadLocaleSection('ar', 'contact'), 'ar');

        $this->upsertPage('contact', $enContact, $arContact);
        $this->command?->info('Contact page content seeded (EN + AR).');
    }

    private function mapContactFromLocale(?array $contact, string $locale): array
    {
        if (!is_array($contact)) {
            return $this->fallbackContact($locale);
        }

        $hero = is_array($contact['hero'] ?? null) ? $contact['hero'] : [];
        $info = is_array($contact['info'] ?? null) ? $contact['info'] : [];
        $form = is_array($contact['form'] ?? null) ? $contact['form'] : [];

        return [
            'hero' => [
                'eyebrow' => $hero['eyebrow'] ?? '',
                'title' => $hero['title'] ?? '',
                'description' => $hero['description'] ?? '',
            ],
            'info' => [
                'tag' => $info['tag'] ?? '',
                'title' => $info['title'] ?? '',
                'subtitle' => $info['subtitle'] ?? '',
                'email_label' => $info['email_label'] ?? '',
                'phone_label' => $info['phone_label'] ?? '',
                'office_label' => $info['office_label'] ?? '',
            ],
            'form' => [
                'title' => $form['title'] ?? '',
                'subtitle' => $form['subtitle'] ?? '',
                'name_label' => $form['name_label'] ?? '',
                'name_placeholder' => $form['name_placeholder'] ?? '',
                'email_label' => $form['email_label'] ?? '',
                'email_placeholder' => $form['email_placeholder'] ?? '',
                'subject_label' => $form['subject_label'] ?? '',
                'subject_placeholder' => $form['subject_placeholder'] ?? '',
                'message_label' => $form['message_label'] ?? '',
                'message_placeholder' => $form['message_placeholder'] ?? '',
                'sent_title' => $form['sent_title'] ?? '',
                'sent_desc' => $form['sent_desc'] ?? '',
                'send_another' => $form['send_another'] ?? '',
                'sending' => $form['sending'] ?? '',
                'sending_btn' => $form['sending_btn'] ?? '',
                'submit' => $form['submit'] ?? '',
                'success_toast' => $form['success_toast'] ?? '',
                'error_toast' => $form['error_toast'] ?? '',
                'subject_default' => $form['subject_default'] ?? '',
            ],
        ];
    }

    private function fallbackContact(string $locale): array
    {
        if ($locale === 'ar') {
            return [
                'hero' => [
                    'eyebrow' => 'تواصل معنا',
                    'title' => 'دعنا نتحدث',
                    'description' => 'هل لديك مشروع أو تحدٍ؟ نود أن نسمع منك.',
                ],
                'info' => [
                    'tag' => 'معلومات التواصل',
                    'title' => 'نحن هنا للمساعدة',
                    'subtitle' => 'تواصل عبر أي قناة — عادةً نرد خلال ساعات.',
                    'email_label' => 'البريد',
                    'phone_label' => 'الهاتف',
                    'office_label' => 'المكتب',
                ],
                'form' => [
                    'title' => 'أرسل رسالة',
                    'subtitle' => 'املأ النموذج وسنرد عليك قريباً.',
                    'name_label' => 'الاسم الكامل',
                    'name_placeholder' => 'أحمد محمد',
                    'email_label' => 'البريد الإلكتروني',
                    'email_placeholder' => 'ahmed@company.com',
                    'subject_label' => 'الموضوع',
                    'subject_placeholder' => 'كيف يمكننا المساعدة؟',
                    'message_label' => 'الرسالة',
                    'message_placeholder' => 'أخبرنا عن مشروعك أو تحديك...',
                    'sent_title' => 'تم الإرسال!',
                    'sent_desc' => 'شكراً لتواصلك. سنرد خلال 24 ساعة عمل.',
                    'send_another' => 'أرسل أخرى',
                    'sending' => 'جارٍ إرسال رسالتك...',
                    'sending_btn' => 'جارٍ الإرسال...',
                    'submit' => 'إرسال الرسالة',
                    'success_toast' => 'شكراً — سنرد خلال 24 ساعة عمل.',
                    'error_toast' => 'فشل الإرسال. حاول مرة أخرى.',
                    'subject_default' => 'استفسار عام',
                ],
            ];
        }

        return [
            'hero' => [
                'eyebrow' => 'Get in Touch',
                'title' => "Let's Talk",
                'description' => "Have a project in mind? A challenge to solve? Or just want to explore what's possible? We'd love to hear from you.",
            ],
            'info' => [
                'tag' => 'Contact Info',
                'title' => "We're Here to Help",
                'subtitle' => 'Reach out through any channel — our team typically responds within a few hours.',
                'email_label' => 'Email',
                'phone_label' => 'Phone',
                'office_label' => 'Office',
            ],
            'form' => [
                'title' => 'Send Us a Message',
                'subtitle' => "Fill out the form and we'll get back to you shortly.",
                'name_label' => 'Full Name',
                'name_placeholder' => 'John Doe',
                'email_label' => 'Email Address',
                'email_placeholder' => 'john@company.com',
                'subject_label' => 'Subject',
                'subject_placeholder' => 'How can we help you?',
                'message_label' => 'Message',
                'message_placeholder' => 'Tell us about your project or challenge...',
                'sent_title' => 'Message Sent!',
                'sent_desc' => "Thank you for reaching out. We'll get back to you within 24 business hours.",
                'send_another' => 'Send Another',
                'sending' => 'Sending your message...',
                'sending_btn' => 'Sending...',
                'submit' => 'Send Message',
                'success_toast' => "Thanks — we'll get back to you within 24 business hours.",
                'error_toast' => 'Failed to send. Please try again.',
                'subject_default' => 'General Inquiry',
            ],
        ];
    }

    private function seedLayout(): void
    {
        $enLayout = $this->mapLayoutFromLocale('en');
        $arLayout = $this->mapLayoutFromLocale('ar');

        $this->upsertPage('layout', $enLayout, $arLayout);
        $this->command?->info('Layout (navbar/footer) content seeded (EN + AR).');
    }

    private function mapLayoutFromLocale(string $locale): array
    {
        $nav = $this->loadLocaleSection($locale, 'nav');
        $footer = $this->loadLocaleSection($locale, 'footer');
        $common = $this->loadLocaleSection($locale, 'common');

        if (!is_array($nav) || !is_array($footer)) {
            return $this->fallbackLayout($locale);
        }

        $brand = $locale === 'ar'
            ? ['name_first' => 'إنوفيشن', 'name_second' => 'تكنولوجي']
            : ['name_first' => 'Innovation', 'name_second' => 'Technology'];

        $linkDefs = [
            ['key' => 'home', 'path' => '/'],
            ['key' => 'about', 'path' => '/about'],
            ['key' => 'services', 'path' => '/services'],
            ['key' => 'contact', 'path' => '/contact'],
        ];

        $links = [];
        foreach ($linkDefs as $def) {
            $links[] = [
                'label' => $nav[$def['key']] ?? '',
                'path' => $def['path'],
            ];
        }

        $socialLabels = $locale === 'ar'
            ? [
                'facebook' => 'فيسبوك',
                'twitter' => 'تويتر',
                'linkedin' => 'لينكدإن',
                'instagram' => 'إنستغرام',
            ]
            : [
                'facebook' => 'Facebook',
                'twitter' => 'Twitter',
                'linkedin' => 'LinkedIn',
                'instagram' => 'Instagram',
            ];

        $socials = [];
        foreach ($socialLabels as $network => $label) {
            $socials[] = [
                'network' => $network,
                'url' => '',
                'label' => $label,
            ];
        }

        return [
            'brand' => $brand,
            'nav' => [
                'cta_label' => is_array($common) ? ($common['send_message'] ?? 'Send Message') : 'Send Message',
                'cta_path' => '/contact',
                'links' => $links,
            ],
            'footer' => [
                'description' => $footer['description'] ?? '',
                'quick_links_heading' => $footer['quick_links'] ?? '',
                'contact_heading' => $footer['contact_us'] ?? '',
                'rights' => $footer['rights'] ?? '',
                'socials' => $socials,
            ],
        ];
    }

    private function fallbackLayout(string $locale): array
    {
        if ($locale === 'ar') {
            return [
                'brand' => ['name_first' => 'إنوفيشن', 'name_second' => 'تكنولوجي'],
                'nav' => [
                    'cta_label' => 'إرسال الرسالة',
                    'cta_path' => '/contact',
                    'links' => [
                        ['label' => 'الرئيسية', 'path' => '/'],
                        ['label' => 'من نحن', 'path' => '/about'],
                        ['label' => 'خدماتنا', 'path' => '/services'],
                        ['label' => 'اتصل بنا', 'path' => '/contact'],
                    ],
                ],
                'footer' => [
                    'description' => 'إنوفيشن تكنولوجي — شريكك الموحّد لتقنية المعلومات.',
                    'quick_links_heading' => 'روابط سريعة',
                    'contact_heading' => 'اتصل بنا',
                    'rights' => 'جميع الحقوق محفوظة.',
                    'socials' => [
                        ['network' => 'facebook', 'url' => '', 'label' => 'فيسبوك'],
                        ['network' => 'twitter', 'url' => '', 'label' => 'تويتر'],
                        ['network' => 'linkedin', 'url' => '', 'label' => 'لينكدإن'],
                        ['network' => 'instagram', 'url' => '', 'label' => 'إنستغرام'],
                    ],
                ],
            ];
        }

        return [
            'brand' => ['name_first' => 'Innovation', 'name_second' => 'Technology'],
            'nav' => [
                'cta_label' => 'Send Message',
                'cta_path' => '/contact',
                'links' => [
                    ['label' => 'Home', 'path' => '/'],
                    ['label' => 'About Us', 'path' => '/about'],
                    ['label' => 'Services', 'path' => '/services'],
                    ['label' => 'Contact', 'path' => '/contact'],
                ],
            ],
            'footer' => [
                'description' => 'Innovation Technology — your one-call partner for hardware, software, and IT operations across Egypt and the MENA region.',
                'quick_links_heading' => 'Quick Links',
                'contact_heading' => 'Contact Us',
                'rights' => 'All rights reserved.',
                'socials' => [
                    ['network' => 'facebook', 'url' => '', 'label' => 'Facebook'],
                    ['network' => 'twitter', 'url' => '', 'label' => 'Twitter'],
                    ['network' => 'linkedin', 'url' => '', 'label' => 'LinkedIn'],
                    ['network' => 'instagram', 'url' => '', 'label' => 'Instagram'],
                ],
            ],
        ];
    }

    /**
     * Mission/vision bodies live on the About page store (not Settings).
     * Prefer existing Settings values when present so re-seeds preserve edits.
     */
    private function mergeMissionVisionBodies(array &$enAbout, array &$arAbout): void
    {
        $defaults = [
            'en' => [
                'mission' => 'To empower enterprises with transformative technology solutions that catalyze growth, optimize operations, and redefine industry standards.',
                'vision' => 'To be the global benchmark for excellence in enterprise technology, bridging the gap between complex innovation and practical business success for organizations worldwide.',
            ],
            'ar' => [
                'mission' => 'تمكين الشركات والمؤسسات من خلال حلول تقنية تحويلية تحفز النمو، وتحسن العمليات، وتعيد تعريف معايير الصناعة.',
                'vision' => 'أن نكون المرجع العالمي للتميز في تكنولوجيا المؤسسات، وسد الفجوة بين الابتكار المعقد والنجاح التجاري العملي للمنظمات في جميع أنحاء العالم.',
            ],
        ];

        $setting = Setting::query()->first();

        $enAbout['mission']['body'] = $setting?->getTranslation('our_mission', 'en')
            ?: ($enAbout['mission']['body'] ?? $defaults['en']['mission']);
        $enAbout['vision']['body'] = $setting?->getTranslation('our_vision', 'en')
            ?: ($enAbout['vision']['body'] ?? $defaults['en']['vision']);
        $arAbout['mission']['body'] = $setting?->getTranslation('our_mission', 'ar')
            ?: ($arAbout['mission']['body'] ?? $defaults['ar']['mission']);
        $arAbout['vision']['body'] = $setting?->getTranslation('our_vision', 'ar')
            ?: ($arAbout['vision']['body'] ?? $defaults['ar']['vision']);
    }

    private function upsertPage(string $pageKey, array $enContent, array $arContent): void
    {
        $page = PageContent::updateOrCreate(
            ['page_key' => $pageKey],
            ['content' => $enContent]
        );

        $page->translations()->where('locale', 'ar')->where('field', 'content')->delete();
        $page->translations()->create([
            'locale' => 'ar',
            'field' => 'content',
            'value' => json_encode($arContent, JSON_UNESCAPED_UNICODE),
        ]);
    }

    private function mapAboutFromLocale(?array $about, string $locale): array
    {
        if (!is_array($about)) {
            return $this->fallbackAbout($locale);
        }

        return [
            'hero' => [
                'breadcrumb' => $about['breadcrumb'] ?? '',
                'title_fallback' => $about['hero_title_fallback'] ?? '',
                'subtitle_fallback' => $about['hero_subtitle_fallback'] ?? '',
            ],
            'mission' => [
                'label' => $about['mission']['label'] ?? '',
                'body' => $about['mission']['body'] ?? '',
            ],
            'vision' => [
                'label' => $about['vision']['label'] ?? '',
                'body' => $about['vision']['body'] ?? '',
            ],
            'story' => [
                'tag_fallback' => $about['story']['tag_fallback'] ?? '',
                'title_fallback' => $about['story']['title_fallback'] ?? '',
                'cta' => $about['story']['cta'] ?? '',
                'image' => $about['story']['image'] ?? '',
            ],
            'clutch' => [
                'year' => (string) ($about['clutch']['year'] ?? '2012'),
                'title' => $about['clutch']['title'] ?? '',
                'label' => $about['clutch']['label'] ?? '',
            ],
            'values' => [
                'tag' => $about['values']['tag'] ?? '',
                'title' => $about['values']['title'] ?? '',
                'subtitle' => $about['values']['subtitle'] ?? '',
                'items' => array_map(static function ($item) {
                    return [
                        'title' => $item['title'] ?? '',
                        'description' => $item['description'] ?? '',
                    ];
                }, is_array($about['values']['items'] ?? null) ? $about['values']['items'] : []),
            ],
            'team' => [
                'tag' => $about['team']['tag'] ?? '',
                'title' => $about['team']['title'] ?? '',
                'subtitle' => $about['team']['subtitle'] ?? '',
            ],
        ];
    }

    private function loadLocaleSection(string $locale, string $key): ?array
    {
        $path = base_path('../Innovation-Technology/src/locales/' . $locale . '.json');
        if (!is_file($path)) {
            $path = dirname(base_path()) . DIRECTORY_SEPARATOR . 'Innovation-Technology' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'locales' . DIRECTORY_SEPARATOR . $locale . '.json';
        }

        if (!is_file($path)) {
            $this->command?->warn("Locale file not found for {$locale}: {$path}");
            return null;
        }

        $json = json_decode(file_get_contents($path), true);
        if (!is_array($json) || !isset($json[$key]) || !is_array($json[$key])) {
            return null;
        }

        return $json[$key];
    }

    private function loadLocaleHome(string $locale): array
    {
        return $this->loadLocaleSection($locale, 'home') ?: $this->fallbackHome($locale);
    }

    private function fallbackAbout(string $locale): array
    {
        if ($locale === 'ar') {
            return [
                'hero' => [
                    'breadcrumb' => 'من نحن',
                    'title_fallback' => 'إنوفيشن تكنولوجي',
                    'subtitle_fallback' => 'شريكك الموحّد لتقنية المعلومات.',
                ],
                'mission' => [
                    'label' => 'مهمتنا',
                    'body' => 'تمكين الشركات والمؤسسات من خلال حلول تقنية تحويلية تحفز النمو، وتحسن العمليات، وتعيد تعريف معايير الصناعة.',
                ],
                'vision' => [
                    'label' => 'رؤيتنا',
                    'body' => 'أن نكون المرجع العالمي للتميز في تكنولوجيا المؤسسات، وسد الفجوة بين الابتكار المعقد والنجاح التجاري العملي للمنظمات في جميع أنحاء العالم.',
                ],
                'story' => [
                    'tag_fallback' => 'قصتنا',
                    'title_fallback' => 'بنيت على الثقة، مدفوعة بالنتائج',
                    'cta' => 'اعمل معنا',
                    'image' => '',
                ],
                'clutch' => [
                    'year' => '2012',
                    'title' => 'تأسست 2012',
                    'label' => 'المقر في 6 أكتوبر، الجيزة',
                ],
                'values' => [
                    'tag' => 'قيمنا',
                    'title' => 'المبادئ التي نعيش بها',
                    'subtitle' => 'الالتزامات التي تشكّل علاقتنا بالعملاء.',
                    'items' => [
                        ['title' => 'الابتكار', 'description' => 'نتبنى ما يحسّن النتائج فعلياً.'],
                        ['title' => 'الالتزام', 'description' => 'نبقى مسؤولين من النطاق حتى التشغيل.'],
                        ['title' => 'الثقة', 'description' => 'توصيات شفافة وتسليم متوقع.'],
                    ],
                ],
                'team' => [
                    'tag' => 'فريقنا',
                    'title' => 'الناس خلف العمل',
                    'subtitle' => 'مهندسون ومصممون واستراتيجيون بشغف مشترك.',
                ],
            ];
        }

        return [
            'hero' => [
                'breadcrumb' => 'About Us',
                'title_fallback' => 'Innovation Technology',
                'subtitle_fallback' => 'Your one-call source for hardware, software, maintenance, and IT operations.',
            ],
            'mission' => [
                'label' => 'Our Mission',
                'body' => 'To empower enterprises with transformative technology solutions that catalyze growth, optimize operations, and redefine industry standards.',
            ],
            'vision' => [
                'label' => 'Our Vision',
                'body' => 'To be the global benchmark for excellence in enterprise technology, bridging the gap between complex innovation and practical business success for organizations worldwide.',
            ],
            'story' => [
                'tag_fallback' => 'Our Story',
                'title_fallback' => 'Built on Trust, Driven by Results',
                'cta' => 'Work With Us',
                'image' => '',
            ],
            'clutch' => [
                'year' => '2012',
                'title' => 'Established 2012',
                'label' => 'Headquartered in 6th of October, Giza',
            ],
            'values' => [
                'tag' => 'Our Values',
                'title' => 'The Principles We Live By',
                'subtitle' => 'The commitments that shape how we engage clients, vendors, and each other.',
                'items' => [
                    ['title' => 'Innovation', 'description' => 'We continuously adopt practical advances that improve outcomes.'],
                    ['title' => 'Commitment', 'description' => 'We stay accountable from first scope through support.'],
                    ['title' => 'Trust', 'description' => 'Transparent recommendations and predictable delivery.'],
                ],
            ],
            'team' => [
                'tag' => 'Our Team',
                'title' => 'The People Behind the Work',
                'subtitle' => 'World-class engineers, designers, and strategists.',
            ],
        ];
    }

    private function fallbackHome(string $locale): array
    {
        if ($locale === 'ar') {
            return [
                'hero' => [
                    'badge' => 'منذ 2012',
                    'title_part1' => 'التكنولوجيا التي',
                    'title_part2' => 'عملك',
                    'hero_words' => ['تقوي', 'تسرّع', 'تحمي'],
                    'description' => 'إنوفيشن تكنولوجي — شريكك لتقنية المعلومات.',
                    'cta_primary' => 'استكشف الخدمات',
                    'cta_secondary' => 'تعرف علينا',
                    'badge_secondary' => 'سحابة · أمن · مركز بيانات',
                    'trust' => [
                        'iso_certified' => 'مايكروسوفت',
                        'uptime_sla' => 'أمن',
                        'support_24' => 'دورة حياة شاملة',
                    ],
                    'hero_images' => self::HERO_IMAGES,
                ],
                'services' => [
                    'tag' => 'ماذا نفعل',
                    'title' => 'حلول مبنية للتوسع',
                    'subtitle' => 'نغطي مكدس التكنولوجيا لديك.',
                    'cta' => 'عرض جميع الخدمات',
                ],
                'cta' => [
                    'title' => 'هل أنت جاهز لتحويل عملك؟',
                    'description' => 'أخبرنا عن بيئتك.',
                    'primary' => 'ابدأ مشروعاً',
                    'secondary' => 'عن إنوفيشن تكنولوجي',
                ],
                'partners' => [
                    'tag' => 'شركاء',
                    'title' => 'جهات تثق بنا',
                    'subtitle' => 'لمحة عن القطاعات التي ندعمها.',
                ],
                'about_strip' => [
                    'who_tag' => 'من نحن',
                    'title' => 'شريكك الموحّد لتقنية المعلومات',
                    'p1' => 'نساعد الشركات على بدء وصيانة وترقية خدمات تقنية المعلومات.',
                    'bullets' => ['تسليم شامل', 'إرشاد محايد', 'خبرة إقليمية'],
                    'cta' => 'اقرأ قصتنا',
                    'card_badge' => 'تسليم منظم',
                    'card_p' => 'ننسق قرارات التكنولوجيا مع أولويات العمل.',
                ],
                'why' => [
                    'tag' => 'لماذا IN Tech',
                    'title' => 'شريك تعمل معه',
                    'subtitle' => 'تنفيذ تقني عميق وتخطيط عملي.',
                    'items' => [
                        ['title' => 'عمليات حقيقية', 'desc' => 'حلول قابلة للصيانة بعد الإطلاق.'],
                        ['title' => 'أمن أولاً', 'desc' => 'المرونة جزء من المعمار.'],
                        ['title' => 'دعم يظهر', 'desc' => 'استجابة سريعة للحوادث.'],
                        ['title' => 'أفضل سعر', 'desc' => 'قيمة إجمالية عادلة.'],
                    ],
                ],
                'fulfillment' => [
                    'tag' => 'التوريد',
                    'title' => 'تسليم أجهزة نظيف وكامل',
                    'p1' => 'نتعامل مع التوريد كمنتج مغلق.',
                    'bullets' => ['تسليم مغلق', 'عروض صريحة', 'خدمات اختيارية'],
                    'cta' => 'اسأل عن التوريد',
                    'card1_title' => 'SKU موثوق',
                    'card1_sub' => 'متوافق مع معاييرك.',
                    'card2_title' => 'حركة متتبعة',
                    'card2_sub' => 'من المستودع إلى المكتب.',
                    'footer_note' => 'لا حزم مفروضة',
                ],
                'process' => [
                    'tag' => 'كيف نعمل',
                    'title' => 'إيقاع تسليم شفاف',
                    'subtitle' => 'مسار واضح من الاكتشاف إلى التشغيل.',
                    'steps' => [
                        ['n' => '01', 'title' => 'اكتشاف', 'desc' => 'نتوافق على الأهداف.'],
                        ['n' => '02', 'title' => 'تصميم', 'desc' => 'هندسة وخطة إطلاق.'],
                        ['n' => '03', 'title' => 'تسليم', 'desc' => 'تنفيذ وتحقق.'],
                        ['n' => '04', 'title' => 'تطوّر', 'desc' => 'مراقبة وتحسين.'],
                    ],
                ],
                'pillars' => [
                    'tag' => 'الخبرات الأساسية',
                    'title' => 'تغطية عبر المكدس',
                    'subtitle' => 'نتائج قابلة للقياس.',
                    'items' => [
                        ['title' => 'السحابة', 'desc' => 'Azure و Microsoft 365.'],
                        ['title' => 'الأمن', 'desc' => 'طبقات دفاع متعددة.'],
                        ['title' => 'مركز البيانات', 'desc' => 'خوادم وشبكات.'],
                        ['title' => 'الصوت', 'desc' => 'VoIP حديث.'],
                    ],
                    'cta' => 'استكشف الخدمات',
                ],
                'testimonials' => [
                    'tag' => 'ماذا يقول العملاء',
                    'title' => 'نتائج ملموسة',
                    'subtitle' => 'وضوح واستقرار وثقة.',
                    'quotes' => [
                        ['quote' => 'ساعدونا على خارطة طريق قابلة للصيانة.', 'name' => 'رئيس عمليات تقنية المعلومات', 'org' => 'مؤسسة إقليمية'],
                        ['quote' => 'عمل الأمن كان شاملاً دون إبطاء التسليم.', 'name' => 'مدير تقنية المعلومات', 'org' => 'خدمات مالية'],
                        ['quote' => 'يربط قرارات البنية بنتائج الأعمال.', 'name' => 'المدير التنفيذي للعمليات', 'org' => 'شركة خدمات'],
                    ],
                ],
            ];
        }

        return [
            'hero' => [
                'badge' => 'Since 2012 · SMEs & enterprises in MENA',
                'badge_secondary' => 'Cloud · Security · Data center · VoIP',
                'title_part1' => 'Technology That',
                'title_part2' => 'Your Business',
                'hero_words' => ['Powers', 'Accelerates', 'Secures', 'Transforms', 'Automates', 'Optimizes', 'Scales', 'Innovates'],
                'description' => 'Innovation Technology is your single point of contact for hardware, software, maintenance, and modern IT services.',
                'cta_primary' => 'Explore Services',
                'cta_secondary' => 'Learn About Us',
                'trust' => [
                    'iso_certified' => 'Microsoft ecosystem depth',
                    'uptime_sla' => 'Defense-in-depth security',
                    'support_24' => 'End-to-end IT lifecycle',
                ],
                'hero_images' => self::HERO_IMAGES,
            ],
            'services' => [
                'tag' => 'What We Do',
                'title' => 'Solutions Built for Scale',
                'subtitle' => 'From cloud infrastructure to AI-powered analytics — we cover every layer of your technology stack.',
                'cta' => 'View All Services',
            ],
            'cta' => [
                'title' => 'Ready to Transform Your Business?',
                'description' => "Tell us about your environment — we'll help you choose the right stack, rollout plan, and support model.",
                'primary' => 'Start a Project',
                'secondary' => 'About Innovation Technology',
            ],
            'partners' => [
                'tag' => 'Partners',
                'title' => 'Organizations that trust us',
                'subtitle' => 'A snapshot of the industries and businesses we support.',
            ],
            'about_strip' => [
                'who_tag' => 'Who we are',
                'title' => 'Your one-call partner for hardware, software & IT operations',
                'p1' => 'IN Tech helps SMEs and enterprises start, maintain, and upgrade IT services with a professional team.',
                'bullets' => [
                    'End-to-end delivery: advisory, implementation, and support',
                    'Vendor-neutral guidance focused on fit, performance, and cost',
                    'Regional experience across regulated and high-availability environments',
                ],
                'cta' => 'Read our story',
                'card_badge' => 'Structured delivery. Clear ownership. Measurable outcomes.',
                'card_p' => 'We align technology decisions with business priorities.',
            ],
            'why' => [
                'tag' => 'Why IN Tech',
                'title' => 'A partner you can run with — not just buy from',
                'subtitle' => 'Deep technical execution with pragmatic planning.',
                'items' => [
                    ['title' => 'Built for real operations', 'desc' => 'Solutions that stay maintainable after go-live.'],
                    ['title' => 'Security-first mindset', 'desc' => 'Resilience and compliance as part of the architecture.'],
                    ['title' => 'Support that shows up', 'desc' => 'Responsive team for incidents and changes.'],
                    ['title' => 'Best solution, best price', 'desc' => 'Optimize for total value.'],
                ],
            ],
            'fulfillment' => [
                'tag' => 'Fulfillment',
                'title' => 'Hardware delivered clean, complete, no surprise add-ons',
                'p1' => 'Tracked logistics, careful handover, and quotes without padded bundles.',
                'bullets' => [
                    'Sealed-box delivery where it matters',
                    'Straight quotes: what you approve is what ships',
                    'Optional services are explicit',
                ],
                'cta' => 'Ask about hardware fulfillment',
                'card1_title' => 'Sealed SKU, verified config',
                'card1_sub' => 'Asset-tagged and aligned to your standards.',
                'card2_title' => 'Tracked movement',
                'card2_sub' => 'From dock to desk — visibility you can plan around.',
                'footer_note' => 'No forced bundles · Best solution · Best price',
            ],
            'process' => [
                'tag' => 'How we work',
                'title' => 'A calm, transparent delivery rhythm',
                'subtitle' => 'A clear path from first workshop to steady-state operations.',
                'steps' => [
                    ['n' => '01', 'title' => 'Discover', 'desc' => 'Align on goals, constraints, and success metrics.'],
                    ['n' => '02', 'title' => 'Design', 'desc' => 'Architecture, security model, and phased rollout.'],
                    ['n' => '03', 'title' => 'Deliver', 'desc' => 'Implementation with validation and knowledge transfer.'],
                    ['n' => '04', 'title' => 'Evolve', 'desc' => 'Monitoring, optimization, and continuous improvements.'],
                ],
            ],
            'pillars' => [
                'tag' => 'Core expertise',
                'title' => 'Coverage across the stack',
                'subtitle' => 'Outcomes you can measure: uptime, security posture, and operational agility.',
                'items' => [
                    ['title' => 'Cloud & Microsoft 365', 'desc' => 'Azure and Microsoft 365 adoption and governance.'],
                    ['title' => 'Cyber security', 'desc' => 'Network, endpoints, encryption, and monitoring.'],
                    ['title' => 'Data center', 'desc' => 'Servers, storage, networking, and virtualization.'],
                    ['title' => 'Voice (VoIP)', 'desc' => 'Modern telephony for real business needs.'],
                ],
                'cta' => 'Explore services',
            ],
            'testimonials' => [
                'tag' => 'What clients say',
                'title' => 'Outcomes you can feel in day-to-day operations',
                'subtitle' => 'Clarity, stability, and teams that regain confidence.',
                'quotes' => [
                    ['quote' => 'They helped us design a roadmap we could actually maintain.', 'name' => 'Head of IT Operations', 'org' => 'Regional enterprise'],
                    ['quote' => 'Clear ownership and strong follow-through on security work.', 'name' => 'IT Director', 'org' => 'Financial services'],
                    ['quote' => 'A partner that connects infrastructure decisions to business outcomes.', 'name' => 'COO', 'org' => 'Mid-sized services company'],
                ],
            ],
        ];
    }
}
