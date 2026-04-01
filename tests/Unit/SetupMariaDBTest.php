<?php

namespace ClarionApp\Backend\Tests\Unit;

use PHPUnit\Framework\TestCase;

class SetupMariaDBTest extends TestCase
{
    public function test_handle_uses_process_not_exec(): void
    {
        // Verify that SetupMariaDB source code does not contain exec() calls
        $source = file_get_contents(__DIR__ . '/../../src/Commands/SetupMariaDB.php');
        $this->assertStringNotContainsString('exec(', $source, 'SetupMariaDB should not use exec()');
    }

    public function test_handle_uses_symfony_process(): void
    {
        $source = file_get_contents(__DIR__ . '/../../src/Commands/SetupMariaDB.php');
        $this->assertStringContainsString('Symfony\Component\Process\Process', $source,
            'SetupMariaDB should use Symfony Process');
    }
}
