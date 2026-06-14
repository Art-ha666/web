<?php

/*
| Editable content schema for the Process page (/process).
| Field defaults mirror the original copy, so the page renders unchanged
| until an admin edits it in Admin → Page content → Process.
*/

return [
    'label' => 'Process',
    'route' => '/process',
    'sections' => [

        'seo' => [
            'label' => 'SEO',
            'fields' => [
                'title' => ['type' => 'text', 'label' => 'Meta title', 'default' => 'Process - AKH Solutions'],
                'description' => ['type' => 'textarea', 'label' => 'Meta description', 'default' => 'How AKH Solutions works - from discovery to delivery, with clear engagement models and principles that compound.'],
            ],
        ],

        'hero' => [
            'label' => 'Hero',
            'fields' => [
                'eyebrow' => ['type' => 'text', 'label' => 'Eyebrow', 'default' => 'How we work'],
                'title' => ['type' => 'text', 'label' => 'Title', 'default' => 'A method built to'],
                'title_accent' => ['type' => 'text', 'label' => 'Title (gradient part)', 'default' => 'stay ahead'],
                'body' => ['type' => 'textarea', 'label' => 'Intro paragraph', 'default' => 'Process is not paperwork. It is the discipline that lets a small expert team move fast without breaking what matters - turning ambiguity into durable, production software.'],
                'fallback' => ['type' => 'textarea', 'label' => 'Secondary paragraph (when no rich text)', 'default' => 'We keep the loop tight: understand the real problem, prove the riskiest part first, then build in deliberate increments you can see and steer every week. No black boxes, no surprises at the finish line - just momentum you can measure.'],
            ],
        ],

        'timeline' => [
            'label' => 'Process timeline',
            'toggle' => true,
            'fields' => [
                'eyebrow' => ['type' => 'text', 'label' => 'Eyebrow', 'default' => 'The path'],
                'title' => ['type' => 'text', 'label' => 'Title', 'default' => 'From first conversation to confident launch'],
                'subtitle' => ['type' => 'textarea', 'label' => 'Subtitle', 'default' => 'Four phases, each ending in something real you can hold, test and decide on.'],
            ],
        ],

        'engagement' => [
            'label' => 'Engagement models',
            'toggle' => true,
            'fields' => [
                'eyebrow' => ['type' => 'text', 'label' => 'Eyebrow', 'default' => 'Engagement models'],
                'title' => ['type' => 'text', 'label' => 'Title', 'default' => 'Work with us the way that fits'],
                'subtitle' => ['type' => 'textarea', 'label' => 'Subtitle', 'default' => 'Three shapes of collaboration, one standard of craft.'],
                'items' => ['type' => 'repeater', 'label' => 'Models', 'subfields' => [
                    'icon' => ['type' => 'text', 'label' => 'Icon name'],
                    'title' => ['type' => 'text', 'label' => 'Title'],
                    'description' => ['type' => 'textarea', 'label' => 'Description'],
                    'points' => ['type' => 'list', 'label' => 'Bullet points'],
                ], 'default' => [
                    [
                        'icon' => 'compass',
                        'title' => 'Fixed scope',
                        'description' => 'A defined outcome, a fixed timeline, and a price that holds. Best when the destination is clear.',
                        'points' => ['Scoped milestones with weekly demos', 'Fixed budget, no surprise invoices', 'Defined acceptance criteria'],
                    ],
                    [
                        'icon' => 'users',
                        'title' => 'Dedicated team',
                        'description' => 'A cross-functional pod that owns delivery end to end and delivers against your roadmap.',
                        'points' => ['Experienced engineers, design and PM', 'Owns velocity and quality', 'Roadmap-driven, sprint by sprint'],
                    ],
                    [
                        'icon' => 'zap',
                        'title' => 'Team extension',
                        'description' => 'Embed our specialists directly into your squads to add throughput without the ramp.',
                        'points' => ['Plugs into your tools and rituals', 'Scale up or down monthly', 'Knowledge stays in your team'],
                    ],
                ]],
            ],
        ],

        'principles' => [
            'label' => 'Why it works',
            'toggle' => true,
            'fields' => [
                'eyebrow' => ['type' => 'text', 'label' => 'Eyebrow', 'default' => 'Why it works'],
                'title' => ['type' => 'text', 'label' => 'Title', 'default' => 'The principles underneath'],
                'subtitle' => ['type' => 'textarea', 'label' => 'Subtitle', 'default' => 'The habits that keep our work ahead of the curve - and out of the rework pile.'],
                'items' => ['type' => 'repeater', 'label' => 'Principles', 'subfields' => [
                    'icon' => ['type' => 'text', 'label' => 'Icon name'],
                    'title' => ['type' => 'text', 'label' => 'Title'],
                    'description' => ['type' => 'textarea', 'label' => 'Description'],
                ], 'default' => [
                    ['icon' => 'sparkles', 'title' => 'Release to learn', 'description' => 'Small, frequent releases turn assumptions into evidence before they become expensive.'],
                    ['icon' => 'star', 'title' => 'Experience by default', 'description' => 'Every line is written and reviewed by engineers who have shipped production systems before.'],
                    ['icon' => 'rocket', 'title' => 'Built to outlast us', 'description' => 'Clean architecture, tests and docs so your team can run fast long after we hand off.'],
                ]],
            ],
        ],

        'closing' => [
            'label' => 'Closing CTA (fallback)',
            'fields' => [
                'eyebrow' => ['type' => 'text', 'label' => 'Eyebrow', 'default' => "Let's begin"],
                'title' => ['type' => 'text', 'label' => 'Title', 'default' => 'See the method on your problem.'],
                'subtitle' => ['type' => 'textarea', 'label' => 'Subtitle', 'default' => 'Bring the hard part. We will walk you through exactly how we would approach it.'],
                'button_label' => ['type' => 'text', 'label' => 'Button label', 'default' => 'Book a discovery call'],
            ],
        ],

    ],
];
