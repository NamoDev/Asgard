<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class IPDataController extends Controller
{

    /**
     * Gets information by IP
     *
     * @param  Request      $request
     * @return array        $returnData (with all the IP information!)
     */
    public function getIpInfo(Request $request){

        $validatedData = $request->validate([
            "ip" => "required|ipv4"
        ]);

        // Get this IP's information:
        $apiResponse = $this->getRipeAPIsearch($request->input("ip"));

        // Do some processing to get stuff we need:
        $returnData = [];

        // Magic. DO NOT TOUCH.
        foreach($apiResponse->objects as $apiIntermediateObject){
            foreach($apiIntermediateObject as $apiObject){
                if(isset($apiObject->type)){
                    switch($apiObject->type){
                        case "inetnum":
                            foreach($apiObject->attributes->attribute as $qValue){
                                if($qValue->name == "netname"){
                                    $returnData["netname"] = $qValue->value;
                                }
                            }
                        break;
                        case "route":
                            foreach($apiObject->attributes->attribute as $qValue){
                                if($qValue->name == "descr"){
                                    $returnData["netdescr"] = $qValue->value;
                                }
                                if($qValue->name == "origin"){
                                    $returnData["asn"] = $qValue->value;
                                }
                            }
                        break;
                    }
                }
            }
        }

        // Lookup AS number in origin and get the data for that:
        $asSearch = $this->getRipeAPIsearch($returnData["asn"]);
        foreach($asSearch->objects->object as $asSearchObject){
            foreach($asSearchObject->attributes->attribute as $asAttribute){
                if($asAttribute->name == "as-name"){
                    $returnData["asname"] = $asAttribute->value;
                }
                if($asAttribute->name == "descr"){
                    $returnData["asdescr"] = $asAttribute->value;
                }
            }
        }

        $descrModified = false;

        if(!isset($returnData["asdescr"])){
            $returnData["asdescr"] = strstr($returnData["netdescr"], ".", true) ?: $returnData["netdescr"];
            $descrModified = true;
        }

        if($descrModified === false){
            // Too many numbers in there, probably an address with zip code. We won't use that!
            if(preg_match("/[0-9]{3,}/", $returnData["asdescr"]) !== false && $returnData["netdescr"] != "Proxy-registered route object"){
                $returnData["asdescr"] = $returnData["netdescr"];
            }
        }

        return $returnData;

    }

    /**
     * Grab something from RIPE NCC's public APIs
     *
     * @param  string       $query
     * @return object       API response object
     */
    public function getRipeAPIsearch(String $query){

        $apiQueryURL = "http://rest.db.ripe.net/search.json?query-string=" . $query . "&flags=no-filtering&flags=resource";
        $curl = curl_init($apiQueryURL);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
        curl_setopt($curl, CURLOPT_URL,$apiQueryURL);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $rawApiResponse = curl_exec($curl);
        $apiInfo = curl_getinfo($curl);
        curl_close($curl);

        // Parse and return that:
        return json_decode($rawApiResponse);

    }

}
