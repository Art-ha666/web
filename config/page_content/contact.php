<?php

/*
| Editable content schema for the Contact page (/contact).
| The form fields stay validated server-side; every visible string is editable.
*/

return [
    'label' => 'Contact',
    'route' => '/contact',
    'sections' => [

        'seo' => [
            'label' => 'SEO',
            'fields' => [
                'title' => ['type' => 'text', 'label' => 'Meta title', 'default' => 'Contact - AKH Solutions'],
                'description' => ['type' => 'textarea', 'label' => 'Meta description', 'default' => 'Tell us about the problem. A 30-minute call with an engineer - we reply to every serious enquiry within one business day.'],
            ],
        ],

        'hero' => [
            'label' => 'Intro',
            'fields' => [
                'eyebrow' => ['type' => 'text', 'label' => 'Eyebrow', 'default' => 'Start the conversation'],
                'title' => ['type' => 'text', 'label' => 'Title', 'default' => "Tell us what you're"],
                'title_accent' => ['type' => 'text', 'label' => 'Title (gradient part)', 'default' => 'building.'],
                'subtitle' => ['type' => 'textarea', 'label' => 'Subtitle', 'default' => 'A 30-minute call with an engineer who has built what you need - no pitch deck, no pressure. Unusual problems welcome.'],
            ],
        ],

        'promises' => [
            'label' => 'Promise bullets',
            'toggle' => true,
            'fields' => [
                'items' => ['type' => 'list', 'label' => 'Bullets', 'default' => [
                    'We reply within one business day.',
                    'An engineer joins the first call - not a salesperson.',
                    'Your IP and your code stay yours.',
                    'NDA-friendly from the first conversation.',
                ]],
            ],
        ],

        'info' => [
            'label' => 'Contact cards',
            'fields' => [
                'email_label' => ['type' => 'text', 'label' => 'Email card label', 'default' => 'Email'],
                'phone_label' => ['type' => 'text', 'label' => 'Phone card label', 'default' => 'Phone'],
                'locations_label' => ['type' => 'text', 'label' => 'Locations card label', 'default' => 'Where we work'],
            ],
        ],

        'form' => [
            'label' => 'Form',
            'fields' => [
                'name_label' => ['type' => 'text', 'label' => 'Name label', 'default' => 'Name *'],
                'name_placeholder' => ['type' => 'text', 'label' => 'Name placeholder', 'default' => 'Jane Doe'],
                'email_label' => ['type' => 'text', 'label' => 'Email label', 'default' => 'Work email *'],
                'email_placeholder' => ['type' => 'text', 'label' => 'Email placeholder', 'default' => 'jane@company.com'],
                'company_label' => ['type' => 'text', 'label' => 'Company label', 'default' => 'Company'],
                'company_placeholder' => ['type' => 'text', 'label' => 'Company placeholder', 'default' => 'Acme Inc.'],
                'budget_label' => ['type' => 'text', 'label' => 'Budget label', 'default' => 'Budget'],
                'budget_placeholder' => ['type' => 'text', 'label' => 'Budget empty option', 'default' => 'Select a range'],
                'budget_ranges' => ['type' => 'list', 'label' => 'Budget ranges', 'default' => [
                    'Under $10k',
                    '$10k-$25k',
                    '$25k-$50k',
                    '$50k-$100k',
                    '$100k+',
                    'Not sure yet',
                ]],
                'service_label' => ['type' => 'text', 'label' => 'Service label', 'default' => 'Interested in'],
                'service_placeholder' => ['type' => 'text', 'label' => 'Service empty option', 'default' => 'Select a service'],
                'message_label' => ['type' => 'text', 'label' => 'Message label', 'default' => 'What are you building? *'],
                'message_placeholder' => ['type' => 'text', 'label' => 'Message placeholder', 'default' => 'A braindump is perfect - what, why, and by when.'],
                'consent_label' => ['type' => 'textarea', 'label' => 'Consent checkbox (HTML allowed)', 'default' => 'I agree to AKH Solutions processing my data to respond to this enquiry, as described in the <a href="/privacy">Privacy Policy</a> and <a href="/terms">Terms &amp; Conditions</a>. *'],
                'consent_error' => ['type' => 'text', 'label' => 'Consent error message', 'default' => 'Please accept the privacy terms so we can reply.'],
                'marketing_label' => ['type' => 'text', 'label' => 'Marketing checkbox', 'default' => 'Send me occasional engineering notes - no spam, unsubscribe anytime (optional).'],
                'sending_label' => ['type' => 'text', 'label' => 'Sending state', 'default' => 'Sending…'],
                'submit_label' => ['type' => 'text', 'label' => 'Submit button', 'default' => 'Book a discovery call'],
                'success_title' => ['type' => 'text', 'label' => 'Success title', 'default' => 'Message received.'],
                'success_body' => ['type' => 'textarea', 'label' => 'Success message', 'default' => "Thanks - we'll reply within one business day. Talk soon."],
                'success_flash' => ['type' => 'text', 'label' => 'Success flash ({name} = sender name)', 'default' => "Thanks, {name} - we'll reply within one business day."],
                'reassurance' => ['type' => 'list', 'label' => 'Reassurance row (under submit)', 'default' => [
                    'No obligation - a plan either way',
                    'Confidential & NDA-friendly',
                    'An engineer replies, not a bot',
                ]],
            ],
        ],

    ],
];
