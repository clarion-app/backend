<?php

namespace ClarionApp\Backend\Tests\Unit;

use PHPUnit\Framework\TestCase;

class NetworkControllerTest extends TestCase
{
    public function test_complete_join_uses_quoted_name_parameter(): void
    {
        $source = file_get_contents(__DIR__ . '/../../src/Controllers/NetworkController.php');
        // Must use $request->input('name') not $request->input(name)
        $this->assertStringContainsString("input('name')", $source,
            'completeJoin should use quoted name parameter');
        $this->assertStringNotContainsString('input(name)', $source,
            'completeJoin should not use unquoted constant reference');
    }

    public function test_app_manager_has_event_dispatches_uncommented(): void
    {
        $source = file_get_contents(__DIR__ . '/../../src/AppManager.php');
        $this->assertMatchesRegularExpression(
            '/^\s*event\(new InstallNPMPackageEvent/m',
            $source,
            'AppManager should have uncommented InstallNPMPackageEvent dispatch'
        );
        $this->assertMatchesRegularExpression(
            '/^\s*event\(new UninstallNPMPackageEvent/m',
            $source,
            'AppManager should have uncommented UninstallNPMPackageEvent dispatch'
        );
    }
}
