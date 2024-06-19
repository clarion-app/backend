<?php
namespace ClarionApp\Backend;

use ClarionApp\Backend\SupervisorManager;

class LaravelQueueManager
{
    protected $supervisorManager;

    public function __construct(SupervisorManager $supervisorManager)
    {
        $this->supervisorManager = $supervisorManager;
    }

    public function createQueueConfig($queueName, $numProcs = 1)
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

        $this->supervisorManager->createConfig("laravel-worker-{$queueName}", $config);
    }

    public function removeQueueConfig($queueName)
    {
        $this->supervisorManager->removeConfig("laravel-worker-{$queueName}");
    }

    public function startQueue($queueName)
    {
        $this->supervisorManager->startProgram("laravel-worker-{$queueName}");
    }

    public function stopQueue($queueName)
    {
        $this->supervisorManager->stopProgram("laravel-worker-{$queueName}");
    }

    public function restartQueue($queueName)
    {
        $this->supervisorManager->restartProgram("laravel-worker-{$queueName}");
    }

    public function reloadSupervisor()
    {
        $this->supervisorManager->reloadSupervisor();
    }

    public function listQueueConfigs()
    {
        return $this->supervisorManager->getConfigs();
    }
}
