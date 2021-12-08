<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ParseRzn extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse:rzn {mode=manual} {date=now}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'RosZdravNadzor parser';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $mode = $this->argument('mode');
        $date = new \DateTime($this->argument('date'));
        if (!$date) {
            throw new \Exception('Укажите дату');
        }
        switch ($mode) {
            case 'manual':
                while (true) {
                    if ($date->format('Y') < \App\Jobs\ParseRzn::YEAR_MIN) {
                        break;
                    }
                    foreach (\App\Jobs\ParseRzn::REGIONS as $regionId => $regionName) {
                        \App\Jobs\ParseRzn::dispatchNow($regionId, $date);
                    }
                    $date->modify('-1 day');
                }
                break;
            case 'auto':
                foreach (\App\Jobs\ParseRzn::REGIONS as $regionId => $regionName) {
                    \App\Jobs\ParseRzn::dispatch($regionId, $date);
                }
                break;
            default:
                throw new \Exception('Укажите режим (auto/manual)');
        }
        return 0;
    }
}
