<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->decimal('montant_encaissement', 14, 2)->nullable()->after('budget_initial');
            $table->decimal('montant_decaissement', 14, 2)->nullable()->after('montant_encaissement');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'montant_encaissement',
                'montant_decaissement',
            ]);
        });
    }
};
