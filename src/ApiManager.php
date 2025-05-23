<?php
namespace ClarionApp\Backend;

use Illuminate\Support\Facades\Log;
use ClarionApp\Backend\Services\DocumentationService;

class ApiManager
{
    /**
     * Get all package descriptions
     */
    public static function getPackageDescriptions(): array
    {
        $packages = ClarionPackageServiceProvider::getPackageDescriptions();

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
        if($urlFilter != null)
        {
            $urlFilter = str_replace("@", "/", $urlFilter);
        }

        $docService = new DocumentationService();
        $api = $docService->getApiDocs();

        $results = [];
        foreach($api['paths'] as $url=>$path)
        {
            if($urlFilter != null)
            {
                if(!str_starts_with($url, $urlFilter))
                    continue;
            }

            foreach($path as $method=>$details)
            {
                if(isset($details['summary']))
                {
                    if(strpos($details['summary'], "resource") === false)
                    {
                        $result = ["operationId"=>$details['operationId'], "summary"=>$details['summary']];
                    }

                    if(isset($result))
                    {
                        foreach($results as $r)
                        {
                            if($r['operationId'] == $result['operationId'])
                                continue 2;
                        }
                        $results[] = $result;
                    }
                }
            }
        }
        return $results;
    }

    /** 
     * Get operation details
     **/   
    public static function getOperationDetails(string $operationId)
    {
        $docService = new DocumentationService();
        $api = $docService->getApiDocs();

        foreach($api['paths'] as $path=>$pathDetails)
        {
            foreach($pathDetails as $method=>$details)
            {
                if($details['operationId'] == $operationId)
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

    public static function getCustomPrompts($package): array
    {
        return ClarionPackageServiceProvider::getCustomPrompts($package);
    }
}
