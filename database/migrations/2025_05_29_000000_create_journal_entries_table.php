<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJournalEntriesTable extends Migration
{
    public function up(): void
    {
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->string('entry_number');
            $table->date('entry_date');
            $table->string('entry_day');
            $table->string('company_name');
            $table->string('logo_path')->nullable();
            $table->text('description')->nullable();
            $table->json('debit_accounts');
            $table->json('credit_accounts');
            $table->string('attachment_path')->nullable();
            $table->string('accountant_signature')->nullable();
            $table->string('reviewer_signature')->nullable();
            $table->string('owner_signature')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
}
