<?php

namespace ClarionApp\ClarionSetup\Commands;

use Illuminate\Console\Command;
use Artisan;

class RebuildClarionRoutes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clarion:routes-rebuild {directory}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $directory = $this->argument('directory');

        $path = $directory."/package.json";
        $package = json_decode(file_get_contents($path));

        $import_header = array('import { Route, Routes } from "react-router-dom";');
        $route_body = array();
        foreach($package->dependencies as $k=>$v)
        {
            $path = $directory."/node_modules/".$k."/package.json";
            $depPackage = json_decode(file_get_contents($path));

            if(!isset($depPackage->customFields)) continue;
            if(!isset($depPackage->customFields->routes)) continue;

            $routes = $depPackage->customFields->routes;
            foreach($routes as $route)
            {
                $component = str_replace(array(" ", "<", "/>"), "", $route->element);
                array_push($import_header, 'import { '.$component.' } from "'.$k.'";');
                array_push($route_body, '    <Route path="'.$route->path.'" element={'.$route->element.'} />');
            }
        }

        $body = implode("\n", $import_header)."\n\n";
        $body.= "const ClarionRoutes = () => {\n";
        $body.= "  return <Routes>\n";
        $body.= implode("\n", $route_body)."\n";
        $body.= "  </Routes>\n";
        $body.= "};\n\n";
        $body.= "export default ClarionRoutes;\n";
        file_put_contents($directory."/src/ClarionRoutes.tsx", $body);
    }
}
