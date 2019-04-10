<?php declare(strict_types=1);

namespace WyriHaximus\React\Inspector\ParallelPools;

use function ApiClients\Tools\Rx\observableFromArray;
use Rx\ObservableInterface;
use WyriHaximus\React\Inspector\CollectorInterface;
use WyriHaximus\React\Inspector\Metric;
use WyriHaximus\React\Parallel\PoolInterface;

final class ParallelPoolsCollector implements CollectorInterface
{
    /**
     * @var PoolInterface[]
     */
    private $pools = [];

    public function register(string $key, PoolInterface $pool): void
    {
        $this->pools[$key] = $pool;
    }

    public function collect(): ObservableInterface
    {
        $metrics = [];

        foreach ($this->pools as $key => $pool) {
            foreach ($pool->info() as $metric => $value) {
                $metrics[] = new Metric(
                    'parallel.pool.' . $key . '.' . $metric,
                    $value
                );
            }
        }

        return observableFromArray($metrics);
    }

    public function cancel(): void
    {
        $this->pools = [];
    }
}
