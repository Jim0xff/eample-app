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
        Schema::table('token', function (Blueprint $table) {
            $table->timestamp('sell_at')->nullable()->comment("发售时间");
            $table->string("ai_agent_type")->nullable()->comment("agent类型");
            $table->string("co_build_agent_id")->nullable()->comment("共建agentId");
            $table->unsignedInteger("features")->nullable()->comment("feature排序值");
            $table->unsignedInteger("trading_volume")->nullable()->comment("24小时交易量");
            $table->unsignedInteger("progress")->nullable()->comment("进度");
            $table->unsignedInteger("airdrop_rate")->nullable()->comment("空投比例");
            $table->fullText(["name","symbol","desc"], "token_search_index");
            $table->index(['address'], 'idx_address');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('token', function (Blueprint $table) {
            $table->dropColumn('sell_at');
            $table->dropColumn('ai_agent_type');
            $table->dropColumn('co-build-agent-id');
            $table->dropColumn('features');
            $table->dropColumn('trading-volume');
            $table->dropColumn('progress');

            $table->dropFullText("token_search_index");
        });
    }
};
