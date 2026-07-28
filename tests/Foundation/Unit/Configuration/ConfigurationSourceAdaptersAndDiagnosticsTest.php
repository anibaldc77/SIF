<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Configuration;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Configuration\ConfigurationValueType;
use Sif\Foundation\Configuration\Diagnostics\ConfigurationDiagnosticSeverity;
use Sif\Foundation\Configuration\Exceptions\InvalidEnvironmentConfigurationException;
use Sif\Foundation\Configuration\Source\EnvironmentConfigurationSource;
use Sif\Foundation\Configuration\Source\EnvironmentVariableDefinition;
use Sif\Foundation\Configuration\Source\FileConfigurationSource;

final class ConfigurationSourceAdaptersAndDiagnosticsTest extends TestCase
{
    /** @var list<string> */
    private array $temporaryFiles = [];

    protected function tearDown(): void
    {
        putenv('SIF_TEST_NAME');
        putenv('SIF_TEST_WORKERS');
        putenv('SIF_TEST_DEBUG');
        putenv('SIF_TEST_REQUIRED');

        foreach ($this->temporaryFiles as $file) {
            @unlink($file);
        }
    }

    public function testFileSourceLoadsSupportedFileAndReportsSafeDiagnostic(): void
    {
        $file = $this->temporaryFile('php', "<?php\nreturn ['app' => ['name' => 'SIF']];\n");
        $result = (new FileConfigurationSource('application', $file, 20))->load();

        self::assertSame(['app' => ['name' => 'SIF']], $result->values());
        self::assertSame('CFG_SOURCE_FILE_LOADED', $result->diagnostics()[0]->code());
        self::assertSame(ConfigurationDiagnosticSeverity::Info, $result->diagnostics()[0]->severity());
        self::assertArrayNotHasKey('value', $result->diagnostics()[0]->context());
    }

    public function testOptionalMissingFileProducesEmptyValuesAndWarning(): void
    {
        $result = (new FileConfigurationSource('optional', __DIR__ . '/missing.json', required: false))->load();

        self::assertSame([], $result->values());
        self::assertSame('CFG_SOURCE_OPTIONAL_FILE_MISSING', $result->diagnostics()[0]->code());
        self::assertSame(ConfigurationDiagnosticSeverity::Warning, $result->diagnostics()[0]->severity());
    }

    public function testEnvironmentSourceUsesExplicitDefinitionsAndTypedParsing(): void
    {
        putenv('SIF_TEST_NAME=SIF');
        putenv('SIF_TEST_WORKERS=8');
        putenv('SIF_TEST_DEBUG=true');

        $result = (new EnvironmentConfigurationSource('environment', [
            new EnvironmentVariableDefinition('SIF_TEST_NAME', 'app.name'),
            new EnvironmentVariableDefinition('SIF_TEST_WORKERS', 'runtime.workers', ConfigurationValueType::Integer),
            new EnvironmentVariableDefinition('SIF_TEST_DEBUG', 'app.debug', ConfigurationValueType::Boolean),
        ]))->load();

        self::assertSame([
            'app' => ['name' => 'SIF', 'debug' => true],
            'runtime' => ['workers' => 8],
        ], $result->values());
        self::assertSame('CFG_SOURCE_ENV_LOADED', $result->diagnostics()[0]->code());
    }

    public function testOptionalMissingEnvironmentVariableCanUseDefaultAndReportsWarning(): void
    {
        $result = (new EnvironmentConfigurationSource('environment', [
            new EnvironmentVariableDefinition(
                'SIF_TEST_NAME',
                'app.name',
                ConfigurationValueType::String,
                default: 'SIF',
            ),
        ]))->load();

        self::assertSame(['app' => ['name' => 'SIF']], $result->values());
        self::assertSame('CFG_SOURCE_ENV_OPTIONAL_MISSING', $result->diagnostics()[0]->code());
    }

    public function testRequiredMissingEnvironmentVariableFailsExplicitly(): void
    {
        $this->expectException(InvalidEnvironmentConfigurationException::class);

        (new EnvironmentConfigurationSource('environment', [
            new EnvironmentVariableDefinition(
                'SIF_TEST_REQUIRED',
                'required.value',
                required: true,
            ),
        ]))->load();
    }

    public function testInvalidTypedEnvironmentValueFailsWithoutCoercion(): void
    {
        putenv('SIF_TEST_WORKERS=many');
        $this->expectException(InvalidEnvironmentConfigurationException::class);

        (new EnvironmentConfigurationSource('environment', [
            new EnvironmentVariableDefinition(
                'SIF_TEST_WORKERS',
                'runtime.workers',
                ConfigurationValueType::Integer,
            ),
        ]))->load();
    }

    private function temporaryFile(string $extension, string $contents): string
    {
        $file = tempnam(sys_get_temp_dir(), 'sif-config-');
        self::assertNotFalse($file);
        $target = $file . '.' . $extension;
        self::assertTrue(rename($file, $target));
        self::assertNotFalse(file_put_contents($target, $contents));
        $this->temporaryFiles[] = $target;

        return $target;
    }
}
