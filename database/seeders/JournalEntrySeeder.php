<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JournalEntry;

class JournalEntrySeeder extends Seeder
{
    public function run(): void
    {
        JournalEntry::create([
            'entry_number' => '10/5-2025',
            'entry_date' => '2025-05-29',
            'entry_day' => 'Thursday',
            'company_name' => 'WKS Accounting Ltd.',
            'logo_path' => null,
            'description' => 'سلفة للموظفين من الخزنة والبنك',
            'debit_accounts' => json_encode([
                ['account' => 'حـ/ أحمد', 'amount' => 20000],
                ['account' => 'حـ/ محمد', 'amount' => 30000],
                ['account' => 'حـ/ علي', 'amount' => 40000],
                ['account' => 'حـ/ محمود', 'amount' => 10000],
            ]),
            'credit_accounts' => json_encode([
                ['account' => 'النقدية بالخزينة', 'amount' => 50000],
                ['account' => 'النقدية بالبنك', 'amount' => 50000],
            ]),
            'attachment_path' => null,
            'accountant_signature' => 'Emad Mousa',
            'reviewer_signature' => 'Ali Mahmoud',
            'owner_signature' => 'Waleed Farghaly',
        ]);
    }
}
