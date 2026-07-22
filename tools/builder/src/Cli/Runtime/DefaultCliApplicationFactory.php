<?php

declare(strict_types=1);

namespace Sif\Builder\Cli\Runtime;

use Sif\Builder\Cli\Application\CliApplication;
use Sif\Builder\Cli\Command\BuildCommand;
use Sif\Builder\Cli\Command\HelpCommand;
use Sif\Builder\Cli\Command\ListCommand;
use Sif\Builder\Cli\Command\ValidateCommand;
use Sif\Builder\Cli\Command\VersionCommand;
use Sif\Builder\Cli\Configuration\BuilderRequestFactory;
use Sif\Builder\Cli\Configuration\WorkingDirectoryPathResolver;
use Sif\Builder\Cli\Contract\CliApplicationFactoryInterface;
use Sif\Builder\Cli\Contract\CliApplicationInterface;
use Sif\Builder\Cli\Input\ArgumentParser;
use Sif\Builder\Cli\Registry\CommandRegistry;
use Sif\Builder\Cli\Reporting\BuilderCommandResultFactory;
use Sif\Builder\Engine\Artifact\AtomicArtifactWriter;
use Sif\Builder\Engine\Extension\AnalyzerRegistry;
use Sif\Builder\Engine\Extension\GeneratorRegistry;
use Sif\Builder\Generator\ReferenceReport\ReferenceReportGenerator;
use Sif\Builder\Generator\RepositoryIndex\RepositoryIndexGenerator;

final readonly class DefaultCliApplicationFactory implements CliApplicationFactoryInterface
{
    public function __construct(
        private string $workingDirectory,
        private string $applicationName = 'SIF Builder',
        private string $version = '2.0.0-alpha1',
    ) {
    }

    public function create(): CliApplicationInterface
    {
        $analyzers = new AnalyzerRegistry();
        $generators = new GeneratorRegistry();
        $generators->register(new RepositoryIndexGenerator());
        $generators->register(new ReferenceReportGenerator());
        $reporters = ['report.markdown', 'report.json'];

        $requestFactory = new BuilderRequestFactory(
            new WorkingDirectoryPathResolver($this->workingDirectory),
        );
        $engineFactory = new DefaultBuilderEngineFactory(
            analyzers: $analyzers,
            generators: $generators,
            artifactWriter: new AtomicArtifactWriter(),
        );
        $resultFactory = new BuilderCommandResultFactory();
        $versionProvider = new StaticVersionProvider($this->applicationName, $this->version);
        $catalog = new StaticComponentCatalog(
            [],
            [RepositoryIndexGenerator::IDENTIFIER, ReferenceReportGenerator::IDENTIFIER],
            $reporters,
        );

        $commands = new CommandRegistry();
        $commands->register(new BuildCommand($requestFactory, $engineFactory, $resultFactory));
        $commands->register(new ValidateCommand($requestFactory, $engineFactory, $resultFactory));
        $commands->register(new ListCommand($catalog));
        $commands->register(new VersionCommand($versionProvider));
        $commands->register(new HelpCommand($commands, $versionProvider));

        return new CliApplication(new ArgumentParser(), $commands);
    }
}
