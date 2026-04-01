<?php
namespace ClarionApp\Backend;

use Symfony\Component\Process\Process as SymfonyProcess;

class SupervisorManager
{
    protected $configPath;

    public function __construct($configPath = null)
    {
        $this->configPath = $configPath ?? '/etc/supervisor';
        $this->ensureDirectoryExists($this->configPath);
        $this->ensureDirectoryExists("{$this->configPath}/conf.d");
    }

    private function validateProgramName(string $name): void
    {
        if (!preg_match('/^[a-zA-Z0-9][a-zA-Z0-9_-]{0,63}$/', $name)) {
            throw new \InvalidArgumentException("Invalid program name: $name");
        }
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

    /**
     * @param string $programName
     * @param array $config Key-value pairs for supervisor program config
     */
    public function createConfig(string $programName, $config)
    {
        $this->validateProgramName($programName);

        if (is_array($config)) {
            $lines = ["[program:$programName]"];
            foreach ($config as $key => $value) {
                if (is_bool($value)) {
                    $value = $value ? 'true' : 'false';
                }
                $lines[] = "$key=$value";
            }
            $configString = implode("\n", $lines) . "\n";
        } else {
            $configString = $config;
        }

        file_put_contents("{$this->configPath}/conf.d/{$programName}.conf", $configString);
    }

    public function removeConfig($programName)
    {
        $this->validateProgramName($programName);
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
        $process = new SymfonyProcess(['sudo', '/usr/bin/supervisorctl', 'update']);
        $process->setTimeout(60);
        $process->run();
    }

    public function startProgram($programName)
    {
        $this->validateProgramName($programName);
        $process = new SymfonyProcess(['supervisorctl', '-c', "{$this->configPath}/supervisord.conf", 'start', "{$programName}:*"]);
        $process->setTimeout(60);
        $process->run();
    }

    public function stopProgram($programName)
    {
        $this->validateProgramName($programName);
        $process = new SymfonyProcess(['supervisorctl', '-c', "{$this->configPath}/supervisord.conf", 'stop', "{$programName}:*"]);
        $process->setTimeout(60);
        $process->run();
    }

    public function restartProgram($programName)
    {
        $this->validateProgramName($programName);
        $process = new SymfonyProcess(['supervisorctl', '-c', "{$this->configPath}/supervisord.conf", 'restart', "{$programName}:*"]);
        $process->setTimeout(60);
        $process->run();
    }

    public function startSupervisord()
    {
        $process = new SymfonyProcess(['supervisord', '-c', "{$this->configPath}/supervisord.conf"]);
        $process->setTimeout(60);
        $process->run();
    }

    public function stopSupervisord()
    {
        $process = new SymfonyProcess(['supervisorctl', '-c', "{$this->configPath}/supervisord.conf", 'shutdown']);
        $process->setTimeout(60);
        $process->run();
    }

    public function isSupervisordRunning()
    {
        $process = new SymfonyProcess(['supervisorctl', '-c', "{$this->configPath}/supervisord.conf", 'status']);
        $process->setTimeout(60);
        $process->run();
        return $process->isSuccessful();
    }
}
