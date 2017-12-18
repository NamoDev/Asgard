<?php

namespace App\Jobs;

use App\TestRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessTest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $recordID;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(String $recordID)
    {
        $this->recordID = $recordID;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(TestRequest $testRequest)
    {

        $theRequest = $testRequest->where("id", $this->recordID)->first();
        $testDestination = (string) $theRequest->destination;

        // TODO: Add multi-router/multi-location support

        // Which test to run?
        switch($theRequest->type){

            case "traceroute":
                // IPv4 traceroute
                $traceResult = shell_exec("traceroute $testDestination");
                $theRequest->result = $traceResult;
            break;

            case "ping":
                // IPv4 ping
                $pingResult = shell_exec("ping -c 4 $testDestination");
                $theRequest->result = $pingResult;
            break;

            case "mtr":
                // IPv4 MTR
                $mtrResult = shell_exec("mtr -r -c 2 $testDestination --report-wide");
                $theRequest->result = $mtrResult;
            break;

            case "v4_suite":
                // Complete IPv4 test suite
                $traceResult = shell_exec("traceroute $testDestination");
                $pingResult = shell_exec("ping -c 4 $testDestination");
                $mtrResult = shell_exec("mtr -r -c 2 $testDestination --report-wide");
                $theRequest->result = "Traceroute:\n" . $traceResult . "\n\nPing:\n" . $pingResult . "\n\nMTR:\n" . $mtrResult;
            break;

        }

        $theRequest->done = 1;
        $theRequest->save();

    }
}
