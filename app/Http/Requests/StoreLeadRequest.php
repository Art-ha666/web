<?php

namespace App\Http\Requests;

use App\Services\PageContentService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var array<int, string> $budgetRanges */
        $budgetRanges = app(PageContentService::class)->for('contact')['form']['budget_ranges'] ?? [];

        return [
            'name' => ['required', 'string', 'max:120'],
            'business_email' => ['required', 'email:rfc', 'max:255'],
            'company' => ['nullable', 'string', 'max:160'],
            'phone' => ['nullable', 'string', 'max:40'],
            'budget_range' => ['nullable', Rule::in($budgetRanges)],
            'service_interest' => ['nullable', 'string', 'max:120'],
            'message' => ['required', 'string', 'min:10', 'max:4000'],
            'consent_data_processing' => ['accepted'],
            'consent_marketing' => ['nullable', 'boolean'],
            'source_page' => ['nullable', 'string', 'max:160'],
            // Honeypot - real users never fill this.
            'website' => ['nullable', 'max:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $consentError = (string) (app(PageContentService::class)->for('contact')['form']['consent_error'] ?? '');

        return [
            'consent_data_processing.accepted' => $consentError !== '' ? $consentError : 'Please accept the privacy terms so we can reply.',
            'website.max' => 'Submission rejected.',
        ];
    }
}
