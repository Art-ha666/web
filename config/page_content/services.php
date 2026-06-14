<?php

/*
| Editable content schema for the Services page (/services).
| Field defaults mirror the original copy, so the page renders unchanged
| until an admin edits it in Admin → Page content → Services.
| The `detail` section drives the chrome of individual service pages
| (/services/{slug}) rendered by ServiceShow.vue.
*/

return [
    'label' => 'Services',
    'route' => '/services',
    'sections' => [

        'seo' => [
            'label' => 'SEO',
            'fields' => [
                'title' => ['type' => 'text', 'label' => 'Meta title', 'default' => 'Services - AKH Solutions'],
                'description' => ['type' => 'textarea', 'label' => 'Meta description', 'default' => 'Software, AI, and IT engineering services: web & mobile, AI & data, embedded & robotics, IoT, desktop, networking, and cloud. One team from idea to production.'],
            ],
        ],

        'hero' => [
            'label' => 'Hero',
            'fields' => [
                'eyebrow' => ['type' => 'text', 'label' => 'Eyebrow', 'default' => 'What we do'],
                'title' => ['type' => 'text', 'label' => 'Title', 'default' => 'If it runs on code,'],
                'title_accent' => ['type' => 'text', 'label' => 'Title (gradient part)', 'default' => 'we build it'],
                'body' => ['type' => 'textarea', 'label' => 'Intro paragraph', 'default' => 'Web platforms, mobile apps, AI systems, embedded firmware, desktop software, robots, IoT fleets - and the networks underneath them. One compact team covers the whole stack, and the people who scope your project are the people who build it.'],
            ],
        ],

        'grid' => [
            'label' => 'Service grid',
            'fields' => [
                'explore_label' => ['type' => 'text', 'label' => 'Explore link label', 'default' => 'Explore service'],
            ],
        ],

        'detail' => [
            'label' => 'Service detail pages',
            'fields' => [
                'back_label' => ['type' => 'text', 'label' => 'Back button label', 'default' => 'All services'],
                'cta_label' => ['type' => 'text', 'label' => 'CTA button label', 'default' => 'Book a discovery call'],
                'metric_caption' => ['type' => 'text', 'label' => 'Metric caption', 'default' => 'What to expect'],
                'benefits_eyebrow' => ['type' => 'text', 'label' => 'Benefits eyebrow', 'default' => 'Why teams choose this'],
                'benefits_title' => ['type' => 'text', 'label' => 'Benefits title', 'default' => 'What you get'],
                'tech_title' => ['type' => 'text', 'label' => 'Tech stack title', 'default' => 'Tech stack'],
                'tech_note' => ['type' => 'textarea', 'label' => 'Tech stack note', 'default' => 'Pragmatic tooling, chosen for longevity - not hype.'],
                'related_eyebrow' => ['type' => 'text', 'label' => 'Related work eyebrow', 'default' => 'Proof of work'],
                'related_title' => ['type' => 'text', 'label' => 'Related work title', 'default' => 'Related work'],
                'related_view_all' => ['type' => 'text', 'label' => 'Related work view-all label', 'default' => 'All work'],
                'more_eyebrow' => ['type' => 'text', 'label' => 'More services eyebrow', 'default' => 'Keep exploring'],
                'more_title' => ['type' => 'text', 'label' => 'More services title', 'default' => 'More services'],
                'closing_eyebrow' => ['type' => 'text', 'label' => 'Closing eyebrow', 'default' => "Let's build it together"],
                'closing_title_prefix' => ['type' => 'text', 'label' => 'Closing title (before service name)', 'default' => 'Ready to put'],
                'closing_title_suffix' => ['type' => 'text', 'label' => 'Closing title (after service name)', 'default' => 'to work?'],
                'closing_body' => ['type' => 'textarea', 'label' => 'Closing body', 'default' => "Tell us where you're headed. We'll map the fastest credible path to get there."],
            ],
        ],

        'cta' => [
            'label' => 'Contact CTA',
            'toggle' => true,
            'fields' => [
                'eyebrow' => ['type' => 'text', 'label' => 'Eyebrow', 'default' => 'Ready when you are'],
                'title' => ['type' => 'text', 'label' => 'Title', 'default' => "Tell us what you're building."],
                'body' => ['type' => 'textarea', 'label' => 'Body', 'default' => "Thirty minutes with an engineer - not a salesperson. We'll tell you honestly whether we're the right team, and leave you with a sharper plan either way."],
                'button_label' => ['type' => 'text', 'label' => 'Button label', 'default' => 'Book a discovery call'],
            ],
        ],

    ],
];
