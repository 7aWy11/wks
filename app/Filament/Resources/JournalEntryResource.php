<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JournalEntryResource\Pages;
use App\Models\JournalEntry;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables;
use Illuminate\Support\Str;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;

class JournalEntryResource extends Resource
{
    protected static ?string $model = JournalEntry::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('entry_number')->required(),
                DatePicker::make('entry_date')
                    ->required()
                    ->afterStateUpdated(fn ($state, callable $set) => $set('entry_day', now()->parse($state)->format('l'))),
                TextInput::make('entry_day')->disabled(),
                TextInput::make('company_name')->required(),
                FileUpload::make('logo_path')->image()->directory('logos'),
                Textarea::make('description'),
                Repeater::make('debit_accounts')
                    ->schema([
                        TextInput::make('account')->required()->label('Debit Account'),
                        TextInput::make('amount')->required()->numeric(),
                    ])
                    ->label('Debit Accounts')
                    ->minItems(1)
                    ->default([])
                    ->afterStateHydrated(function ($component, $state) {
                        if (is_string($state)) {
                            $decoded = json_decode($state, true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                $component->state($decoded);
                            } else {
                                $component->state([]);
                            }
                        }
                    }),
                Repeater::make('credit_accounts')
                    ->schema([
                        TextInput::make('account')->required()->label('Credit Account'),
                        TextInput::make('amount')->required()->numeric(),
                    ])
                    ->label('Credit Accounts')
                    ->minItems(1)
                    ->default([])
                    ->afterStateHydrated(function ($component, $state) {
                        if (is_string($state)) {
                            $decoded = json_decode($state, true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                $component->state($decoded);
                            } else {
                                $component->state([]);
                            }
                        }
                    }),
                FileUpload::make('attachment_path')->label('Attachment')->directory('attachments'),
                TextInput::make('accountant_signature')->label('Accountant'),
                TextInput::make('reviewer_signature')->label('Reviewer'),
                TextInput::make('owner_signature')->label('Owner'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('entry_number'),
                Tables\Columns\TextColumn::make('entry_date'),
                Tables\Columns\TextColumn::make('company_name'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJournalEntries::route('/'),
            'create' => Pages\CreateJournalEntry::route('/create'),
            'edit' => Pages\EditJournalEntry::route('/{record}/edit'),
        ];
    }
}
