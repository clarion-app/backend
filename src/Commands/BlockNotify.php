<?php

namespace ClarionApp\Backend\Commands;

use Illuminate\Console\Command;
use MetaverseSystems\MultiChain\Facades\MultiChain;

class BlockNotify extends Command
{
    protected $signature = 'block:notify {block}';
    protected $description = 'Handle block notify event';

    public function handle()
    {
        $blockRaw = $this->argument('block');
        \Log::info($blockRaw);

        $block = json_decode($blockRaw);
        foreach($block->vout as $vout)
        { 
            if(!isset($vout->items)) continue;
            foreach($vout->items as $item)
            {
                $data = hex2bin($item->data);
                $keys = $item->keys;
                \Log::info($data);
            }
        }
    }
}
