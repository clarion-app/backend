<?php
namespace ClarionApp\Backend;

use Illuminate\Support\Facades\Log;

class ApiManager
{
    /**
     * Get all package descriptions
     */
    public static function getPackageDescriptions(): array
    {
        $packages = ClarionPackageServiceProvider::getPackageDescriptions();

        foreach($packages as $name=>$package)
        {
            $operations = self::getOperations("/".$name);
            foreach($operations as $operation)
            {
                if(in_array($operation['summary'], $packages[$name]['operations'])) continue;
                $packages[$name]['operations'][] = $operation['summary'];
            }
        }
        return $packages;
    }

    /**
     * Get all operations from the API
     * Returns an array of objects:
     * { operationId: string, summary: string }
     * @return array
     */
    public static function getOperations($urlFilter = null): array
    {
        $url = config("app.url")."/docs/api.json";
        $api = json_decode(file_get_contents($url));

        $results = [];
        foreach($api->paths as $url=>$path)
        {
            if($urlFilter != null)
            {
                $urlFilter = str_replace("@", "", $urlFilter);
                
                if(!str_starts_with($url, $urlFilter))
                    continue;
            }

            foreach($path as $method=>$details)
            {
                if(isset($details->summary))
                {
                    if(strpos($details->summary, "resource") === false)
                        $result = ["operationId"=>$details->operationId, "summary"=>$details->summary];

                    if(isset($result)) $results[] = $result;
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
