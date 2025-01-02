<?php

namespace ClarionApp\Backend;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use ReflectionClass;

abstract class ClarionPackageServiceProvider extends ServiceProvider
{
    protected static $packageDescriptions = [];
    protected string $routePrefix;

    public function register(): void
    {
        $reflection = new ReflectionClass($this);

        $packageRoot = dirname($reflection->getFileName(), 2);
        $composerPath = $packageRoot . '/composer.json';

        if (!file_exists($composerPath)) return;

        $composerInfo = json_decode(file_get_contents($composerPath), true);
        $clarion = $composerInfo['extra']['clarion'] ?? false;
        if (!$clarion) return;
        $name = $clarion['app-name'];
        $description = $clarion['description'];
        $this->routePrefix = 'api/' . str_replace("@", "", $name);
        self::$packageDescriptions[$name] = ['description'=>$description, 'operations'=>[]];
    }

    public function boot(): void
    {
    }

    public static function getPackageDescriptions(): array
    {
        return self::$packageDescriptions;
    }
}
