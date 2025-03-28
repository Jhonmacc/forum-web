<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mentions', function (Blueprint $table) {
            $table->foreign('comment_id')->references('id')->on('comments')->onDelete('cascade');
            $table->foreign('reply_id')->references('id')->on('replies')->onDelete('cascade');
            $table->foreign('mentioned_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('mentions', function (Blueprint $table) {
            $table->dropForeign(['comment_id']);
            $table->dropForeign(['reply_id']);
            $table->dropForeign(['mentioned_user_id']);
        });
    }
};
