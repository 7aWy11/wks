<?php

namespace App\Filament\Resources\JournalEntryResource\Pages;

use App\Filament\Resources\JournalEntryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditJournalEntry extends EditRecord
{
    protected static string $resource = JournalEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Ensure debit_accounts and credit_accounts are arrays
        if (isset($data['debit_accounts']) && is_string($data['debit_accounts'])) {
            $data['debit_accounts'] = json_decode($data['debit_accounts'], true) ?? [];
        }
        
        if (isset($data['credit_accounts']) && is_string($data['credit_accounts'])) {
            $data['credit_accounts'] = json_decode($data['credit_accounts'], true) ?? [];
        }

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Ensure arrays are properly JSON encoded before saving
        if (isset($data['debit_accounts'])) {
            $data['debit_accounts'] = is_array($data['debit_accounts']) ? $data['debit_accounts'] : [];
        }
        
        if (isset($data['credit_accounts'])) {
            $data['credit_accounts'] = is_array($data['credit_accounts']) ? $data['credit_accounts'] : [];
        }

        $record->update($data);
        return $record;
    }
}
