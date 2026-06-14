<?php

/*
| Editable content schema for the Team page (/team).
| Field defaults mirror the original copy, so the page renders unchanged
| until an admin edits it in Admin → Page content → Team.
*/

return [
    'label' => 'Team',
    'route' => '/team',
    'sections' => [

        'seo' => [
            'label' => 'SEO',
            'fields' => [
                'title' => ['type' => 'text', 'label' => 'Meta title', 'default' => 'The team - AKH Solutions'],
                'description' => ['type' => 'textarea', 'label' => 'Meta description', 'default' => 'The people who scope your work are the people who build it - a compact team covering web, AI, embedded, infrastructure, and everything between.'],
            ],
        ],

        'hero' => [
            'label' => 'Hero',
            'fields' => [
                'eyebrow' => ['type' => 'text', 'label' => 'Eyebrow', 'default' => "Who you'll work with"],
                'title' => ['type' => 'text', 'label' => 'Title', 'default' => 'A compact team,'],
                'title_accent' => ['type' => 'text', 'label' => 'Title (gradient part)', 'default' => 'full coverage.'],
                'body' => ['type' => 'textarea', 'label' => 'Intro paragraph', 'default' => 'The people who scope your work are the people who build it. No bait-and-switch, no hand-offs - a small bench of engineers covering web, AI, embedded, infrastructure, and everything between.'],
            ],
        ],

        'hero_stats' => [
            'label' => 'Hero stats band',
            'toggle' => true,
            'fields' => [
                'years_suffix' => ['type' => 'text', 'label' => 'Avg. years suffix', 'default' => '+'],
                'years_label' => ['type' => 'text', 'label' => 'Avg. years label', 'default' => 'Avg. years of experience'],
                'engineers_label' => ['type' => 'text', 'label' => 'Engineers label', 'default' => 'Engineers'],
                'handoffs_label' => ['type' => 'text', 'label' => 'Hand-offs label', 'default' => 'Hand-offs mid-project'],
                'handoffs_value' => ['type' => 'text', 'label' => 'Hand-offs value', 'default' => '0'],
            ],
        ],

        'roster' => [
            'label' => 'Roster',
            'fields' => [
                'empty' => ['type' => 'text', 'label' => 'Empty state message', 'default' => 'The roster is being introduced. Check back shortly.'],
            ],
        ],

        'cta' => [
            'label' => 'Contact CTA',
            'toggle' => true,
            'fields' => [
                'eyebrow' => ['type' => 'text', 'label' => 'Eyebrow', 'default' => "We're hiring"],
                'title' => ['type' => 'text', 'label' => 'Title', 'default' => 'Engineer who hates hand-offs too?'],
                'body' => ['type' => 'textarea', 'label' => 'Body', 'default' => "We grow slowly and deliberately. If you have years of building behind you and want to own work end-to-end, we'd like to talk."],
                'primary_label' => ['type' => 'text', 'label' => 'Primary button label', 'default' => 'Apply to join'],
                'secondary_label' => ['type' => 'text', 'label' => 'Secondary button label', 'default' => 'Book a discovery call'],
                'email_prefix' => ['type' => 'text', 'label' => 'Email line prefix', 'default' => 'Or reach us directly at'],
            ],
        ],

    ],
];
