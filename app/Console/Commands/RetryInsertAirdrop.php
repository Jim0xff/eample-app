<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Pump\Token\Service\TokenGraduateService;

class RetryInsertAirdrop extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:retry-insert-airdrop';

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
        /** @var TokenGraduateService $tokenGraduateService */
        $tokenGraduateService = resolve('token_graduate_service');

        $tokenGraduateService->retryInsertAirdrop();
    }
}
