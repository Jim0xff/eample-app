<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('comment', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->autoIncrement()->primary();
            $table->string('token')->comment('代币地址');
            $table->string('user')->comment('用户id');
            $table->string('reply_user')->nullable()->comment('回复用户id');
            $table->string('parent_comment_id')->nullable()->comment('所属评论id');
            $table->string('reply_comment_id')->nullable()->comment('回复评论id');
            $table->unsignedInteger('love_cnt')->default(0)->comment('点赞数');
            $table->longText('content')->comment('留言内容');
            $table->index(['parent_comment_id'], 'idx_parent_comment_id');
            $table->index(['reply_comment_id'], 'idx_reply_comment_id');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comment');
    }
};
