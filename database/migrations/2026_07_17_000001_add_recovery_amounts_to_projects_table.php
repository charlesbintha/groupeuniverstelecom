<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('projects', 'montant_recouvrement')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->decimal('montant_recouvrement', 14, 2)->default(0);
            });
        }

        if (!Schema::hasColumn('projects', 'montant_recouvre')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->decimal('montant_recouvre', 14, 2)->default(0);
            });
        }
    }

    public function down(): void
    {
        $columns = array_values(array_filter(
            ['montant_recouvrement', 'montant_recouvre'],
            fn (string $column) => Schema::hasColumn('projects', $column)
        ));

        if ($columns !== []) {
            Schema::table('projects', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
};
