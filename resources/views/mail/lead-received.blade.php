<x-mail::message>
# New discovery-call lead

A new lead just came in from the AKH website.

**Name:** {{ $lead->name }}
**Email:** {{ $lead->business_email }}
**Company:** {{ $lead->company ?: '-' }}
**Phone:** {{ $lead->phone ?: '-' }}
**Budget:** {{ $lead->budget_range ?: '-' }}
**Interested in:** {{ $lead->service_interest ?: '-' }}
**Source page:** {{ $lead->source_page ?: '-' }}

**Message:**

{{ $lead->message }}

<x-mail::button :url="config('app.url').'/admin/leads'">
View in admin
</x-mail::button>

Marketing consent: {{ $lead->consent_marketing ? 'yes' : 'no' }} · Data-processing consent: {{ $lead->consent_data_processing ? 'yes' : 'no' }}

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
