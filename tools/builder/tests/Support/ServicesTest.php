<?php
declare(strict_types=1);
namespace Sif\Support\Tests;
use PHPUnit\Framework\TestCase;
use Sif\Support\Time\Stopwatch;
use Sif\Support\Utilities\Arr;
use Sif\Support\Utilities\Reflection;
use Sif\Support\Utilities\Str;
final class ServicesTest extends TestCase
{
    public function testStringAndArrayServices(): void { $str = new Str(); self::assertSame('hello_world', $str->snake('helloWorld')); self::assertSame('helloWorld', $str->camel('hello_world')); self::assertSame('hel…', $str->truncate('hello', 4)); $arr = new Arr(); self::assertTrue($arr->has(['a' => ['b' => 1]], 'a.b')); self::assertSame(1, $arr->require(['a' => ['b' => 1]], 'a.b')); }
    public function testStopwatchAndReflectionServices(): void { $stopwatch = new Stopwatch(); $stopwatch->start(); $timer = $stopwatch->stop(); self::assertGreaterThanOrEqual(0, $timer->elapsedNanoseconds()); $reflection = new Reflection(); self::assertTrue($reflection->hasMethod(Str::class, 'snake')); self::assertInstanceOf(Str::class, $reflection->instantiate(Str::class)); }
}
