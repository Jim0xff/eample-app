<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Pump\Token\Service\TokenService;
use Pump\Token\Service\TopOfTheMoonService;

class generateTopOfTheMoon extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-top-of-the-moon';

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
        /** @var $topOfTheMoonService TopOfTheMoonService */
        $topOfTheMoonService = resolve('top_of_the_moon_service');
        $topOfTheMoonService->generateTopOfTheMoon();
    }
}
