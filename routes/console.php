<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

//\Illuminate\Support\Facades\Schedule::command("app:generate-top-of-the-moon")->dailyAt('24:00');


\Illuminate\Support\Facades\Schedule::command("app:scan-graduated-token")->everyTenMinutes();


\Illuminate\Support\Facades\Schedule::command("app:retry-insert-airdrop")->everyTenMinutes();

\Illuminate\Support\Facades\Schedule::command("app:get-all-tokens-trading-volume")->dailyAt("23:50");

\Illuminate\Support\Facades\Schedule::command("app:token-price-schedule")->everyFourMinutes();

\Illuminate\Support\Facades\Schedule::command("app:token-contribute-user-cnt")->everyTwoHours();



