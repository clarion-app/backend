<?php
namespace ClarionApp\Backend;

class SupervisorManager
{
    protected $configPath;

    public function __construct()
    {
        $this->configPath = storage_path('supervisor');
        if (!file_exists($this->configPath)) {
            mkdir($this->configPath, 0755, true);
        }
        if(!file_exists("{$this->configPath}/conf.d")) {
            mkdir("{$this->configPath}/conf.d", 0755, true);
        }
    }

    public function createSupervisorConfig()
    {
        $config = "[supervisord]
logfile={$this->configPath}/supervisord.log ; main log file
pidfile={$this->configPath}/supervisord.pid ; pid file location

[unix_http_server]
file={$this->configPath}/supervisor.sock ; path to the socket file

[supervisorctl]
serverurl=unix://{$this->configPath}/supervisor.sock ; use a unix:// URL for a unix socket

[include]
files = {$this->configPath}/conf.d/*.conf
";
        file_put_contents("{$this->configPath}/supervisord.conf", $config);
    }        

    public function createConfig($queueName, $numProcs = 1)
    {
        // get system username
        $username = exec('whoami');
        $artisanPath = base_path('artisan');
        $logsPath = storage_path('logs');
        $config = "[program:laravel-worker-{$queueName}]
process_name=%(program_name)s_%(process_num)02d
command=php {$artisanPath} queue:work --queue={$queueName} --sleep=3 --tries=3
autostart=true
autorestart=true
user={$username}
numprocs={$numProcs}
redirect_stderr=true
stdout_logfile={$logsPath}/laravel-worker-{$queueName}.log";
        file_put_contents("{$this->configPath}/conf.d/laravel-worker-{$queueName}.conf", $config);
    }

    public function removeConfig($queueName)
    {
        $configPath = "{$this->configPath}/conf.d/laravel-worker-{$queueName}.conf";
        if (file_exists($configPath)) {
            unlink($configPath);
        }
    }

    public function getConfigs()
    {
        return array_diff(scandir($this->configPath."/conf.d"), ['.', '..']);
    }

    public function reloadSupervisor()
    {
        Process::run("supervisorctl -c {$this->configPath}/supervisord.conf reread");
        Process::run("supervisorctl -c {$this->configPath}/supervisord.conf update");
    }

    public function startWorker($queueName)
    {
        Process::run("supervisorctl -c {$this->configPath}/supervisord.conf start laravel-worker-{$queueName}:*");
    }

    public function stopWorker($queueName)
    {
        Process::run("supervisorctl -c {$this->configPath}/supervisord.conf stop laravel-worker-{$queueName}:*");
    }

    public function restartWorker($queueName)
    {
        Process::run("supervisorctl -c {$this->configPath}/supervisord.conf restart laravel-worker-{$queueName}:*");
    }
}