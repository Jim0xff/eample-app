<?php

namespace App\Console\Commands;

use App\InternalServices\GraphService\Service;
use Illuminate\Console\Command;

class zhufengTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:zhufeng-test';

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
        /** @var Service $graphService */
       $graphService = resolve(Service::class);
       $params = [
           "query" => 'query MyQuery {
  playRecords(where: {roundId: "1"}) {
    id
    roundId
    status
    winAmount
    player {
      id
    }
    fundAmount
  }
}'
       ];
       $rt = $graphService->baseQuery($params);
       print_r(json_encode($rt));
    }
}
