<?php

namespace ClarionApp\Backend;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use ReflectionClass;
use ClarionApp\Backend\ApiManager;

abstract class ClarionPackageServiceProvider extends ServiceProvider
{
    protected string $packageName;
    protected string $routePrefix;

    protected static $packageDescriptions = [];
    protected static $packageOperations = [];
    protected static $customPrompts = [];

    public function register(): void
    {
        $reflection = new ReflectionClass($this);

        $packageRoot = dirname($reflection->getFileName(), 2);
        $composerPath = $packageRoot . '/composer.json';

        if (!file_exists($composerPath)) return;

        $composerInfo = json_decode(file_get_contents($composerPath), true);
        $clarion = $composerInfo['extra']['clarion'] ?? false;
        if (!$clarion) return;
        $this->packageName = $clarion['app-name'];
        $description = $clarion['description'];
        $this->routePrefix = 'api/' . str_replace("@", "", $this->packageName);
        self::$packageDescriptions[$this->packageName] = ['description'=>$description];
        self::$customPrompts[$this->packageName] = $clarion['customPrompts'] ?? [];
    }

    public function boot(): void
    {
    }

    public static function getPackageDescriptions(): array
    {
        return self::$packageDescriptions;
    }

    public static function getPackageOperations($package): array
    {
        if (!isset(self::$packageOperations[$package])) {
            self::$packageOperations[$package] = ApiManager::getOperations($package);
        }
        return self::$packageOperations[$package];
    }

    public static function getCustomPrompts($package): array
    {
        return self::$customPrompts[$package];
    }
}
