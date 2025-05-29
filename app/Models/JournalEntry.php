<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    protected $fillable = [
        'entry_number',
        'entry_date',
        'entry_day',
        'company_name',
        'logo_path',
        'description',
        'debit_accounts',
        'credit_accounts',
        'attachment_path',
        'accountant_signature',
        'reviewer_signature',
        'owner_signature'
    ];

    protected $casts = [
        'debit_accounts' => 'array',
        'credit_accounts' => 'array',
    ];
}
