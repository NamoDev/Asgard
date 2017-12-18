<?php

namespace App\Http\Controllers;

use App\TestRequest;
use App\Jobs\ProcessTest;
use Illuminate\Http\Request;

class LookingGlassController extends Controller
{

    /**
     * Dispatch a new test request (I know, it should've been named better...)
     *
     * @param  Request      $request
     * @param  TestRequest  $testRequest
     * @return array        $returnData (with TraceID)
     */
    public function dispatchNewTraceroute(Request $request, TestRequest $testRequest){

        if($request->has("destination")){

            // TODO: Massage this a bit in preparation of IPv6 support. New preg_matches() will be required.
            if(preg_match("/^((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)(\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)){3})$/", $request->input("destination")) || preg_match("/^((([a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z0-9]|[A-Za-z0-9][A-Za-z0-9\-]*[A-Za-z0-9]))$/", $request->input("destination"))){

                // Valid IP/hostname, but let's escape that again anyway:
                $testDestination = escapeshellarg($request->input("destination"));

                // Valid test type?
                $testType = $request->input("type");
                $allowableTestTypes = ["ping", "traceroute", "mtr", "v4_suite"];

                if(in_array($testType, $allowableTestTypes)){

                    // Create new test-request
                    $testRequest->clientIP = \Request::ip();
                    $testRequest->type = $testType;
                    $testRequest->done = 0;
                    $testRequest->result = "";
                    $testRequest->destination = $testDestination;
                    $testRequest->key = (string) $request->input("_token");
                    $testRequest->save();

                    // Dispatch request
                    ProcessTest::dispatch($testRequest->id);

                    $returnData = [
                        "traceID" => (string) $testRequest->id
                    ];

                    return $returnData;
                }else{
                    abort(417, "Invalid Test Type");
                }

            }else{
                abort(417, "Invalid Destination");
            }
        }else{
            abort(400, "No Destination Specified");
        }

    }


    /**
     * Checks the test result
     *
     * @param  string       $traceID
     * @param  Request      $request
     * @param  TestRequest  $testRequest
     * @return array        $returnData (with test info)
     */
    public function checkTraceResult(String $traceID, Request $request, TestRequest $testRequest){
        if($testRequest->where("id", $traceID)->count() != 1){
            abort(404, "Trace Request ID not found");
        }else{
            $thisRequest = $testRequest->where("id", $traceID)->first();
            if($thisRequest->key == (string) $request->input("_token")){
                $returnData = [
                    "done" => $thisRequest->done,
                    "clientIP" => $thisRequest->clientIP,
                    "result" => $thisRequest->result
                ];
                return $returnData;
            }else{
                abort(403, "Invalid Client Key");
            }
        }
    }

}
