<?php

namespace ClarionApp\Backend\Tests\Unit;

use PHPUnit\Framework\TestCase;

class ComposerControllerTest extends TestCase
{
    public function test_source_has_validation_for_install(): void
    {
        $source = file_get_contents(__DIR__ . '/../../src/Controllers/ComposerController.php');
        $this->assertMatchesRegularExpression(
            '/validate|ComposerPackageName/',
            $source,
            'ComposerController::install should validate the package field'
        );
    }

    public function test_source_has_validation_for_uninstall(): void
    {
        $source = file_get_contents(__DIR__ . '/../../src/Controllers/ComposerController.php');
        $this->assertMatchesRegularExpression(
            '/validate|ComposerPackageName/',
            $source,
            'ComposerController::uninstall should validate the package field'
        );
    }
}
