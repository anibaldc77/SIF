<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Command\Configuration;

use Sif\Foundation\Cli\Contracts\CliCommandInterface;
use Sif\Foundation\Cli\Value\CliCommandMetadata;
use Sif\Foundation\Cli\Value\CliCommandName;
use Sif\Foundation\Cli\Value\CliCommandResult;
use Sif\Foundation\Cli\Value\CliExitCode;
use Sif\Foundation\Cli\Value\CliInvocation;
use Sif\Foundation\Cli\Value\CliOperationalClass;
use Sif\Foundation\Configuration\Contracts\TypedConfigurationInterface;
use Sif\Foundation\Configuration\Schema\ConfigurationSchemaValidator;
use Sif\Foundation\Configuration\Schema\ConfigurationValidationIssue;
use Sif\Foundation\Configuration\Schema\Contracts\ConfigurationSchemaInterface;

final readonly class ConfigurationValidateCommand implements CliCommandInterface
{
    public function __construct(
        private TypedConfigurationInterface $configuration,
        private ConfigurationSchemaInterface $schema,
        private ConfigurationSchemaValidator $validator = new ConfigurationSchemaValidator(),
    ) {
    }

    public function metadata(): CliCommandMetadata
    {
        return new CliCommandMetadata(
            new CliCommandName('config:validate'),
            'Validates configuration against the governed schema.',
            null,
            [],
            [],
            CliOperationalClass::validation(),
            false,
            false,
        );
    }

    public function execute(CliInvocation $invocation): CliCommandResult
    {
        $result = $this->validator->validate($this->configuration, $this->schema);
        $issues = array_map(
            static fn (ConfigurationValidationIssue $issue): array => [
                'code' => $issue->code,
                'key' => $issue->key->value(),
                'message' => $issue->message,
            ],
            $result->issues(),
        );

        return new CliCommandResult(
            $result->isValid() ? CliExitCode::success() : CliExitCode::validationFailure(),
            $result->isValid() ? 'Configuration is valid.' : 'Configuration validation failed.',
            ['valid' => $result->isValid(), 'issue_count' => count($issues), 'issues' => $issues],
        );
    }
}
