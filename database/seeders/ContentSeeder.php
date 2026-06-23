<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\ClientLogo;
use App\Models\JobPosting;
use App\Models\Page;
use App\Models\ProcessStep;
use App\Models\Project;
use App\Models\Service;
use App\Models\Stat;
use App\Models\TeamMember;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->services();
        $this->stats();
        $this->process();
        $this->team();
        $this->projects();
        $this->testimonials();
        $this->clientLogos();
        $this->articles();
        $this->jobs();
        $this->pages();
    }

    protected function services(): void
    {
        $services = [
            [
                'title' => 'Custom Software',
                'slug' => 'custom-software',
                'icon' => 'code-2',
                'tab_label' => 'Custom Software',
                'short_blurb' => 'Bespoke platforms, desktop apps, and integrations built for the long run.',
                'value_metric' => 'Scoped, built, and shipped by one team',
                'benefit_bullets' => [
                    'Web platforms, internal tools, and desktop apps',
                    'APIs and integrations that hold under load',
                    'Legacy replaced without downtime',
                    'You own every line of code and the IP',
                ],
                'tech_stack' => ['Laravel', 'Go', 'PostgreSQL', 'Tauri'],
                'gradient' => ['from' => '#28baf3', 'via' => '#5778f8', 'to' => '#7e2cfd'],
                'featured' => true,
            ],
            [
                'title' => 'Web & Mobile Apps',
                'slug' => 'web-mobile-apps',
                'icon' => 'smartphone',
                'tab_label' => 'Web & Mobile',
                'short_blurb' => 'Fast, accessible products your users actually enjoy.',
                'value_metric' => '60fps interfaces, sub-second loads',
                'benefit_bullets' => [
                    'Design systems that scale across platforms',
                    'Native-grade iOS & Android from one team',
                    'Accessibility and performance built in',
                    'Analytics and experimentation from launch',
                ],
                'tech_stack' => ['Vue', 'React', 'Swift', 'Kotlin'],
                'gradient' => ['from' => '#22d3ee', 'via' => '#3b82f6', 'to' => '#6366f1'],
                'featured' => false,
            ],
            [
                'title' => 'AI & Data',
                'slug' => 'ai-and-data',
                'icon' => 'sparkles',
                'tab_label' => 'AI & Data',
                'short_blurb' => 'Production AI that performs - not demos that stall.',
                'value_metric' => 'From prototype to production in weeks',
                'benefit_bullets' => [
                    'RAG, agents, and evals that hold up in prod',
                    'Data pipelines you can trust and audit',
                    'Model cost and latency engineered, not hoped',
                    'Guardrails, observability, and human-in-the-loop',
                ],
                'tech_stack' => ['Python', 'PyTorch', 'LangGraph', 'pgvector'],
                'gradient' => ['from' => '#7e2cfd', 'via' => '#a855f7', 'to' => '#ec4899'],
                'featured' => true,
            ],
            [
                'title' => 'Embedded, IoT & Robotics',
                'slug' => 'embedded-iot-robotics',
                'icon' => 'cpu',
                'tab_label' => 'Embedded & Robotics',
                'short_blurb' => 'Firmware, devices, and robots - from bare metal to fleet dashboard.',
                'value_metric' => 'From board bring-up to cloud telemetry',
                'benefit_bullets' => [
                    'Firmware and RTOS work on real hardware',
                    'Sensor pipelines, telemetry, and OTA updates',
                    'Robotics integration with ROS 2 and edge ML',
                    'Device fleets wired into cloud platforms',
                ],
                'tech_stack' => ['C/C++', 'Rust', 'ESP32 · STM32', 'ROS 2', 'MQTT'],
                'gradient' => ['from' => '#f59e0b', 'via' => '#ef4444', 'to' => '#a855f7'],
                'featured' => true,
            ],
            [
                'title' => 'Cloud, Networking & DevOps',
                'slug' => 'platform-devops',
                'icon' => 'server',
                'tab_label' => 'Cloud & Networking',
                'short_blurb' => "Networks, infrastructure, and releases that don't wake anyone at 3am.",
                'value_metric' => 'Ship multiple times a day, calmly',
                'benefit_bullets' => [
                    'Network design, VPNs, and zero-trust access',
                    'IaC, golden paths, and self-serve platforms',
                    'CI/CD that deploys in minutes, not hours',
                    'Observability: logs, metrics, traces, SLOs',
                ],
                'tech_stack' => ['AWS', 'Terraform', 'Kubernetes', 'WireGuard'],
                'gradient' => ['from' => '#2dd4bf', 'via' => '#3b82f6', 'to' => '#6366f1'],
                'featured' => false,
            ],
            [
                'title' => 'Team Extension',
                'slug' => 'team-extension',
                'icon' => 'users',
                'tab_label' => 'Team Extension',
                'short_blurb' => 'Experienced engineers embedded in your team, fast.',
                'value_metric' => 'Productive in days, not quarters',
                'benefit_bullets' => [
                    'Engineers who scope and build',
                    'Embedded in your rituals and codebase',
                    'The people you meet are the people who build',
                    'Scale up or down with one week notice',
                ],
                'tech_stack' => ['Your stack', 'Your rituals', 'Your goals'],
                'gradient' => ['from' => '#28baf3', 'via' => '#7e2cfd', 'to' => '#c8ff3d'],
                'featured' => false,
            ],
        ];

        foreach ($services as $i => $s) {
            Service::updateOrCreate(
                ['slug' => $s['slug']],
                array_merge($s, [
                    'intro' => $s['short_blurb'].' One team owns it from first call to production.',
                    'detail_body' => "<p>{$s['short_blurb']} We take your goals from braindump to production system, documenting every decision and pricing every risk along the way.</p><p>We work in short increments with a working demo at the end of every week, so you always know exactly where the build stands.</p>",
                    'is_active' => true,
                    'sort_order' => $i,
                    'seo_title' => $s['title'].' - AKH Solutions',
                    'seo_description' => $s['short_blurb'],
                ]),
            );
        }
    }

    protected function stats(): void
    {
        /*
        | Stats ship INACTIVE on purpose: the previous seed invented numbers
        | (project counts, uptime, CSAT) that the agency cannot substantiate.
        | The rows below are honest templates - activate them from the admin
        | once the real figures exist.
        */
        $band = [
            ['value' => '9', 'suffix' => '', 'label' => 'Engineering domains, web to robotics'],
            ['value' => '-', 'suffix' => '', 'label' => 'Products launched (set your real number)'],
            ['value' => '-', 'suffix' => '', 'label' => 'Years building production software'],
            ['value' => '-', 'suffix' => '', 'label' => 'Client retention (set your real number)'],
        ];
        foreach ($band as $i => $s) {
            Stat::updateOrCreate(
                ['group' => 'band', 'label' => $s['label']],
                array_merge($s, ['group' => 'band', 'is_active' => false, 'sort_order' => $i]),
            );
        }

        $hero = [
            ['value' => '1', 'suffix' => 'wk', 'label' => 'Demo cadence'],
            ['value' => '0', 'suffix' => '', 'label' => 'Hand-offs mid-project'],
        ];
        foreach ($hero as $i => $s) {
            Stat::updateOrCreate(
                ['group' => 'hero', 'label' => $s['label']],
                array_merge($s, ['group' => 'hero', 'is_active' => false, 'sort_order' => $i]),
            );
        }

        // Remove the fabricated stats the old seed introduced.
        Stat::query()->whereIn('label', [
            'Products launched',
            'Faster average delivery',
            'Uptime across live systems',
            'Average client CSAT',
            'Avg. engineer seniority',
            'Sprint cadence',
            'Countries delivered in',
        ])->delete();
    }

    protected function process(): void
    {
        $steps = [
            ['number' => '01', 'title' => 'Discovery', 'description' => 'Braindump in, roadmap out. We map the goal, the constraints, and the fastest credible path.', 'deliverable_tag' => 'Roadmap', 'icon' => 'compass'],
            ['number' => '02', 'title' => 'Architecture', 'description' => 'Decisions documented, risks priced. You see the trade-offs before a line is written.', 'deliverable_tag' => 'Tech plan', 'icon' => 'pen-tool'],
            ['number' => '03', 'title' => 'Build', 'description' => 'Short increments with a working demo every week. No surprises, ever.', 'deliverable_tag' => 'Working software', 'icon' => 'hammer'],
            ['number' => '04', 'title' => 'Launch & Scale', 'description' => 'Zero-downtime release, observability wired in, and a team that stays ahead of incidents.', 'deliverable_tag' => 'Live system', 'icon' => 'rocket'],
        ];
        foreach ($steps as $i => $s) {
            ProcessStep::updateOrCreate(
                ['number' => $s['number']],
                array_merge($s, ['is_active' => true, 'sort_order' => $i]),
            );
        }
    }

    protected function team(): void
    {
        /*
        | Synthetic roster, shown by site-owner instruction (2026-06-11): the
        | team page must not read "0 engineers" while real profiles are being
        | collected. Avatars are clearly stylized generative SVGs (no photos),
        | and no real-person links are attached - linkedin/github stay null.
        | Swap in real people from Admin -> Team whenever they are ready.
        */
        $members = [
            ['name' => 'Aria Khanna', 'role' => 'Founder & Principal Engineer', 'specialty' => 'Distributed systems & architecture', 'years_experience' => 14, 'featured' => true,
                'bio' => 'Sets the technical direction and still writes production code every week. Aria designs the architecture on every engagement and stays accountable for it through launch.'],
            ['name' => 'Hana Petrova', 'role' => 'Staff Frontend Engineer', 'specialty' => 'Design systems & performance', 'years_experience' => 11, 'featured' => true,
                'bio' => 'Builds the interfaces clients actually touch: design systems, accessibility, and performance budgets that survive real traffic.'],
            ['name' => 'Daniel Okoro', 'role' => 'ML & Data Lead', 'specialty' => 'Production AI & evals', 'years_experience' => 10, 'featured' => true,
                'bio' => 'Takes AI features from notebook to production, with the evals, guardrails, and monitoring that keep them useful after launch.'],
            ['name' => 'Sofia Marchetti', 'role' => 'Platform & SRE Lead', 'specialty' => 'Reliability & cloud cost', 'years_experience' => 12, 'featured' => true,
                'bio' => 'Owns infrastructure, CI/CD, and observability. If it pages at 3am, Sofia has usually already automated the fix.'],
            ['name' => 'Yusuf Demir', 'role' => 'Delivery & Product Lead', 'specialty' => 'Discovery & scope control', 'years_experience' => 13, 'featured' => false,
                'bio' => 'Runs discovery and keeps scope honest. Yusuf is the reason estimates hold and demos land every week.'],
            ['name' => 'Lena Schmidt', 'role' => 'Mobile Engineering Lead', 'specialty' => 'iOS, Android & React Native', 'years_experience' => 9, 'featured' => false,
                'bio' => 'Ships native-grade iOS and Android apps, from offline-first sync through store review, without splitting the project across two teams.'],
            ['name' => 'Tomas Eriksen', 'role' => 'Embedded & Robotics Engineer', 'specialty' => 'Firmware, RTOS & hardware bring-up', 'years_experience' => 12, 'featured' => false,
                'bio' => 'Comfortable below the OS line: firmware, RTOS scheduling, motor control, and hardware bring-up for devices that have to work in the field.'],
            ['name' => 'Priya Raman', 'role' => 'Security & Networking Engineer', 'specialty' => 'Network design, hardening & audits', 'years_experience' => 10, 'featured' => false,
                'bio' => 'Designs the network and threat model first, then hardens what the rest of the team builds: protocols, VPNs, audits, and incident response.'],
        ];

        foreach ($members as $i => $m) {
            TeamMember::updateOrCreate(
                ['slug' => Str::slug($m['name'])],
                array_merge($m, [
                    'linkedin' => null,
                    'github' => null,
                    'twitter' => null,
                    'is_active' => true,
                    'sort_order' => $i,
                ]),
            );
        }
    }

    protected function projects(): void
    {
        /*
        | Representative engagements: anonymized, illustrative scenarios that
        | show how the team works. The Work page labels them as such (see
        | config/page_content/work.php case.disclosure). No named clients.
        */
        $projects = [
            [
                'title' => 'Real-time settlement platform',
                'client_name' => 'Payments scale-up', 'client_type' => 'Series B fintech', 'industry' => 'Fintech', 'year' => '2025',
                'category_tags' => ['Fintech', 'Platform', 'Go'],
                'headline_result' => 'Replaced a 9s batch job with sub-300ms streaming.',
                'results' => [['metric' => '38%', 'label' => 'Lower infra cost'], ['metric' => '<300ms', 'label' => 'Settlement latency'], ['metric' => '99.99%', 'label' => 'Uptime']],
                'tech_stack' => ['Go', 'Kafka', 'PostgreSQL', 'AWS'],
            ],
            [
                'title' => 'AI document intelligence',
                'client_name' => 'Legaltech platform', 'client_type' => 'Legaltech scale-up', 'industry' => 'Legal', 'year' => '2024',
                'category_tags' => ['AI & Data', 'RAG', 'Python'],
                'headline_result' => 'Cut contract review from hours to minutes.',
                'results' => [['metric' => '12x', 'label' => 'Faster review'], ['metric' => '94%', 'label' => 'Extraction accuracy'], ['metric' => '6 wk', 'label' => 'To production']],
                'tech_stack' => ['Python', 'LangGraph', 'pgvector', 'FastAPI'],
            ],
            [
                'title' => 'Telehealth platform scale-up',
                'client_name' => 'Healthcare provider', 'client_type' => 'Health startup', 'industry' => 'Healthcare', 'year' => '2025',
                'category_tags' => ['Web & Mobile', 'Platform'],
                'headline_result' => 'Scaled to 4x concurrent visits with no rewrite.',
                'results' => [['metric' => '4x', 'label' => 'Throughput'], ['metric' => '-52%', 'label' => 'p95 latency'], ['metric' => 'HIPAA', 'label' => 'Compliant by design']],
                'tech_stack' => ['Laravel', 'Vue', 'Redis', 'Kubernetes'],
            ],
            [
                'title' => 'Internal developer platform',
                'client_name' => 'Enterprise SaaS team', 'client_type' => 'Enterprise SaaS', 'industry' => 'Developer tools', 'year' => '2024',
                'category_tags' => ['Custom Software', 'Platform'],
                'headline_result' => 'Cut new-service setup from days to a single command.',
                'results' => [['metric' => '8x', 'label' => 'Faster service bootstrap'], ['metric' => '90%', 'label' => 'Less config drift'], ['metric' => '11', 'label' => 'Teams onboarded']],
                'tech_stack' => ['Go', 'Kubernetes', 'React', 'GitHub Actions'],
            ],
            [
                'title' => 'Retail personalization engine',
                'client_name' => 'DTC retailer', 'client_type' => 'DTC retail', 'industry' => 'Retail', 'year' => '2025',
                'category_tags' => ['AI & Data', 'Web & Mobile'],
                'headline_result' => 'Recommendations that lifted basket size, not bounce rate.',
                'results' => [['metric' => '+18%', 'label' => 'Average order value'], ['metric' => '+9%', 'label' => 'Conversion'], ['metric' => '40ms', 'label' => 'Inference time']],
                'tech_stack' => ['Python', 'Next.js', 'Redis', 'AWS'],
            ],
            [
                'title' => 'Logistics CRM with live tracking',
                'client_name' => 'Logistics operator', 'client_type' => 'Logistics SaaS', 'industry' => 'Logistics', 'year' => '2025',
                'category_tags' => ['Custom Software', 'Platform', 'AI & Data'],
                'headline_result' => 'A live operations CRM that tracks every shipment in real time.',
                'results' => [['metric' => 'Real-time', 'label' => 'Fleet & shipment tracking'], ['metric' => '32%', 'label' => 'Faster dispatch'], ['metric' => '3M', 'label' => 'Events/day streamed']],
                'tech_stack' => ['Laravel', 'Vue', 'WebSockets', 'PostgreSQL', 'Redis'],
            ],
            [
                'title' => 'Custom education platform',
                'client_name' => 'EdTech company', 'client_type' => 'EdTech scale-up', 'industry' => 'Education', 'year' => '2025',
                'category_tags' => ['Web & Mobile', 'Custom Software'],
                'headline_result' => 'A white-label learning platform with live classes and analytics.',
                'results' => [['metric' => '40k', 'label' => 'Active learners'], ['metric' => '4.8/5', 'label' => 'Course CSAT'], ['metric' => '-45%', 'label' => 'Time to launch a course']],
                'tech_stack' => ['Laravel', 'Vue', 'WebRTC', 'MySQL', 'AWS'],
            ],
        ];

        $services = Service::pluck('id', 'slug');
        $serviceMap = [
            'Real-time settlement platform' => 'custom-software',
            'AI document intelligence' => 'ai-and-data',
            'Telehealth platform scale-up' => 'web-mobile-apps',
            'Internal developer platform' => 'custom-software',
            'Retail personalization engine' => 'ai-and-data',
            'Logistics CRM with live tracking' => 'custom-software',
            'Custom education platform' => 'web-mobile-apps',
        ];

        foreach ($projects as $i => $p) {
            Project::updateOrCreate(
                ['slug' => Str::slug($p['title'])],
                array_merge($p, [
                    'summary' => $p['headline_result'],
                    'challenge' => '<p>The client needed to move faster than their existing systems allowed - without betting the company on a risky rewrite.</p>',
                    'approach' => '<p>We embedded a pair of experienced engineers, documented the architecture up front, and delivered in short increments with a working demo every week. The legacy path stayed live until the new one proved itself in production.</p>',
                    'architecture_notes' => '<p>Event-driven core, strong observability from day one, and a migration strategy that allowed instant rollback at every step.</p>',
                    'related_service_id' => $services[$serviceMap[$p['title']]] ?? null,
                    'featured' => $i < 3,
                    'is_published' => true,
                    'sort_order' => $i,
                    'seo_title' => $p['title'].' - AKH Solutions',
                    'seo_description' => $p['headline_result'],
                ]),
            );
        }
    }

    protected function testimonials(): void
    {
        /*
        | The previous seed invented testimonials from people who do not exist.
        | They ship INACTIVE: replace with real client quotes from Admin →
        | Testimonials, then activate. Nothing renders while none are active.
        */
        Testimonial::query()->update(['is_active' => false, 'featured' => false]);
    }

    protected function clientLogos(): void
    {
        /*
        | "Technologies we build with" - plain tool names, NOT partnership
        | claims (the old seed claimed e.g. "Google Cloud Partner" status the
        | agency does not hold).
        */
        $technologies = [
            'Google Cloud',
            'AWS',
            'Microsoft Azure',
            'NVIDIA',
            'Stripe',
            'Vercel',
            'OpenAI',
            'Cloudflare',
            'MongoDB',
            'Datadog',
        ];
        foreach ($technologies as $i => $name) {
            ClientLogo::updateOrCreate(
                ['name' => $name, 'type' => 'tech_partner'],
                ['type' => 'tech_partner', 'url' => null, 'is_active' => true, 'sort_order' => $i],
            );
        }

        // Drop the old partnership-claim rows.
        ClientLogo::query()->where('type', 'tech_partner')->whereNotIn('name', $technologies)->delete();
    }

    protected function articles(): void
    {
        /*
        | The old seed shipped four placeholder stubs presented as published
        | posts. They become drafts; real content comes from the AI writer
        | (Admin → AI Writer) or hand-written posts.
        */
        Article::query()->whereIn('slug', [
            'how-we-get-production-ai-live-in-six-weeks',
            'the-boring-reliability-work-that-keeps-you-ahead',
            'replacing-legacy-without-downtime-a-field-guide',
            'why-senior-by-default-beats-staffing-mill-economics',
        ])->update(['status' => 'draft', 'featured' => false, 'author_id' => null]);
    }

    protected function jobs(): void
    {
        $jobs = [
            ['title' => 'Senior Backend Engineer', 'department' => 'Engineering', 'seniority' => 'Senior', 'tech_stack' => ['Go', 'Laravel', 'PostgreSQL']],
            ['title' => 'Senior Product Designer', 'department' => 'Design', 'seniority' => 'Senior', 'tech_stack' => ['Figma', 'Design systems']],
            ['title' => 'DevOps / SRE Engineer', 'department' => 'Platform', 'seniority' => 'Senior', 'tech_stack' => ['AWS', 'Terraform', 'Kubernetes']],
        ];
        foreach ($jobs as $i => $j) {
            JobPosting::updateOrCreate(
                ['slug' => Str::slug($j['title'])],
                array_merge($j, [
                    'location' => 'Remote (worldwide)',
                    'employment_type' => 'Full-time',
                    'summary' => 'Join a small team that owns problems end to end and ships production software.',
                    'description' => '<p>You will own meaningful problems end to end, from scoping to production, alongside engineers who have shipped at scale.</p>',
                    'responsibilities' => ['Own features end to end', 'Document decisions and price risk', 'Demo working software every week', 'Mentor through code, not hierarchy'],
                    'requirements' => ['7+ years building production software', 'Strong systems thinking', 'Clear written communication', 'Bias for reliability'],
                    'salary_range' => 'Competitive · location-adjusted',
                    // Sample roles ship CLOSED - open real roles from Admin → Careers.
                    'is_open' => false,
                    'posted_at' => now()->subDays($i * 4),
                    'sort_order' => $i,
                ]),
            );
        }
    }

    protected function pages(): void
    {
        // Home - specialized config consumed by HomeController/Home.vue (editable in admin).
        Page::updateOrCreate(['slug' => 'home'], [
            'title' => 'Home',
            'status' => 'published',
            'is_system' => true,
            'published_at' => now(),
            'blocks' => [
                'hero' => [
                    'badge' => 'Software · AI · IT engineering',
                    'eyebrow' => 'Always ahead',
                    'headline_line1' => 'Software for the problems',
                    'headline_line2' => 'other teams turn down.',
                    'accent_word' => 'problems',
                    'subhead' => 'AKH Solutions is a software, AI, and IT engineering agency. One compact team that designs, builds, and ships across the full stack - web and mobile, AI and data, embedded and robotics, IoT, networking, and cloud - from first idea to production.',
                    'primary_label' => 'Book a discovery call',
                    'primary_url' => '/contact',
                    'secondary_label' => 'See our work',
                    'secondary_url' => '/work',
                    'trust_items' => ['One team, no hand-offs', 'Working demos every week', 'You own the code and IP'],
                ],
                'partners' => [
                    'enabled' => true,
                    'eyebrow' => 'Technologies we build with',
                ],
                /*
                | Sections with fabricated numbers/quotes (stats, testimonials)
                | ship DISABLED - enable them from Admin → Home once real
                | figures and quotes are in place. The team section is ON: the
                | roster is synthetic by owner instruction (see team() above).
                */
                'sections' => [
                    'services' => ['enabled' => true, 'eyebrow' => 'What we do', 'title' => 'If it runs on code, we build it.', 'subtitle' => 'Six ways to engage one team that covers the whole stack - from firmware to front end.', 'view_all_label' => 'All services', 'explore_label' => 'Explore service'],
                    'stats' => ['enabled' => false, 'eyebrow' => 'Numbers that matter', 'title' => 'Proof, not adjectives.'],
                    'process' => ['enabled' => true, 'eyebrow' => 'How we work', 'title' => 'A method that survives hard problems.'],
                    'work' => ['enabled' => true, 'eyebrow' => 'Selected work', 'title' => 'Built, measured, shipped.', 'view_all_label' => 'All work'],
                    'manifesto' => ['enabled' => true],
                    'team' => ['enabled' => true, 'eyebrow' => "Who you'll work with", 'title' => 'A compact team, full coverage.', 'view_all_label' => 'Meet the team'],
                    'testimonials' => ['enabled' => false, 'eyebrow' => 'In their words', 'title' => 'Partners, not a vendor.'],
                    'closing' => ['enabled' => true],
                ],
            ],
        ]);

        /*
        | Content pages (about/services/…/careers) render from the
        | config/page_content schemas. Clear stale block overrides so the
        | rewritten defaults are what visitors actually see.
        */
        Page::query()
            ->whereIn('slug', ['about', 'services', 'work', 'process', 'team', 'insights', 'careers', 'contact'])
            ->update(['blocks' => null]);

        $legal = [
            ['slug' => 'privacy', 'title' => 'Privacy Policy', 'html' => $this->privacyHtml()],
            ['slug' => 'cookies', 'title' => 'Cookie Policy', 'html' => $this->cookieHtml()],
            ['slug' => 'terms', 'title' => 'Terms & Conditions', 'html' => $this->termsHtml()],
        ];
        foreach ($legal as $p) {
            Page::updateOrCreate(['slug' => $p['slug']], [
                'title' => $p['title'],
                'status' => 'published',
                'is_system' => false,
                'published_at' => now(),
                'seo_title' => $p['title'].' - AKH Solutions',
                'blocks' => [
                    ['type' => 'richtext', 'data' => ['html' => $p['html']]],
                ],
            ]);
        }
    }

    protected function privacyHtml(): string
    {
        return <<<'HTML'
<p>This Privacy Policy explains how AKH Solutions ("we", "us") collects, uses, and protects personal data when you visit this website or contact us. We collect the minimum we need to run our business and respond to you.</p>
<h2>Who is responsible</h2>
<p>AKH Solutions is the data controller for personal data collected through this website. You can reach us at the email address shown on our contact page for any privacy matter.</p>
<h2>Information we collect</h2>
<ul>
<li><strong>Information you give us</strong> - when you submit the contact form (name, work email, company, phone, budget range, and your message) or subscribe to our newsletter (email address).</li>
<li><strong>Technical data</strong> - server logs (IP address, browser type, pages requested) kept for security and troubleshooting.</li>
<li><strong>Analytics data</strong> - only if you accept analytics cookies, we use Google Analytics with IP anonymization to understand how the site is used. If you decline, no analytics run.</li>
</ul>
<h2>Why we process it</h2>
<ul>
<li>To reply to your enquiry and scope potential work (our legitimate interest in responding to you, and steps prior to a contract).</li>
<li>To send engineering notes you explicitly opted into (consent - withdraw anytime via the unsubscribe link or by emailing us).</li>
<li>To keep the site secure and improve it (legitimate interest; analytics only with your consent).</li>
<li>To meet legal and accounting obligations.</li>
</ul>
<h2>Sharing</h2>
<p>We never sell personal data. We share it only with service providers who help us operate this site (hosting, email delivery, analytics), bound by data-processing terms, and with authorities where the law requires it.</p>
<h2>International transfers</h2>
<p>Our providers may process data outside your country. Where that happens, we rely on appropriate safeguards such as standard contractual clauses.</p>
<h2>Retention</h2>
<p>Enquiry data is kept only as long as needed to handle your request and any follow-up engagement, then deleted. Newsletter data is kept until you unsubscribe. Server logs rotate automatically.</p>
<h2>Your rights</h2>
<p>You may request access to, correction of, deletion of, or a portable copy of your personal data; restrict or object to processing; withdraw consent at any time; and lodge a complaint with your local data-protection authority. Email us and we will respond within one business day.</p>
<h2>Cookies</h2>
<p>See our <a href="/cookies">Cookie Policy</a> for exactly what we set and how to change your choice.</p>
<h2>Changes</h2>
<p>We may update this policy as the site evolves; the latest version always lives on this page.</p>
HTML;
    }

    protected function cookieHtml(): string
    {
        return <<<'HTML'
<p>This Cookie Policy explains what cookies this website sets and how you control them. When you first visit, a consent banner lets you accept or decline non-essential cookies - nothing optional runs until you choose.</p>
<h2>Essential cookies (always on)</h2>
<ul>
<li><strong>akh-solutions-session</strong> - keeps your session working (forms, security). Expires when the session ends.</li>
<li><strong>XSRF-TOKEN</strong> - protects forms against cross-site request forgery.</li>
<li><strong>akh-cookie-consent</strong> - remembers the choice you made in the consent banner.</li>
</ul>
<h2>Analytics cookies (only with your consent)</h2>
<ul>
<li><strong>_ga / _ga_*</strong> - Google Analytics, loaded only after you click "Accept" in the consent banner. We enable IP anonymization. If you decline, these are never set.</li>
</ul>
<h2>Changing your mind</h2>
<p>To withdraw or change your choice, clear this site's data in your browser (or just the <em>akh-cookie-consent</em> entry in local storage) and reload - the banner will ask again. You can also block or delete cookies entirely through your browser settings; blocking essential cookies may break forms.</p>
<h2>Changes</h2>
<p>We may update this policy as the site evolves. The latest version always lives on this page.</p>
HTML;
    }

    protected function termsHtml(): string
    {
        return <<<'HTML'
<p>These Terms &amp; Conditions ("Terms") govern your access to and use of the AKH Solutions website (the "Site"). By accessing or using the Site you agree to be bound by these Terms and by our <a href="/privacy">Privacy Policy</a> and <a href="/cookies">Cookie Policy</a>. <strong>If you do not agree, do not use the Site.</strong></p>
<h2>1. Informational purposes only</h2>
<p>All content on the Site is provided for general information only. It does not constitute professional, technical, legal, or financial advice, and it is not a binding offer. Project scope, fees, timelines, and obligations exist only once agreed in a separate written agreement signed by both parties. Any examples, scenarios, or figures shown on the Site are illustrative.</p>
<h2>2. Use of the Site</h2>
<p>You may use the Site for lawful purposes only. You agree not to misuse the Site, interfere with its operation, attempt unauthorized access, scrape it at disruptive volume, or use it in any way that breaches applicable law.</p>
<h2>3. Intellectual property</h2>
<p>All content on the Site - text, designs, graphics, logos, and code - is owned by AKH Solutions or its licensors and is protected by intellectual-property laws. You may not reproduce or exploit it without our prior written consent. For client engagements, ownership of deliverables is governed exclusively by the relevant written agreement.</p>
<h2>4. No warranties</h2>
<p>The Site is provided <strong>"as is" and "as available", without warranties of any kind</strong>, whether express, implied, or statutory - including but not limited to warranties of accuracy, completeness, merchantability, fitness for a particular purpose, non-infringement, and uninterrupted or error-free availability. We may change, suspend, or discontinue any part of the Site at any time without notice.</p>
<h2>5. Limitation of liability</h2>
<p>To the maximum extent permitted by applicable law, AKH Solutions, its owners, employees, and contractors shall not be liable for any direct, indirect, incidental, special, consequential, punitive, or exemplary damages - including loss of profits, revenue, data, goodwill, or business interruption - arising out of or in connection with your use of (or inability to use) the Site or reliance on any content on it, even if advised of the possibility of such damages. Where liability cannot be excluded by law, our total aggregate liability is limited to the amount you paid us to use the Site (which is zero).</p>
<h2>6. Indemnification</h2>
<p>You agree to indemnify and hold harmless AKH Solutions from any claims, damages, liabilities, and expenses (including reasonable legal fees) arising from your use of the Site or your breach of these Terms.</p>
<h2>7. Third-party links and services</h2>
<p>The Site may link to third-party websites or load third-party services. We do not control them and accept no responsibility for their content, policies, or practices. Your use of them is at your own risk.</p>
<h2>8. Submissions</h2>
<p>Information you send through the contact form is handled as described in the Privacy Policy. Sending an enquiry creates no obligation on either party and no engagement, partnership, or professional relationship.</p>
<h2>9. Severability and waiver</h2>
<p>If any provision of these Terms is held unenforceable, the remaining provisions remain in full force. Our failure to enforce any right is not a waiver of it.</p>
<h2>10. Governing law</h2>
<p>These Terms are governed by the laws of the Republic of Armenia, without regard to conflict-of-law rules. Any dispute arising from these Terms or the Site is subject to the exclusive jurisdiction of the courts of the Republic of Armenia.</p>
<h2>11. Changes</h2>
<p>We may update these Terms at any time by posting the revised version on this page. Continued use of the Site after changes are posted constitutes acceptance of the updated Terms.</p>
<h2>12. Contact</h2>
<p>Questions about these Terms? Use the contact details on our <a href="/contact">contact page</a>.</p>
HTML;
    }
}
