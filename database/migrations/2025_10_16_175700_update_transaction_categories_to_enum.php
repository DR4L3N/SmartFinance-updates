<?php

use App\Enums\TransactionCategory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Map old categories to new enum values
        $categories = TransactionCategory::getValues();

        // Update any categories that don't match the enum values to a default category
        DB::table('transactions')
            ->whereNotIn('category', $categories)
            ->update(['category' => TransactionCategory::BANKING->value]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need for down method as we don't want to revert the categories
    }
};
