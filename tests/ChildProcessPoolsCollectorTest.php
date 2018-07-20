<?php declare(strict_types=1);

namespace WyriHaximus\React\Tests\Inspector\ChildProcessPools;

use ApiClients\Tools\TestUtilities\TestCase;
use React\EventLoop\Factory;
use Rx\React\Promise;
use WyriHaximus\React\ChildProcess\Messenger\ReturnChild;
use WyriHaximus\React\ChildProcess\Pool\Factory\Flexible;
use WyriHaximus\React\Inspector\ChildProcessPools\ChildProcessPoolsCollector;
use WyriHaximus\React\Inspector\Metric;

final class ChildProcessPoolsCollectorTest extends TestCase
{
    /**
     * @test
     */
    public function emptyPool()
    {
        $loop = Factory::create();
        $pool = $this->await(Flexible::createFromClass(ReturnChild::class, $loop), Factory::create());
        $collector = new ChildProcessPoolsCollector();
        $collector->register('test', $pool);

        $metrics = $this->await(Promise::fromObservable($collector->collect()->toArray()));
        self::assertCount(4, $metrics);

        /** @var Metric $metric */
        foreach ($metrics as $metric) {
            self::assertInstanceOf(Metric::class, $metric);
            self::assertTrue(in_array(
                $metric->getKey(),
                [
                    'childprocess.pool.test.busy',
                    'childprocess.pool.test.calls',
                    'childprocess.pool.test.idle',
                    'childprocess.pool.test.size',
                ],
                true
            ));
            self::assertSame(0.0, $metric->getValue());
        }
    }
}
