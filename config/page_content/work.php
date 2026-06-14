<?php

/*
| Editable content schema for the Work page (/work) and the case-study
| chrome on /work/{slug}. Field defaults mirror the live copy, so the
| pages render unchanged until an admin edits them in
| Admin → Page content → Work.
*/

return [
    'label' => 'Work',
    'route' => '/work',
    'sections' => [

        'seo' => [
            'label' => 'SEO',
            'fields' => [
                'title' => ['type' => 'text', 'label' => 'Meta title', 'default' => 'Work - AKH Solutions'],
                'description' => ['type' => 'textarea', 'label' => 'Meta description', 'default' => 'Representative engagements from AKH Solutions - how we approach hard problems across web, AI, embedded, and cloud, and what shipping looks like.'],
            ],
        ],

        'hero' => [
            'label' => 'Hero',
            'fields' => [
                'eyebrow' => ['type' => 'text', 'label' => 'Eyebrow', 'default' => 'Selected work'],
                'title' => ['type' => 'text', 'label' => 'Title', 'default' => 'Built,'],
                'title_accent' => ['type' => 'text', 'label' => 'Title (gradient part)', 'default' => 'measured'],
                'title_suffix' => ['type' => 'text', 'label' => 'Title (after gradient)', 'default' => ', shipped.'],
                'body' => ['type' => 'textarea', 'label' => 'Intro paragraph', 'default' => 'Representative engagements that show how we work - anonymized where client agreements require it. Every engagement ends in something live and accountable.'],
            ],
        ],

        'filter' => [
            'label' => 'Industry filter',
            'fields' => [
                'all_label' => ['type' => 'text', 'label' => '"All" filter label', 'default' => 'All'],
                'aria_label' => ['type' => 'text', 'label' => 'Filter aria-label (accessibility)', 'default' => 'Filter work by industry'],
            ],
        ],

        'empty' => [
            'label' => 'Empty state',
            'fields' => [
                'title' => ['type' => 'text', 'label' => 'Title', 'default' => 'Nothing here yet'],
                'body' => ['type' => 'textarea', 'label' => 'Body', 'default' => "We haven't published an engagement in this industry yet. If your problem lives here, we'd still like to hear about it."],
                'button_label' => ['type' => 'text', 'label' => 'Button label', 'default' => 'View all work'],
            ],
        ],

        'cta' => [
            'label' => 'Contact CTA',
            'toggle' => true,
            'fields' => [
                'eyebrow' => ['type' => 'text', 'label' => 'Eyebrow', 'default' => "What's next"],
                'title' => ['type' => 'text', 'label' => 'Title', 'default' => 'Have a problem worth'],
                'title_accent' => ['type' => 'text', 'label' => 'Title (gradient part)', 'default' => 'solving'],
                'title_suffix' => ['type' => 'text', 'label' => 'Title (after gradient)', 'default' => '?'],
                'body' => ['type' => 'textarea', 'label' => 'Body', 'default' => "Tell us where you want to be. We'll scope the fastest credible path to get there."],
                'button_label' => ['type' => 'text', 'label' => 'Button label', 'default' => 'Book a discovery call'],
            ],
        ],

        'case' => [
            'label' => 'Case study page',
            'fields' => [
                'back_label' => ['type' => 'text', 'label' => 'Back link label', 'default' => 'All work'],
                'client_label' => ['type' => 'text', 'label' => 'Client label', 'default' => 'Client:'],
                'video_label' => ['type' => 'text', 'label' => 'Video button label', 'default' => 'Watch the walkthrough'],
                'challenge_title' => ['type' => 'text', 'label' => 'Challenge section title', 'default' => 'The challenge'],
                'approach_title' => ['type' => 'text', 'label' => 'Approach section title', 'default' => 'Our approach'],
                'architecture_title' => ['type' => 'text', 'label' => 'Architecture section title', 'default' => 'Architecture'],
                'results_eyebrow' => ['type' => 'text', 'label' => 'Results eyebrow', 'default' => 'Outcomes'],
                'tech_eyebrow' => ['type' => 'text', 'label' => 'Tech stack eyebrow', 'default' => 'Tech stack'],
                'testimonials_eyebrow' => ['type' => 'text', 'label' => 'Testimonials eyebrow', 'default' => 'In their words'],
                'related_eyebrow' => ['type' => 'text', 'label' => 'Related work eyebrow', 'default' => 'Related work'],
                'related_cta' => ['type' => 'text', 'label' => 'Related card link label', 'default' => 'View case study'],
                'disclosure' => ['type' => 'textarea', 'label' => 'Anonymization disclosure', 'default' => 'Representative engagement - details anonymized under client agreements.'],
            ],
        ],

    ],
];
