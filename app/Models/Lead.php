<?php

namespace App\Models;

use Database\Factories\LeadFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    /** @use HasFactory<LeadFactory> */
    use HasFactory;

    public const STATUSES = ['new', 'contacted', 'qualified', 'won', 'lost'];

    protected $fillable = [
        'name',
        'business_email',
        'company',
        'phone',
        'budget_range',
        'service_interest',
        'message',
        'source_page',
        'consent_marketing',
        'consent_data_processing',
        'status',
        'admin_notes',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'consent_marketing' => 'boolean',
            'consent_data_processing' => 'boolean',
        ];
    }
}
