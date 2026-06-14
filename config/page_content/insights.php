<?php

/*
| Editable content schema for the Insights page (/insights) and the
| article chrome on /insights/{slug}. Field defaults mirror the live copy,
| so pages render unchanged until an admin edits them in
| Admin → Page content → Insights.
*/

return [
    'label' => 'Insights',
    'route' => '/insights',
    'sections' => [

        'seo' => [
            'label' => 'SEO',
            'fields' => [
                'title' => ['type' => 'text', 'label' => 'Meta title', 'default' => 'Insights - Engineering notes from AKH Solutions'],
                'description' => ['type' => 'textarea', 'label' => 'Meta description', 'default' => 'Field notes on architecture, AI, embedded systems, and delivery - written by the engineers at AKH Solutions who do the work.'],
            ],
        ],

        'hero' => [
            'label' => 'Hero',
            'fields' => [
                'eyebrow' => ['type' => 'text', 'label' => 'Eyebrow', 'default' => 'Insights'],
                'title' => ['type' => 'text', 'label' => 'Title', 'default' => 'Engineering'],
                'title_accent' => ['type' => 'text', 'label' => 'Title (gradient part)', 'default' => 'notes.'],
                'body' => ['type' => 'textarea', 'label' => 'Intro paragraph', 'default' => 'Hard-won lessons on architecture, delivery, AI, and hardware - written by the engineers who do the work.'],
            ],
        ],

        'featured' => [
            'label' => 'Featured article',
            'toggle' => true,
            'fields' => [
                'label' => ['type' => 'text', 'label' => 'Featured label', 'default' => 'Featured'],
                'cta_label' => ['type' => 'text', 'label' => 'Read link label', 'default' => 'Read the note'],
            ],
        ],

        'newsletter' => [
            'label' => 'Newsletter CTA',
            'toggle' => true,
            'fields' => [
                'icon' => ['type' => 'text', 'label' => 'Icon name', 'default' => 'mail'],
                'title' => ['type' => 'text', 'label' => 'Title', 'default' => 'Engineering notes,'],
                'title_accent' => ['type' => 'text', 'label' => 'Title (gradient part)', 'default' => 'straight to your inbox.'],
                'body' => ['type' => 'textarea', 'label' => 'Body', 'default' => "No fluff, no fixed cadence - just the occasional deep dive worth your time. Tell us what you're building and we'll keep you in the loop."],
                'button_label' => ['type' => 'text', 'label' => 'Button label', 'default' => 'Subscribe & start a conversation'],
            ],
        ],

        'article' => [
            'label' => 'Article page chrome',
            'fields' => [
                'back_label' => ['type' => 'text', 'label' => 'Back link label', 'default' => 'Insights'],
                'min_read_suffix' => ['type' => 'text', 'label' => 'Reading time suffix', 'default' => 'min read'],
                'author_label' => ['type' => 'text', 'label' => 'Author card label', 'default' => 'Written by'],
                'related_eyebrow' => ['type' => 'text', 'label' => 'Related section eyebrow', 'default' => 'Keep reading'],
                'related_title' => ['type' => 'text', 'label' => 'Related section title', 'default' => 'Related reading'],
                'related_cta' => ['type' => 'text', 'label' => 'Related card link label', 'default' => 'Read article'],
            ],
        ],

    ],
];
