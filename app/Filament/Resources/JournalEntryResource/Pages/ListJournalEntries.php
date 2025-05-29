<?php

namespace App\Filament\Resources\JournalEntryResource\Pages;

use App\Filament\Resources\JournalEntryResource;
use Filament\Resources\Pages\ListRecords;
use App\Models\JournalEntry;
use Illuminate\Support\Str;
use Filament\Notifications\Notification;

class ListJournalEntries extends ListRecords
{
    protected static string $resource = JournalEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Pages\Actions\Action::make('generate_monthly_summary')
                ->label('Generate Monthly Consolidated Entry')
                ->action(fn () => $this->generateMonthlySummary())
                ->color('success'),
        ];
    }

    protected function generateMonthlySummary(): void
    {
        $currentMonth = now()->format('Y-m');
        $entries = JournalEntry::where('entry_date', 'like', "$currentMonth%")->get();

        \Log::info('Total entries found: ' . $entries->count());

        $grouped = collect();

        foreach ($entries as $entry) {
            try {
                // Debug the data types
                \Log::info('Entry ID: ' . $entry->id);
                \Log::info('Debit accounts type: ' . gettype($entry->debit_accounts));
                \Log::info('Credit accounts type: ' . gettype($entry->credit_accounts));
                \Log::info('Debit accounts value: ' . json_encode($entry->debit_accounts));
                \Log::info('Credit accounts value: ' . json_encode($entry->credit_accounts));

                // Ensure we have arrays
                $debitAccounts = $this->ensureArray($entry->debit_accounts);
                $creditAccounts = $this->ensureArray($entry->credit_accounts);
                
                $key = md5(json_encode($debitAccounts) . json_encode($creditAccounts));
                
                if (!isset($grouped[$key])) {
                    $grouped[$key] = collect();
                }
                $grouped[$key]->push($entry);
                
            } catch (\Exception $e) {
                \Log::error('Error processing entry ' . $entry->id . ': ' . $e->getMessage());
                continue;
            }
        }

        \Log::info('Groups created: ' . $grouped->count());

        foreach ($grouped as $key => $group) {
            try {
                if ($group->count() < 2) {
                    \Log::info('Skipping group with less than 2 entries');
                    continue;
                }

                \Log::info('Processing group with ' . $group->count() . ' entries');

                $base = $group->first();
                $mergedDebit = collect();
                $mergedCredit = collect();

                foreach ($group as $e) {
                    \Log::info('Processing entry ID: ' . $e->id);
                    
                    // Double-check data types before foreach
                    $debitAccounts = $this->ensureArray($e->debit_accounts);
                    $creditAccounts = $this->ensureArray($e->credit_accounts);
                    
                    \Log::info('Debit accounts after ensure: ' . gettype($debitAccounts) . ' - ' . json_encode($debitAccounts));
                    \Log::info('Credit accounts after ensure: ' . gettype($creditAccounts) . ' - ' . json_encode($creditAccounts));

                    foreach ($debitAccounts as $debit) {
                        $mergedDebit->push($debit);
                    }
                    
                    foreach ($creditAccounts as $credit) {
                        $mergedCredit->push($credit);
                    }
                    
                    $e->delete();
                }

                JournalEntry::create([
                    'entry_number' => 'SUM-' . strtoupper(Str::random(5)),
                    'entry_date' => now()->toDateString(),
                    'entry_day' => now()->format('l'),
                    'company_name' => $base->company_name,
                    'logo_path' => $base->logo_path,
                    'description' => 'Monthly Consolidated Entry',
                    'debit_accounts' => $mergedDebit->toArray(),
                    'credit_accounts' => $mergedCredit->toArray(),
                    'attachment_path' => null,
                    'accountant_signature' => $base->accountant_signature,
                    'reviewer_signature' => $base->reviewer_signature,
                    'owner_signature' => $base->owner_signature,
                ]);

                \Log::info('Created consolidated entry successfully');

            } catch (\Exception $e) {
                \Log::error('Error processing group: ' . $e->getMessage());
                \Log::error('Stack trace: ' . $e->getTraceAsString());
                continue;
            }
        }

        Notification::make()
            ->title('Monthly Consolidated Entries Generated')
            ->success()
            ->send();
    }

    /**
     * Ensure the value is an array, decode JSON string if necessary
     */
    private function ensureArray($value): array
    {
        if (is_array($value)) {
            return $value;
        }
        
        if (is_string($value)) {
            // First try to decode as JSON
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
            
            // If it's a serialized array
            if (is_string($value) && strpos($value, 'a:') === 0) {
                $unserialized = @unserialize($value);
                if ($unserialized !== false && is_array($unserialized)) {
                    return $unserialized;
                }
            }
            
            // If it's a comma-separated string
            if (strpos($value, ',') !== false) {
                return array_map('trim', explode(',', $value));
            }
            
            // If it's a single value, wrap it in an array
            return [$value];
        }
        
        if (is_null($value)) {
            return [];
        }
        
        // For any other type, try to convert to array
        return (array) $value;
    }
}