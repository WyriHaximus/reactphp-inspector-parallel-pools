<?php declare(strict_types=1);

namespace WyriHaximus\React\Tests\Inspector\ParallelPools;

use Rx\Observable;
use Rx\React\Promise;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;
use WyriHaximus\React\Inspector\Metric;
use WyriHaximus\React\Inspector\ParallelPools\ParallelPoolsCollector;
use WyriHaximus\React\Parallel\PoolInterface;

/**
 * @internal
 */
final class ParallelPoolsCollectorTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function emptyPool(): void
    {
        $pool = $this->prophesize(PoolInterface::class);
        $pool->info()->shouldBeCalled()->willReturn([
            'busy' => 0.0,
            'calls' => 0.0,
            'idle' => 0.0,
            'size' => 0.0,
        ]);

        $collector = new ParallelPoolsCollector();
        $collector->register('test', $pool->reveal());

        /** @var Observable $observable */
        $observable = $collector->collect();
        $metrics = $this->await(Promise::fromObservable($observable->toArray()));
        self::assertCount(4, $metrics);

        foreach ($metrics as $metric) {
            self::assertInstanceOf(Metric::class, $metric);
            self::assertTrue(\in_array(
                $metric->getKey(),
                [
                    'parallel.pool.test.busy',
                    'parallel.pool.test.calls',
                    'parallel.pool.test.idle',
                    'parallel.pool.test.size',
                ],
                true
            ));
            self::assertSame(0.0, $metric->getValue());
        }
    }
}
