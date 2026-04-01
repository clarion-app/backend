<?php
namespace ClarionApp\Backend;

use Illuminate\Support\Facades\Log;
use ClarionApp\Backend\EnvEditor;
use ClarionApp\Backend\SupervisorManager;
use Symfony\Component\Process\Process;

class BlockchainManager
{
    private function validateBlockchainName(string $name): void
    {
        if (!preg_match('/^[a-zA-Z0-9][a-zA-Z0-9_-]{0,31}$/', $name)) {
            throw new \InvalidArgumentException("Invalid blockchain name: $name");
        }
    }

    public function create($name)
    {
        $this->validateBlockchainName($name);

        $binaryPath = config('clarion.multichain_binary_path', '/usr/local/bin');
        $datadir = config('clarion.multichain_datadir', '/var/www/.multichain');

        Log::info('Creating blockchain: ' . $name);
        $process = new Process([$binaryPath . '/multichain-util', '-datadir=' . $datadir, 'create', $name]);
        $process->setTimeout(60);
        $process->run();
        Log::info($process->getOutput());

        $this->config($name);
    }

    public function config($name)
    {
        $this->validateBlockchainName($name);

        $datadir = config('clarion.multichain_datadir', '/var/www/.multichain');
        $binaryPath = config('clarion.multichain_binary_path', '/usr/local/bin');
        $confFile = "$datadir/$name/multichain.conf";
        $paramsFile = "$datadir/$name/params.dat";

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
        $supervisor->createConfig($name."-multichain", [
            'command' => "$binaryPath/multichaind -datadir=$datadir $name",
            'autostart' => true,
            'autorestart' => true,
            'stderr_logfile' => '/var/www/multichain.error',
            'stdout_logfile' => '/var/www/multichain.log',
            'user' => 'www-data',
        ]);

        $supervisor->reloadSupervisor();
    }

    public function exists($name)
    {
        $this->validateBlockchainName($name);
        $datadir = config('clarion.multichain_datadir', '/var/www/.multichain');
        return file_exists("$datadir/$name");
    }

    public function requestJoin($url)
    {
        if (!preg_match('#^https?://#i', $url)) {
            throw new \InvalidArgumentException("Invalid URL scheme: $url");
        }

        $binaryPath = config('clarion.multichain_binary_path', '/usr/local/bin');
        $datadir = config('clarion.multichain_datadir', '/var/www/.multichain');

        Log::info('Joining blockchain: ' . $url);
        $process = new Process([$binaryPath . '/multichaind', '-datadir=' . $datadir, $url]);
        $process->setTimeout(60);
        $process->run();
        $results = $process->getOutput();
        Log::info($results);
        $lines = explode("\n", $results);
        $parts = explode(' ', $lines[4]);
        $wallet_address = $parts[3];
        Log::info("Joining with wallet address: $wallet_address");
        return $wallet_address;
    }
}