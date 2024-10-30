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
        Schema::create('users', function (Blueprint $table) {
            $table->string('address')->primary()->comment('区块链地址');
            $table->string('nick_name')->comment('用户昵称');;
            $table->string('wallet_type')->nullable()->comment('钱包类型');
            $table->string('head_img_url')->nullable()->comment('头像链接');
            $table->longText('content')->nullable()->comment('扩展内容');
            $table->index(['nick_name'], 'idx_name');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
