<?php

namespace App\Console\Commands;

use App\InternalServices\Coingecko\CoingeckoService;
use Illuminate\Console\Command;

class TokenPriceSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:token-price-schedule';

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
        /** @var CoingeckoService $coingeckoService */
        $coingeckoService = resolve(CoingeckoService::class);
        $coingeckoService->getTokenPrice('metis-token', 'usd', 3000);
    }
}
