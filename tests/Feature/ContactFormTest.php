<?php

use App\Mail\LeadReceived;
use App\Models\Lead;
use Illuminate\Support\Facades\Mail;

function validLead(array $overrides = []): array
{
    return array_merge([
        'name' => 'Jane Doe',
        'business_email' => 'jane@acme.com',
        'company' => 'Acme Inc.',
        // Second default budget range from config/page_content/contact.php.
        'budget_range' => '$10k-$25k',
        'service_interest' => 'Custom Software',
        'message' => 'We want to build a real-time settlement platform.',
        'consent_data_processing' => true,
        'consent_marketing' => false,
        'website' => '',
    ], $overrides);
}

it('stores a lead and emails the team', function () {
    Mail::fake();

    $this->post('/contact', validLead())
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseHas('leads', [
        'business_email' => 'jane@acme.com',
        'status' => 'new',
        'consent_data_processing' => true,
    ]);

    Mail::assertSent(LeadReceived::class);
});

it('requires a consent and a message', function () {
    $this->post('/contact', validLead(['consent_data_processing' => false, 'message' => '']))
        ->assertSessionHasErrors(['consent_data_processing', 'message']);

    expect(Lead::count())->toBe(0);
});

it('rejects honeypot submissions', function () {
    Mail::fake();

    $this->post('/contact', validLead(['website' => 'http://spam.example']))
        ->assertSessionHasErrors('website');

    expect(Lead::count())->toBe(0);
    Mail::assertNothingSent();
});

it('rejects an invalid budget range', function () {
    $this->post('/contact', validLead(['budget_range' => 'A trillion dollars']))
        ->assertSessionHasErrors('budget_range');
});
