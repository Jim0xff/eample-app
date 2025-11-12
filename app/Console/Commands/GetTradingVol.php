<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Pump\Token\Service\TokenGraduateService;
use Pump\Token\Service\TradingService;

class GetTradingVol extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:get-all-tokens-trading-volume';

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
        Log::info("start get-all-tokens-trading-volume");

        /** @var TradingService $tradingService */
        $tradingService = resolve('trading_service');

        $tradingService->getAllTokenTradingVol();
    }
}
