<?php

namespace ClarionApp\Backend\Tests\Unit;

use PHPUnit\Framework\TestCase;
use ClarionApp\Backend\LaravelQueueManager;
use ClarionApp\Backend\SupervisorManager;

class LaravelQueueManagerTest extends TestCase
{
    public function test_create_queue_config_rejects_invalid_queue_name(): void
    {
        $supervisor = $this->createMock(SupervisorManager::class);
        $manager = new LaravelQueueManager($supervisor);
        $this->expectException(\InvalidArgumentException::class);
        $manager->createQueueConfig('; rm -rf /');
    }

    public function test_create_queue_config_rejects_queue_name_with_backtick(): void
    {
        $supervisor = $this->createMock(SupervisorManager::class);
        $manager = new LaravelQueueManager($supervisor);
        $this->expectException(\InvalidArgumentException::class);
        $manager->createQueueConfig('test`whoami`');
    }

    public function test_source_does_not_contain_exec(): void
    {
        $source = file_get_contents(__DIR__ . '/../../src/LaravelQueueManager.php');
        $this->assertDoesNotMatchRegularExpression('/\bexec\s*\(/', $source,
            'LaravelQueueManager should not use exec()');
    }

    public function test_source_uses_symfony_process(): void
    {
        $source = file_get_contents(__DIR__ . '/../../src/LaravelQueueManager.php');
        $this->assertStringContainsString('Symfony\Component\Process\Process', $source,
            'LaravelQueueManager should use Symfony Process');
    }
}
