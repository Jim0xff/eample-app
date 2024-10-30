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
        Schema::create('token', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->autoIncrement()->primary();
            $table->string('address')->unique()->comment('代币地址');
            $table->string('name')->comment('名称');
            $table->string('symbol')->comment('代号');
            $table->string('desc')->comment('描述');
            $table->string('img_url')->comment('图片地址');
            $table->string('status')->comment('状态');
            $table->longText('content')->nullable()->comment('扩展内容');
            $table->string('creator')->comment('创建人');
            $table->index(['name'], 'idx_name');
            $table->timestamps();
            $table->softDeletes();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('token');
    }
};
