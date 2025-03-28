<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReplyIdToLikesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('likes', function (Blueprint $table) {
            $table->unsignedBigInteger('reply_id')->nullable()->after('comment_id');
            $table->foreign('reply_id')->references('id')->on('replies')->onDelete('cascade');
            $table->unsignedBigInteger('comment_id')->nullable()->change();
            $table->foreignId('post_id')->nullable()->after('user_id')->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('likes', function (Blueprint $table) {
            $table->dropForeign(['reply_id']);
            $table->dropColumn('reply_id');
            $table->unsignedBigInteger('comment_id')->nullable(false)->change();
            $table->dropForeign(['post_id']);
        });
    }
};
