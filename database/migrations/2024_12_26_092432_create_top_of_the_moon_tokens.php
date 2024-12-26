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
        Schema::create('top_of_the_moon_token', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->autoIncrement()->primary();
            $table->string('address')->comment('代币地址');
            $table->string('status')->comment('状态');
            $table->longText('content')->nullable()->comment('扩展内容');
            $table->string('type')->comment('类型，MANUAL、SYSTEM');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['address'], 'idx_address');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('top_of_the_moon_tokens');
    }
};
