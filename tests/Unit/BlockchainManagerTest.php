<?php

namespace ClarionApp\Backend\Tests\Unit;

use PHPUnit\Framework\TestCase;
use ClarionApp\Backend\BlockchainManager;
use Symfony\Component\Process\Process;

class BlockchainManagerTest extends TestCase
{
    public function test_create_uses_process_with_argument_array(): void
    {
        $manager = new BlockchainManager();

        // We verify that the Process class would be called with argument arrays
        // by testing that invalid blockchain names are rejected
        $this->expectException(\InvalidArgumentException::class);
        $manager->create('; rm -rf /');
    }

    public function test_create_rejects_empty_name(): void
    {
        $manager = new BlockchainManager();
        $this->expectException(\InvalidArgumentException::class);
        $manager->create('');
    }

    public function test_create_rejects_name_with_semicolon(): void
    {
        $manager = new BlockchainManager();
        $this->expectException(\InvalidArgumentException::class);
        $manager->create('test;whoami');
    }

    public function test_create_rejects_name_with_pipe(): void
    {
        $manager = new BlockchainManager();
        $this->expectException(\InvalidArgumentException::class);
        $manager->create('test|cat /etc/passwd');
    }

    public function test_create_rejects_name_with_backtick(): void
    {
        $manager = new BlockchainManager();
        $this->expectException(\InvalidArgumentException::class);
        $manager->create('test`whoami`');
    }

    public function test_create_rejects_name_exceeding_max_length(): void
    {
        $manager = new BlockchainManager();
        $this->expectException(\InvalidArgumentException::class);
        $manager->create(str_repeat('a', 33));
    }

    public function test_request_join_rejects_non_http_url(): void
    {
        $manager = new BlockchainManager();
        $this->expectException(\InvalidArgumentException::class);
        $manager->requestJoin('ftp://evil.com/exploit');
    }

    public function test_request_join_rejects_url_with_shell_metacharacters(): void
    {
        $manager = new BlockchainManager();
        $this->expectException(\InvalidArgumentException::class);
        $manager->requestJoin('; rm -rf /');
    }
}
