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
        Schema::create('co_build_agents', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->autoIncrement()->primary();
            $table->string('agent_name')->comment('名称');
            $table->string('out_agent_id')->comment('外部agentId');
            $table->string("token_address");
            $table->string('status')->comment('状态');
            $table->longText('content')->nullable()->comment('扩展内容');
            $table->string('type')->comment('类型');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['agent_name'], 'idx_agent_name');
            $table->index(['token_address'], 'idx_token_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('co_build_agents');
    }
};
