<?php
namespace ClarionApp\Backend;

use ClarionApp\Backend\SupervisorManager;
use Symfony\Component\Process\Process;

class LaravelQueueManager
{
    protected $supervisorManager;

    public function __construct(SupervisorManager $supervisorManager)
    {
        $this->supervisorManager = $supervisorManager;
    }

    private function validateQueueName(string $name): void
    {
        if (!preg_match('/^[a-zA-Z0-9][a-zA-Z0-9_-]{0,63}$/', $name)) {
            throw new \InvalidArgumentException("Invalid queue name: $name");
        }
    }

    public function createQueueConfig($queueName, $numProcs = 1)
    {
        $this->validateQueueName($queueName);

        $process = new Process(['whoami']);
        $process->setTimeout(60);
        $process->run();
        $username = trim($process->getOutput());

        $artisanPath = base_path('artisan');
        $logsPath = storage_path('logs');

        $this->supervisorManager->createConfig("laravel-worker-{$queueName}", [
            'process_name' => '%(program_name)s_%(process_num)02d',
            'command' => "php {$artisanPath} queue:work --queue={$queueName} --sleep=3 --tries=3",
            'autostart' => true,
            'autorestart' => true,
            'user' => $username,
            'numprocs' => $numProcs,
            'redirect_stderr' => true,
            'stdout_logfile' => "{$logsPath}/laravel-worker-{$queueName}.log",
        ]);
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
