<?php
namespace ClarionApp\Backend;

use Illuminate\Support\Facades\Process;

class SupervisorManager
{
    protected $configPath;

    public function __construct()
    {
        $this->configPath = storage_path('supervisor');
        $this->ensureDirectoryExists($this->configPath);
        $this->ensureDirectoryExists("{$this->configPath}/conf.d");
    }

    protected function ensureDirectoryExists($path)
    {
        if (!file_exists($path)) {
            mkdir($path, 0755, true);
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

    public function createConfig($programName, $config)
    {
        file_put_contents("{$this->configPath}/conf.d/{$programName}.conf", $config);
    }

    public function removeConfig($programName)
    {
        $configPath = "{$this->configPath}/conf.d/{$programName}.conf";
        if (file_exists($configPath)) {
            unlink($configPath);
        }
    }

    public function getConfigs()
    {
        return array_diff(scandir($this->configPath . "/conf.d"), ['.', '..']);
    }

    public function reloadSupervisor()
    {
        Process::run("supervisorctl -c {$this->configPath}/supervisord.conf reread");
        Process::run("supervisorctl -c {$this->configPath}/supervisord.conf update");
    }

    public function startProgram($programName)
    {
        Process::run("supervisorctl -c {$this->configPath}/supervisord.conf start {$programName}:*");
    }

    public function stopProgram($programName)
    {
        Process::run("supervisorctl -c {$this->configPath}/supervisord.conf stop {$programName}:*");
    }

    public function restartProgram($programName)
    {
        Process::run("supervisorctl -c {$this->configPath}/supervisord.conf restart {$programName}:*");
    }

    public function startSupervisord()
    {
        Process::run("supervisord -c {$this->configPath}/supervisord.conf");
    }

    public function stopSupervisord()
    {
        Process::run("supervisorctl -c {$this->configPath}/supervisord.conf shutdown");
    }

    public function isSupervisordRunning()
    {
        $result = Process::run("supervisorctl -c {$this->configPath}/supervisord.conf status");
        return $result->successful();
    }
}
