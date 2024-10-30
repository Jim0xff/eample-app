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
        Schema::create('user_follow_record', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->autoIncrement()->primary();
            $table->string('follower')->comment('关注用户');
            $table->string('followed')->comment('被关注用户');
            $table->string('status')->comment('状态');
            $table->timestamp('follow_at')->comment('关注时间');
            $table->timestamp('cancel_follow_at')->nullable()->comment('取消关注时间');
            $table->index(['follower','follow_at'], 'idx_follower');
            $table->index(['followed'], 'idx_followed');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_follow_record');
    }
};
