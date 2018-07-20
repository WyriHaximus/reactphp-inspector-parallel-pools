<?php declare(strict_types=1);

namespace WyriHaximus\React\Inspector\ChildProcessPools;

use Rx\ObservableInterface;
use WyriHaximus\React\ChildProcess\Pool\PoolInterface;
use WyriHaximus\React\Inspector\CollectorInterface;
use WyriHaximus\React\Inspector\Metric;
use function ApiClients\Tools\Rx\observableFromArray;

final class ChildProcessPoolsCollector implements CollectorInterface
{
    /**
     * @var PoolInterface[]
     */
    private $pools = [];

    public function register(string $key, PoolInterface $pool)
    {
        $this->pools[$key] = $pool;
    }

    public function collect(): ObservableInterface
    {
        $metrics = [];

        foreach ($this->pools as $key => $pool) {
            foreach ($pool->info() as $metric => $value) {
                $metrics[] = new Metric(
                    'childprocess.pool.' . $key . '.' . $metric,
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
