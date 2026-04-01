<?php

namespace ClarionApp\Backend\Tests\Unit;

use PHPUnit\Framework\TestCase;

class ErrorResponseTest extends TestCase
{
    public function test_json_error_response_trait_exists(): void
    {
        $this->assertTrue(
            trait_exists('ClarionApp\Backend\Traits\JsonErrorResponse'),
            'JsonErrorResponse trait should exist'
        );
    }

    public function test_trait_has_error_response_method(): void
    {
        $this->assertTrue(
            method_exists('ClarionApp\Backend\Traits\JsonErrorResponse', 'errorResponse'),
            'JsonErrorResponse trait should have errorResponse method'
        );
    }

    public function test_trait_has_validation_error_response_method(): void
    {
        $this->assertTrue(
            method_exists('ClarionApp\Backend\Traits\JsonErrorResponse', 'validationErrorResponse'),
            'JsonErrorResponse trait should have validationErrorResponse method'
        );
    }

    public function test_trait_has_not_implemented_response_method(): void
    {
        $this->assertTrue(
            method_exists('ClarionApp\Backend\Traits\JsonErrorResponse', 'notImplementedResponse'),
            'JsonErrorResponse trait should have notImplementedResponse method'
        );
    }

    public function test_controllers_use_json_error_response_trait(): void
    {
        $controllers = [
            'backend/src/Controllers/AppController.php',
            'backend/src/Controllers/ComposerController.php',
            'backend/src/Controllers/NetworkController.php',
            'backend/src/Controllers/SystemController.php',
        ];

        foreach ($controllers as $controller) {
            $source = file_get_contents(__DIR__ . '/../../' . str_replace('backend/', '', $controller));
            $this->assertStringContainsString('JsonErrorResponse', $source,
                "$controller should use JsonErrorResponse trait");
        }
    }
}
