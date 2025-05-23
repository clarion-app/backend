<?php

namespace ClarionApp\Backend\Services;

use Dedoc\Scramble\Generator;
use Dedoc\Scramble\Scramble;

class DocumentationService
{
    protected Generator $generator;

    public function __construct()
    {
        $this->generator = app(Generator::class);
    }

    public function getApiDocs(): array
    {
        $docs = ($this->generator)();

        return $docs;
    }
}
