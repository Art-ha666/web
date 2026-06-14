<?php

/*
| Editable content schema for the About page (/about).
| Field defaults mirror the original copy, so the page renders unchanged
| until an admin edits it in Admin → Page content → About.
*/

return [
    'label' => 'About',
    'route' => '/about',
    'sections' => [

        'seo' => [
            'label' => 'SEO',
            'fields' => [
                'title' => ['type' => 'text', 'label' => 'Meta title', 'default' => 'About - AKH Solutions'],
                'description' => ['type' => 'textarea', 'label' => 'Meta description', 'default' => 'AKH Solutions is a compact engineering agency solving hard problems across web, AI, embedded, robotics, IoT, networking, and cloud.'],
            ],
        ],

        'hero' => [
            'label' => 'Hero',
            'fields' => [
                'eyebrow' => ['type' => 'text', 'label' => 'Eyebrow', 'default' => 'Who we are'],
                'title' => ['type' => 'text', 'label' => 'Title', 'default' => 'Engineers for the'],
                'title_accent' => ['type' => 'text', 'label' => 'Title (gradient part)', 'default' => 'hard problems.'],
                'body' => ['type' => 'textarea', 'label' => 'Intro paragraph', 'default' => 'AKH Solutions is a compact engineering agency. We take on the projects most agencies turn down - the odd protocols, the legacy swamps, the robots, the systems that must not fail - and ship them to production.'],
            ],
        ],

        'intro' => [
            'label' => 'Story',
            'toggle' => true,
            'fields' => [
                'body' => ['type' => 'textarea', 'label' => 'Body (HTML allowed)', 'default' => '<p>AKH Solutions exists because too much software is built by the cheapest available hands and handed off the moment it compiles. We do the opposite. Every engagement is led by engineers who have built, scaled, and maintained production systems across web, AI, embedded hardware, and cloud - people who treat your codebase as something they will have to live with, because they will.</p><p>Our range is the point: when one team understands the firmware, the API, and the dashboard, nothing gets lost between vendors. We move fast, but we never trade away the things that make a system durable.</p>'],
            ],
        ],

        'stats' => [
            'label' => 'Stats band',
            'toggle' => true,
            'fields' => [],
        ],

        'principles' => [
            'label' => 'Engineering principles',
            'toggle' => true,
            'fields' => [
                'eyebrow' => ['type' => 'text', 'label' => 'Eyebrow', 'default' => 'How we work'],
                'title' => ['type' => 'text', 'label' => 'Title', 'default' => 'Engineering principles'],
                'subtitle' => ['type' => 'textarea', 'label' => 'Subtitle', 'default' => 'Four commitments that shape every decision we make on the way to production.'],
                'items' => ['type' => 'repeater', 'label' => 'Principles', 'subfields' => [
                    'icon' => ['type' => 'text', 'label' => 'Icon name'],
                    'title' => ['type' => 'text', 'label' => 'Title'],
                    'body' => ['type' => 'textarea', 'label' => 'Body'],
                ], 'default' => [
                    ['icon' => 'compass', 'title' => 'Make the early decisions', 'body' => 'We commit to architecture, boundaries, and trade-offs at the start - when changing direction is still cheap.'],
                    ['icon' => 'pen-tool', 'title' => 'Document the trade-offs', 'body' => 'Every meaningful choice is written down, so the team that inherits the system understands the why, not just the what.'],
                    ['icon' => 'server', 'title' => 'Own the reliability', 'body' => 'Uptime, observability, and on-call discipline are part of the build - not an afterthought handed to someone else.'],
                    ['icon' => 'rocket', 'title' => 'Demo every Friday', 'body' => 'Small, frequent releases beat big-bang launches. We keep a steady cadence so progress is always visible.'],
                ]],
            ],
        ],

        'team_preview' => [
            'label' => 'Team preview',
            'toggle' => true,
            'fields' => [
                'eyebrow' => ['type' => 'text', 'label' => 'Eyebrow', 'default' => 'The people'],
                'title' => ['type' => 'text', 'label' => 'Title', 'default' => 'The engineers behind the work'],
                'subtitle' => ['type' => 'textarea', 'label' => 'Subtitle', 'default' => 'A small bench covering web, AI, embedded, and infrastructure - the same people from the first call to production.'],
                'link_label' => ['type' => 'text', 'label' => 'Link label', 'default' => 'Meet the full team'],
            ],
        ],

        'closing' => [
            'label' => 'Closing CTA (fallback)',
            'fields' => [
                'title' => ['type' => 'text', 'label' => 'Title', 'default' => "Let's build something worth maintaining."],
                'button_label' => ['type' => 'text', 'label' => 'Button label', 'default' => 'Book a discovery call'],
            ],
        ],

    ],
];
