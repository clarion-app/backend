<?php

namespace ClarionApp\Backend\Commands;

use Illuminate\Console\Command;
use Artisan;
use Symfony\Component\Process\Process;

class SetupMariaDB extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clarion:setup-db';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sets up database and configures .env';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $socket = "/var/run/mysqld/mysqld.sock";

        $db_password = uniqid();

        $createDb = new Process(['mysql', '-u', 'root', '-e', 'CREATE DATABASE clarion']);
        $createDb->setTimeout(60);
        $createDb->run();
        if (!$createDb->isSuccessful()) {
            $this->error('Failed to create database: ' . $createDb->getErrorOutput());
            return;
        }

        $password_query = "GRANT ALL ON clarion.* TO 'clarion'@'localhost' IDENTIFIED BY '$db_password'";
        $grantPrivs = new Process(['mysql', '-u', 'root', '-e', $password_query]);
        $grantPrivs->setTimeout(60);
        $grantPrivs->run();
        if (!$grantPrivs->isSuccessful()) {
            $this->error('Failed to grant privileges: ' . $grantPrivs->getErrorOutput());
            return;
        }

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

        config(["database.connections.mysql.unix_socket"=>$socket]);
        array_push($lines, "DB_SOCKET=$socket");

        config(["database.connections.mysql.database"=>"clarion"]);
        array_push($lines, "DB_DATABASE=clarion");

        config(["database.connections.mysql.username"=>"clarion"]);
        array_push($lines, "DB_USERNAME=clarion");

        config(["database.connections.mysql.password"=>$db_password]);
        array_push($lines, "DB_PASSWORD=$db_password");

        $env = implode("\n", $lines);
        file_put_contents(base_path(".env"), $env);

        sleep(3);
        Artisan::call('migrate');
    }
}
