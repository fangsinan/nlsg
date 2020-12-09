<?php


namespace App\Jobs;


use App\Models\ConfigModel;
use App\Servers\ChannelServers;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class JobOfCytx implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $job_data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $data)
    {
        $this->job_data = $data;
    }

    public function handle()
    {
        ConfigModel::whereId(39)->increments('value');
//        $servers = new ChannelServers();
//        $servers->cytxOrderList($this->job_data['id']);
    }

}
