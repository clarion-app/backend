<?php
namespace ClarionApp\Backend;

use Illuminate\Support\Facades\Log;
use ClarionApp\Backend\EnvEditor;
use ClarionApp\Backend\SupervisorManager;
use ClarionApp\MultiChain\Facades\MultiChain;

class BlockchainManager
{
    public function create($name)
    {
        Log::info('Creating blockchain: ' . $name);
        Log::info(shell_exec('/usr/local/bin/multichain-util -datadir=/var/www/.multichain create ' . $name));

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
                $rpcportData = trim(explode('=', $line)[1]);
                $rpcport = explode('#', $rpcportData)[0];
            }
        }

        $laravelEnv = new EnvEditor(base_path('.env'));
        $laravelEnv->set('MULTICHAIN_RPC_PORT', $rpcport);
        $laravelEnv->set('MULTICHAIN_RPC_USER', $rpcuser);
        $laravelEnv->set('MULTICHAIN_RPC_PASS', $rpcpassword);
        $laravelEnv->save();

        config('multichain.rpcport', $rpcport);
        config('multichain.rpcuser', $rpcuser);
        config('multichain.rpcpassword', $rpcpassword);

        // Create supervisor config to run multichaind clarion
        $supervisor = new SupervisorManager();
        $supervisor->createConfig($name."-multichain", "[program:$name-multichain]
command=/usr/local/bin/multichaind -datadir=/var/www/.multichain $name
autostart=true
autorestart=true
stderr_logfile=/var/www/multichain.error
stdout_logfile=/var/www/multichain.log
user=www-data
");

        $supervisor->reloadSupervisor();

        MultiChain::create("stream", "UserRegistry", false);
        MultiChain::subscribe("UserRegistry");
    }

    public function exists($name)
    {
        return file_exists("/var/www/.multichain/$name");
    }
}