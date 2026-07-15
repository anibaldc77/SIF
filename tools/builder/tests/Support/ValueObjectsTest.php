<?php
declare(strict_types=1);
namespace Sif\Support\Tests;
use PHPUnit\Framework\TestCase;
use Sif\Support\Exceptions\InvalidPathException;
use Sif\Support\Exceptions\InvalidVersionException;
use Sif\Support\ValueObjects\Environment;
use Sif\Support\ValueObjects\JsonDocument;
use Sif\Support\ValueObjects\Path;
use Sif\Support\ValueObjects\Uuid;
use Sif\Support\ValueObjects\Version;
final class ValueObjectsTest extends TestCase
{
    public function testVersionParsesAndComparesSemanticVersions(): void { $version = Version::fromString('v2.0.0-alpha.1+build.4'); self::assertSame('2.0.0-alpha.1+build.4', $version->toString()); self::assertLessThan(0, $version->compare(Version::fromString('2.0.0'))); }
    public function testInvalidVersionIsRejected(): void { $this->expectException(InvalidVersionException::class); Version::fromString('2.0'); }
    public function testUuidPathEnvironmentAndJsonAreValueObjects(): void { $uuid = Uuid::v4(); self::assertMatchesRegularExpression('/^[0-9a-f-]{36}$/', $uuid->toString()); self::assertSame('a/c', Path::fromString('a/b/../c')->toString()); self::assertSame('APP_ENV', (new Environment('APP_ENV'))->toString()); self::assertSame('{"name":"SIF"}', JsonDocument::fromJson('{"name":"SIF"}')->toString()); }
    public function testPathCannotEscapeRoot(): void { $this->expectException(InvalidPathException::class); Path::fromString('../secret'); }
}
