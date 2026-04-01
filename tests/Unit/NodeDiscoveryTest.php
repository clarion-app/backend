<?php

namespace ClarionApp\Backend\Tests\Unit;

use PHPUnit\Framework\TestCase;

class NodeDiscoveryTest extends TestCase
{
    public function test_node_discovery_does_not_use_file_get_contents_for_http(): void
    {
        $source = file_get_contents(__DIR__ . '/../../src/Jobs/NodeDiscovery.php');
        $this->assertDoesNotMatchRegularExpression(
            '/file_get_contents\s*\(/',
            $source,
            'NodeDiscovery should not use file_get_contents for HTTP calls'
        );
    }

    public function test_node_discovery_uses_guzzle(): void
    {
        // NodeDiscovery delegates to UPnPScanner which uses Guzzle
        $source = file_get_contents(__DIR__ . '/../../src/Jobs/NodeDiscovery.php');
        $this->assertStringContainsString('UPnPScanner', $source,
            'NodeDiscovery should delegate to UPnPScanner');

        $scannerSource = file_get_contents(__DIR__ . '/../../src/UPnPScanner.php');
        $this->assertStringContainsString('GuzzleHttp', $scannerSource,
            'UPnPScanner should use Guzzle for HTTP');
    }

    public function test_clarion_scan_does_not_use_file_get_contents_for_http(): void
    {
        $source = file_get_contents(__DIR__ . '/../../src/Commands/ClarionScan.php');
        $this->assertDoesNotMatchRegularExpression(
            '/file_get_contents\s*\(/',
            $source,
            'ClarionScan should not use file_get_contents for HTTP calls'
        );
    }

    public function test_clarion_scan_uses_guzzle(): void
    {
        $source = file_get_contents(__DIR__ . '/../../src/Commands/ClarionScan.php');
        $this->assertStringContainsString('GuzzleHttp', $source,
            'ClarionScan should use Guzzle for HTTP');
    }
}
