<?php
namespace ClarionApp\Backend;

use Illuminate\Support\Facades\Log;

class ApiManager
{
    /**
     * Get all operations from the API
     * Returns an array of objects:
     * { operationId: string, summary: string }
     * @return array
     */
    public static function getOperations(): array
    {
        $url = config("app.url")."/docs/api.json";
        $api = json_decode(file_get_contents($url));

        $results = [];
        foreach($api->paths as $path)
        {
            foreach($path as $method=>$details)
            {
                if(isset($details->summary))
                {
                    if(strpos($details->summary, "resource") === false)
                        $results[] = ["operationId"=>$details->operationId, "summary"=>$details->summary];
                }
            }
        }
        return $results;
    }

    /** 
     * Get operation details
     **/   
    public static function getOperationDetails(string $operationId): object
    {
        $url = config("app.url")."/docs/api.json";
        $api = json_decode(file_get_contents($url));

        foreach($api->paths as $path)
        {
            foreach($path as $method=>$details)
            {
                if($details->operationId == $operationId)
                {
                    return [
                        "path" => $path,
                        "method" => $method,
                        "details" => $details
                    ];

                }
            }
        }
        return (object)[];
    }
}