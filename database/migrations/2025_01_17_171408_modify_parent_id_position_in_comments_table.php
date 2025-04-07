<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyParentIdPositionInCommentsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            // Move a coluna parent_id para depois da coluna user_id
            $table->foreignId('parent_id')->nullable()->after('user_id')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            // Reverter a posição da coluna (se necessário, isso não altera a funcionalidade)
            $table->foreignId('parent_id')->nullable()->change();
        });
    }
};
