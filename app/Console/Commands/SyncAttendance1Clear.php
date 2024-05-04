<?php

namespace App\Console\Commands;

use App\Models\FpAttendance;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Rats\Zkteco\Lib\ZKTeco;

class SyncAttendance1Clear extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:attendance1clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * @return int
     */
    public function handle()
    {
        $zk = new ZKTeco(env("IP_FINGERPRINT"));

        if($zk->connect()){
            DB::transaction(function () use($zk){
                foreach (array_chunk($zk->getAttendance(),1000) as $t)
                {
                    collect($t)->each(function (array $row) {
                        $fp = FpAttendance::firstOrCreate(
                            ['uid' => $row['uid'],'mesin'=>'mesin1'],
                            ['id' => $row['id'],'timestamp' => $row['timestamp'],'type' => $row['type'],'state' => $row['state']]
                        );
                    });

                }
                $zk->clearAttendance();
            });
        }
        return 0;
    }
}
