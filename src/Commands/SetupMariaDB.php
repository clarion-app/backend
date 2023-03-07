<?php

namespace ClarionApp\ClarionSetup\Commands;

use Illuminate\Console\Command;
use MetaverseSystems\DockerPhpClient\Facades\DockerClient;
use MetaverseSystems\DockerPhpClient\Containers\HostConfig;

class SetupMariaDB extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clarion:setup-db {srv}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sets up database docker container and configures .env';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $sockdir = $this->argument("srv");

        DockerClient::image_create("mariadb:latest");

        $db_password = uniqid();
        $root_password = uniqid();

        $container = DockerClient::container_new("clarion-db");
        $container->Image = "mariadb:latest";
        $container->Env = array(
            "MARIADB_DATABASE=clarion",
            "MARIADB_USER=clarion",
            "MARIADB_PASSWORD=$db_password",
            "MARIADB_ROOT_PASSWORD=$root_password"
        );
        $container->HostConfig = new HostConfig(array(
          "Mounts"=>array(
              array("Type"=>"bind", "Source"=>$sockdir, "Target"=>"/var/run/mysqld")
          )
        ));
        $container->Cmd = array("mysqld");
        $container->save();

        $env = file_get_contents(base_path(".env"));
        $lines = explode("\n", $env);
        foreach($lines as $k=>$line)
        {
            try {
              list($key, $value) = explode("=", $line);
            } catch (\ErrorException $e) {
                continue;
            }

            if(stripos($key, "DB_") !== false) unset($lines[$k]);
        }

        array_push($lines, "DB_CONNECTION=mysql");
        array_push($lines, "DB_SOCKET=/var/run/mysqld/mysqld.sock");
        array_push($lines, "DB_DATABASE=clarion");
        array_push($lines, "DB_USERNAME=clarion");
        array_push($lines, "DB_PASSWORD=$db_password");

        $env = implode("\n", $lines);
        file_put_contents(base_path(".env"), $env);

        $container->start();
    }
}
