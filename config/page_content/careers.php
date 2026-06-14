<?php

/*
| Editable content schema for the Careers page (/careers) and the
| job-detail page (/careers/{job}) chrome.
| Field defaults mirror the live copy, so the pages render unchanged
| until an admin edits them in Admin → Page content → Careers.
| The open-roles list itself is DB-driven (JobPosting) and not editable here.
*/

return [
    'label' => 'Careers',
    'route' => '/careers',
    'sections' => [

        'seo' => [
            'label' => 'SEO',
            'fields' => [
                'title' => ['type' => 'text', 'label' => 'Meta title', 'default' => 'Careers - AKH Solutions'],
                'description' => ['type' => 'textarea', 'label' => 'Meta description', 'default' => 'Open roles at AKH Solutions. Small team, real ownership, no hand-offs - and more of the stack than most jobs will ever let you touch.'],
            ],
        ],

        'hero' => [
            'label' => 'Hero',
            'fields' => [
                'eyebrow' => ['type' => 'text', 'label' => 'Eyebrow', 'default' => 'Careers'],
                'title' => ['type' => 'text', 'label' => 'Title', 'default' => 'Do the best work'],
                'title_accent' => ['type' => 'text', 'label' => 'Title (gradient part)', 'default' => 'of your career.'],
                'body' => ['type' => 'textarea', 'label' => 'Intro paragraph', 'default' => "Small team, real ownership, no hand-offs. You'll scope problems, build them, and ship them - across more of the stack than most jobs will ever let you touch. If that's how you like to work, we'd like to meet you."],
            ],
        ],

        'empty_state' => [
            'label' => 'No-open-roles state',
            'fields' => [
                'icon' => ['type' => 'text', 'label' => 'Icon name', 'default' => 'users'],
                'title' => ['type' => 'text', 'label' => 'Title', 'default' => 'No open roles right now - introduce yourself anyway.'],
                'body' => ['type' => 'textarea', 'label' => 'Body', 'default' => "We hire ahead of need when we meet exceptional engineers. Tell us what you've built and how you like to work - we read every note and reply to the ones that fit."],
                'button_label' => ['type' => 'text', 'label' => 'Button label', 'default' => 'Introduce yourself'],
            ],
        ],

        'roles' => [
            'label' => 'Roles list',
            'fields' => [
                'view_label' => ['type' => 'text', 'label' => 'View-role link label', 'default' => 'View role'],
            ],
        ],

        'closing' => [
            'label' => 'Closing CTA',
            'toggle' => true,
            'fields' => [
                'eyebrow' => ['type' => 'text', 'label' => 'Eyebrow', 'default' => 'Not seeing your role?'],
                'title' => ['type' => 'text', 'label' => 'Title', 'default' => 'Tell us what you want to build.'],
                'body' => ['type' => 'textarea', 'label' => 'Body', 'default' => "If you've shipped production systems and want to own problems end to end, start a conversation. We'll figure out where you fit."],
                'button_label' => ['type' => 'text', 'label' => 'Button label', 'default' => 'Book a discovery call'],
            ],
        ],

        'job' => [
            'label' => 'Job detail page',
            'fields' => [
                'back_label' => ['type' => 'text', 'label' => 'Back link label', 'default' => 'Careers'],
                'apply_label' => ['type' => 'text', 'label' => 'Apply button label', 'default' => 'Apply for this role'],
                'responsibilities_title' => ['type' => 'text', 'label' => 'Responsibilities title', 'default' => "What you'll do"],
                'requirements_title' => ['type' => 'text', 'label' => 'Requirements title', 'default' => 'What you bring'],
                'tech_title' => ['type' => 'text', 'label' => 'Tech stack title', 'default' => 'Tech stack'],
                'salary_label' => ['type' => 'text', 'label' => 'Salary label', 'default' => 'Compensation'],
                'cta_title' => ['type' => 'text', 'label' => 'CTA title', 'default' => "Think you're a"],
                'cta_title_accent' => ['type' => 'text', 'label' => 'CTA title (gradient part)', 'default' => 'great fit?'],
                'cta_body' => ['type' => 'textarea', 'label' => 'CTA body', 'default' => 'We read every application. Tell us about your work and why this role excites you.'],
                'view_all_label' => ['type' => 'text', 'label' => 'View-all button label', 'default' => 'View all roles'],
            ],
        ],

    ],
];
