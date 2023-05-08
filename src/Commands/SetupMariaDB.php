<?php

namespace ClarionApp\ClarionSetup\Commands;

use Illuminate\Console\Command;
use MetaverseSystems\DockerPhpClient\Facades\DockerClient;
use Artisan;

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
    protected $description = 'Sets up database docker container and configures .env';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $socket = "/var/run/mysqld/mysqld.sock";

        $db_password = uniqid();
        $root_password = uniqid();

        exec("/etc/init.d/mysql stop && /usr/sbin/mysqld --skip-grant-tables --skip-networking &");
        exec("mysql -u root -e 'FLUSH PRIVELEGES'");
        $password_query = "SET PASSWORD FOR root@\'localhost\' = PASSWORD(\'$root_password\');"
        $password_command = "mysql -u root -e '$password_query'";
        print $password_command."\n";
        exec($password_command);
        exec("kill %1 && /etc/init.d/mysql start");
        exec("mysql -u root -e 'CREATE DATABASE clarion'");
        $password_command = "GRANT ALL ON clarion.* TO \'clarion\'@\'localhost\' IDENTIFIED BY \'$db_password\';";
        print $password_command."\n";
        exec($password_command);

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

        sleep(5);
        Artisan::call('migrate');
    }
}
