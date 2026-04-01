<?php

namespace ClarionApp\Backend\Tests\Unit;

use PHPUnit\Framework\TestCase;

class AppControllerTest extends TestCase
{
    public function test_source_does_not_use_file_get_contents_for_http(): void
    {
        $source = file_get_contents(__DIR__ . '/../../src/Controllers/AppController.php');
        $this->assertDoesNotMatchRegularExpression(
            '/file_get_contents\s*\(.*(?:http|url|store)/i',
            $source,
            'AppController should not use file_get_contents for HTTP calls'
        );
    }

    public function test_source_uses_guzzle_client(): void
    {
        $source = file_get_contents(__DIR__ . '/../../src/Controllers/AppController.php');
        $this->assertStringContainsString('GuzzleHttp', $source,
            'AppController should use Guzzle for HTTP');
    }
}
