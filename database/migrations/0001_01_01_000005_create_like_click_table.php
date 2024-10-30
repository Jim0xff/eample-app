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
        Schema::create('like_click', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->autoIncrement()->primary();
            $table->string('comment_id')->comment('评论id');
            $table->string('user')->comment('用户id');
            $table->string('type')->comment('类型');
            $table->string('status')->comment('状态');
            $table->index(['comment_id'], 'idx_comment_id');
            $table->string('liked_user')->comment('被赞的用户id');
            $table->longText('content')->nullable()->comment('扩展内容');
            $table->unique(['user','comment_id'], 'unique_u_c_id');
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
