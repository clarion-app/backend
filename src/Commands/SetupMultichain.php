<?php

namespace ClarionApp\ClarionSetup\Commands;

use Illuminate\Console\Command;
use MetaverseSystems\DockerPhpClient\Facades\DockerClient;
use MetaverseSystems\DockerPhpClient\Containers\HostConfig;

class SetupMultichain extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clarion:setup-multichain';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sets up Multichain docker container and configures .env';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $image_name = "metaversesystems/multichain:latest";

        $rpc_port = "12000";
        $network_port = "12001";
        $rpc_user = "multichainrpc";
        $rpc_password = uniqid();

        DockerClient::image_create($image_name);

        $container = DockerClient::container_new("clarion-chain");
        $container->Image = $image_name;
        $container->Env = array(
            "CHAIN_NAME=clarion-chain",
            "RPC_PASSWORD=$rpc_password"
        );
        $container->HostConfig = new HostConfig(array(
          "Mounts"=>array(
              array("Type"=>"bind", "Source"=>"/srv", "Target"=>"/srv")
          ),
          "PortBindings"=>array(
              "$rpc_port/tcp"=>array(
                array("HostPort"=>$rpc_port)
              )
          )
        ));
        $container->save();
        $container->start();

        $container = DockerClient::container("clarion-chain");
        $rpc_host = $container->NetworkSettings->Networks->bridge->IPAddress;

        $env = file_get_contents(base_path(".env"));
        $lines = explode("\n", $env);

        foreach($lines as $k=>$line)
        {
            try {
              list($key, $value) = explode("=", $line);
            } catch (\ErrorException $e) {
                continue;
            }

            if(stripos($key, "MULTICHAIN_") !== false) unset($lines[$k]);
        }

        array_push($lines, "MULTICHAIN_RPC_HOST=$rpc_host");
        array_push($lines, "MULTICHAIN_RPC_PORT=$rpc_port");
        array_push($lines, "MULTICHAIN_RPC_USER=$rpc_user");
        array_push($lines, "MULTICHAIN_RPC_PASS=$rpc_password");

        $env = implode("\n", $lines);
        file_put_contents(base_path(".env"), $env);
    }
}
