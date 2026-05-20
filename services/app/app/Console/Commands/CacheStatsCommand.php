<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class CacheStatsCommand extends Command
{
    protected $signature = 'cache:stats';

    protected $description = 'Display Redis cache hit/miss ratio';

    public function handle(): int
    {
        $info = Redis::connection('cache')->info('stats');

        $hits = (int) ($info['keyspace_hits'] ?? 0);
        $misses = (int) ($info['keyspace_misses'] ?? 0);
        $total = $hits + $misses;
        $ratio = $total > 0 ? round(($hits / $total) * 100, 2) : 0.0;

        $this->table(
            ['Metric', 'Value'],
            [
                ['Cache hits', number_format($hits)],
                ['Cache misses', number_format($misses)],
                ['Hit ratio', "{$ratio}%"],
                ['Total requests', number_format($total)],
            ]
        );

        return self::SUCCESS;
    }
}
