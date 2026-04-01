<?php

namespace ClarionApp\Backend\Tests\Unit;

use PHPUnit\Framework\TestCase;
use ClarionApp\Backend\AppManager;

class AppManagerTest extends TestCase
{
    public function test_composer_install_rejects_invalid_package_name(): void
    {
        $manager = new AppManager();
        $this->expectException(\InvalidArgumentException::class);
        $manager->composerInstall('; rm -rf /');
    }

    public function test_composer_install_rejects_package_with_semicolon(): void
    {
        $manager = new AppManager();
        $this->expectException(\InvalidArgumentException::class);
        $manager->composerInstall('vendor/package; whoami');
    }

    public function test_composer_uninstall_rejects_invalid_package_name(): void
    {
        $manager = new AppManager();
        $this->expectException(\InvalidArgumentException::class);
        $manager->composerUninstall('invalid;package');
    }

    public function test_composer_install_rejects_empty_package(): void
    {
        $manager = new AppManager();
        $this->expectException(\InvalidArgumentException::class);
        $manager->composerInstall('');
    }
}
