<?php
namespace ClarionApp\Backend;

use Illuminate\Support\Facades\Log;
use ClarionApp\Backend\EnvEditor;
use ClarionApp\Backend\ConfigEditor;
use ClarionApp\Backend\SupervisorManager;

class BlockchainManager
{
    public function create($name)
    {
        Log::info('Creating blockchain: ' . $name);
        print shell_exec('/usr/local/bin/multichain-util create ' . $name);

        $confFile = "/var/www/.multichain/$name/multichain.conf";
        $paramsFile = "/var/www/.multichain/$name/params.dat";

        $conf = file_get_contents($confFile);
        $params = file_get_contents($paramsFile);

        $lines = explode("\n", $conf);
        foreach($lines as $line)
        {
            if(strpos($line, 'rpcuser') !== false)
            {
                $rpcuser = explode('=', $line)[1];
            }
            if(strpos($line, 'rpcpassword') !== false)
            {
                $rpcpassword = explode('=', $line)[1];
            }
        }

        $lines = explode("\n", $params);
        foreach($lines as $line)
        {
            if(strpos($line, 'default-rpc-port') !== false)
            {
                $rpcport = explode('=', $line)[1];
            }
        }

        $laravelEnv = new EnvEditor(base_path('.env'));
        $laravelEnv->set('MULTICHAIN_RPC_PORT', $rpcport);
        $laravelEnv->set('MULTICHAIN_RPC_USER', $rpcuser);
        $laravelEnv->set('MULTICHAIN_RPC_PASS', $rpcpassword);
        $laravelEnv->save();

        // Create supervisor config to run multichaind clarion
        $supervisor = new SupervisorManager();
        $supervisor->createConfig($name, "[program:$name]
command=/usr/local/bin/multichaind $name
autostart=true
autorestart=true
stderr_logfile=/var/www/multichain.error
stdout_logfile=/var/www/multichain.log
user=www-data
");

        $supervisor->reloadSupervisor();

        //ConfigEditor::update('eloquent-multichain-bridge.disabled', false);
    }
}