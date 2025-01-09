<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('tags', function (Blueprint $table) {
            $table->string('code')->after('id')->nullable();  // Adiciona a coluna 'code'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('tags', function (Blueprint $table) {
            $table->dropColumn('code');  // Remove a coluna 'code' em caso de rollback
        });
    }
};
