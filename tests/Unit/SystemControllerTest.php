<?php

namespace ClarionApp\Backend\Tests\Unit;

use PHPUnit\Framework\TestCase;

class SystemControllerTest extends TestCase
{
    public function test_join_has_validation(): void
    {
        $source = file_get_contents(__DIR__ . '/../../src/Controllers/SystemController.php');
        $this->assertStringContainsString('validate', $source,
            'SystemController::join should validate input');
    }

    public function test_network_controller_complete_join_has_validation(): void
    {
        $source = file_get_contents(__DIR__ . '/../../src/Controllers/NetworkController.php');
        $this->assertStringContainsString('validate', $source,
            'NetworkController::completeJoin should validate name field');
    }
}
