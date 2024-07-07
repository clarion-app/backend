<?php
namespace ClarionApp\Backend;

use Illuminate\Support\Facades\Log;

class Composer extends \Illuminate\Support\Composer
{
    public function run(array $command)
    {
        $command = array_merge($this->findComposer(), $command);

        $this->getProcess($command)->run(function ($type, $data) {
            Log::info($data);
        }, [
            'COMPOSER_HOME' => '$HOME/.config/composer'
        ]);
    }
}