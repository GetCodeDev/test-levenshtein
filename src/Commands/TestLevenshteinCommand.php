<?php

namespace GetCodeDev\TestLevenshtein\Commands;

use Illuminate\Console\Command;

class TestLevenshteinCommand extends Command
{
    public $signature = 'test-levenshtein';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
