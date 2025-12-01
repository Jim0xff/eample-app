<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Pump\Token\Service\TradingService;

class TokenContributeUserCnt extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:token-contribute-user-cnt';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info("start token-contribute-user-cnt");

        /** @var TradingService $tradingService */
        $tradingService = resolve('trading_service');
        $tradingService->getContributeUserCnt();
    }
}
