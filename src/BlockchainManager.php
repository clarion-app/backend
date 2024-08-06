<?php
namespace ClarionApp\Backend;

use Illuminate\Support\Facades\Log;
use ClarionApp\Backend\EnvEditor;
use ClarionApp\Backend\SupervisorManager;

class BlockchainManager
{
    public function create($name)
    {
        Log::info('Creating blockchain: ' . $name);
        Log::info(shell_exec('/usr/local/bin/multichain-util -datadir=/var/www/.multichain create '.$name));
        $this->config($name);
    }

    public function config($name)
    {
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
            if(strpos($line, 'default-network-port') !== false)
            {
                $portData = trim(explode('=', $line)[1]);
                $port = explode('#', $portData)[0];
            }

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
        $laravelEnv->set('MULTICHAIN_NODE_PORT', $port);
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
    }

    public function exists($name)
    {
        return file_exists("/var/www/.multichain/$name");
    }

    public function join($url)
    {
        Log::info('Joining blockchain: ' . $url);
        $results = shell_exec('/usr/local/bin/multichaind -datadir=/var/www/.multichain '.$url);
        $lines = explode("\n", $results);
        print_r($lines);
    }
}