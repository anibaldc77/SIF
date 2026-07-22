<?php

declare(strict_types=1);

namespace Sif\Builder\Cli\Runtime;

use Sif\Builder\Analyzer\MetadataCompleteness\MetadataCompletenessAnalyzer;
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
use Sif\Builder\Engine\Pipeline\Stage\RepositoryDiscoveryStage;
use Sif\Builder\Engine\Pipeline\Stage\RepositoryIndexingStage;
use Sif\Builder\Generator\DocumentationNavigation\DocumentationNavigationGenerator;
use Sif\Builder\Generator\ReferenceGraph\ReferenceGraphGenerator;
use Sif\Builder\Generator\ReferenceReport\ReferenceReportGenerator;
use Sif\Builder\Generator\RepositoryManifest\RepositoryManifestGenerator;
use Sif\Builder\Generator\RepositoryIndex\RepositoryIndexGenerator;
use Sif\Builder\Metadata\CoreMetadataValidator;
use Sif\Builder\Metadata\MarkdownFrontMatterReader;
use Sif\Builder\Metadata\MarkdownRepositoryScanner;
use Sif\Builder\Reference\Parser\FrontMatterReferenceParser;
use Sif\Builder\Reference\Resolution\ReferenceResolver;
use Sif\Builder\Repository\RepositoryIndexBuilder;

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
        $analyzers->register(new MetadataCompletenessAnalyzer());
        $generators = new GeneratorRegistry();
        $generators->register(new RepositoryIndexGenerator());
        $generators->register(new ReferenceReportGenerator());
        $generators->register(new ReferenceGraphGenerator());
        $generators->register(new RepositoryManifestGenerator());
        $generators->register(new DocumentationNavigationGenerator());
        $reporters = ['report.markdown', 'report.json'];

        $requestFactory = new BuilderRequestFactory(new WorkingDirectoryPathResolver($this->workingDirectory));
        $engineFactory = new DefaultBuilderEngineFactory(
            analyzers: $analyzers,
            generators: $generators,
            discoveryStage: new RepositoryDiscoveryStage(
                new MarkdownRepositoryScanner(new MarkdownFrontMatterReader(), new CoreMetadataValidator()),
            ),
            indexingStage: new RepositoryIndexingStage(
                new RepositoryIndexBuilder(),
                new FrontMatterReferenceParser(),
                new ReferenceResolver(),
            ),
            artifactWriter: new AtomicArtifactWriter(),
        );
        $resultFactory = new BuilderCommandResultFactory();
        $versionProvider = new StaticVersionProvider($this->applicationName, $this->version);
        $catalog = new StaticComponentCatalog(
            [MetadataCompletenessAnalyzer::IDENTIFIER],
            [
                RepositoryIndexGenerator::IDENTIFIER,
                ReferenceReportGenerator::IDENTIFIER,
                ReferenceGraphGenerator::IDENTIFIER,
                RepositoryManifestGenerator::IDENTIFIER,
                DocumentationNavigationGenerator::IDENTIFIER,
            ],
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
