<?php

namespace ClarionApp\Backend\Tests\Unit;

use PHPUnit\Framework\TestCase;
use ClarionApp\Backend\SupervisorManager;

class SupervisorManagerTest extends TestCase
{
    public function test_create_config_rejects_name_with_semicolon(): void
    {
        $manager = $this->createPartialMock(SupervisorManager::class, []);
        $this->expectException(\InvalidArgumentException::class);
        $manager->createConfig('test;whoami', [
            'command' => '/usr/bin/test',
            'autostart' => true,
        ]);
    }

    public function test_create_config_rejects_name_with_pipe(): void
    {
        $manager = $this->createPartialMock(SupervisorManager::class, []);
        $this->expectException(\InvalidArgumentException::class);
        $manager->createConfig('test|whoami', [
            'command' => '/usr/bin/test',
        ]);
    }

    public function test_start_program_rejects_invalid_name(): void
    {
        $manager = $this->createPartialMock(SupervisorManager::class, []);
        $this->expectException(\InvalidArgumentException::class);
        $manager->startProgram('; rm -rf /');
    }

    public function test_stop_program_rejects_invalid_name(): void
    {
        $manager = $this->createPartialMock(SupervisorManager::class, []);
        $this->expectException(\InvalidArgumentException::class);
        $manager->stopProgram('test`whoami`');
    }

    public function test_restart_program_rejects_invalid_name(): void
    {
        $manager = $this->createPartialMock(SupervisorManager::class, []);
        $this->expectException(\InvalidArgumentException::class);
        $manager->restartProgram('test$(whoami)');
    }

    public function test_remove_config_rejects_invalid_name(): void
    {
        $manager = $this->createPartialMock(SupervisorManager::class, []);
        $this->expectException(\InvalidArgumentException::class);
        $manager->removeConfig('../../../etc/passwd');
    }

    public function test_create_config_builds_structured_output(): void
    {
        // Use a temp directory to avoid modifying system files
        $tmpDir = sys_get_temp_dir() . '/supervisor_test_' . uniqid();
        mkdir($tmpDir . '/conf.d', 0755, true);

        $manager = new SupervisorManager($tmpDir);
        $manager->createConfig('test-program', [
            'command' => '/usr/bin/test --flag',
            'autostart' => true,
            'autorestart' => true,
            'user' => 'www-data',
        ]);

        $configFile = $tmpDir . '/conf.d/test-program.conf';
        $this->assertFileExists($configFile);

        $content = file_get_contents($configFile);
        $this->assertStringContainsString('[program:test-program]', $content);
        $this->assertStringContainsString('command=/usr/bin/test --flag', $content);
        $this->assertStringContainsString('autostart=true', $content);
        $this->assertStringContainsString('user=www-data', $content);

        // Cleanup
        unlink($configFile);
        rmdir($tmpDir . '/conf.d');
        rmdir($tmpDir);
    }
}
