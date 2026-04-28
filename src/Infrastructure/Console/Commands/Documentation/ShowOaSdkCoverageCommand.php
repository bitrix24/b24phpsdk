<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Infrastructure\Console\Commands\Documentation;

use Bitrix24\SDK\Attributes\Services\AttributesParser;
use Bitrix24\SDK\OpenApi\Domain\OaSchemaMethodReader;
use Bitrix24\SDK\OpenApi\Domain\OaSdkCoverageCalculator;
use Bitrix24\SDK\OpenApi\Domain\OaToSdkMethodNormalizationPolicy;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Throwable;

#[AsCommand(
    name: 'b24-dev:show-oa-sdk-coverage',
    description: 'show OpenAPI schema snapshot vs SDK v3 coverage statistics',
    hidden: false
)]
class ShowOaSdkCoverageCommand extends Command
{
    private const SCHEMA_FILE = 'schema-file';
    private const SHOW_UNCOVERED = 'show-uncovered';
    private const SHOW_SDK_ONLY = 'show-sdk-only';

    public function __construct(
        private readonly AttributesParser $attributesParser,
        private readonly OaSchemaMethodReader $oaSchemaMethodReader,
        private readonly OaSdkCoverageCalculator $oaSdkCoverageCalculator,
        private readonly OaToSdkMethodNormalizationPolicy $oaToSdkMethodNormalizationPolicy,
        private readonly Finder $finder,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setHelp('show OpenAPI snapshot coverage for SDK methods marked with ApiVersion::v3')
            ->addOption(
                self::SCHEMA_FILE,
                null,
                InputOption::VALUE_REQUIRED,
                'path to checked-in OpenAPI schema snapshot',
                'docs/open-api/openapi.json'
            )
            ->addOption(
                self::SHOW_UNCOVERED,
                null,
                InputOption::VALUE_NONE,
                'print normalized OA methods missing in SDK v3'
            )
            ->addOption(
                self::SHOW_SDK_ONLY,
                null,
                InputOption::VALUE_NONE,
                'print SDK v3 methods missing in the OA snapshot'
            );
    }

    private function loadAllServiceClasses(): void
    {
        $directory = 'src/Services';
        $this->finder->files()->in($directory)->name('*.php');
        foreach ($this->finder as $file) {
            if ($file->isDir()) {
                continue;
            }

            $absoluteFilePath = $file->getRealPath();
            require_once $absoluteFilePath;
        }
    }

    /**
     * @param non-empty-string $namespace
     * @return list<class-string>
     */
    private function getAllSdkClassNames(string $namespace): array
    {
        $allClasses = get_declared_classes();

        return array_values(array_filter($allClasses, static function ($class) use ($namespace) {
            return strncmp($class, $namespace, strlen($namespace)) === 0;
        }));
    }

    /**
     * @param array<string, array{
     *     totalOaMethods: int,
     *     coveredMethods: int,
     *     uncoveredMethods: int,
     *     coveragePercentage: float
     * }> $scopeBreakdown
     */
    private function renderScopeBreakdown(OutputInterface $output, array $scopeBreakdown): void
    {
        $rows = [];
        foreach ($scopeBreakdown as $scope => $scopeMetrics) {
            $rows[] = [
                $scope,
                $scopeMetrics['totalOaMethods'],
                $scopeMetrics['coveredMethods'],
                $scopeMetrics['uncoveredMethods'],
                $scopeMetrics['coveragePercentage'],
            ];
        }

        $table = new Table($output);
        $table
            ->setHeaders(['Scope', 'OA methods', 'Covered', 'Uncovered', 'Coverage %'])
            ->setRows($rows);
        $table->render();
    }

    /**
     * @param list<string> $uncoveredMethods
     */
    private function renderUncoveredMethodsTable(OutputInterface $output, array $uncoveredMethods): void
    {
        $rows = [];
        foreach ($uncoveredMethods as $uncoveredMethod) {
            $rows[] = [
                $uncoveredMethod,
                $this->oaToSdkMethodNormalizationPolicy->buildDocumentationUrl($uncoveredMethod),
            ];
        }

        $table = new Table($output);
        $table
            ->setHeaders(['Method', 'Documentation URL'])
            ->setRows($rows);
        $table->render();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $schemaFile = (string)$input->getOption(self::SCHEMA_FILE);
            if ($schemaFile === '') {
                throw new InvalidArgumentException('you must provide a schema file path in option «schema-file»');
            }

            $this->logger->debug('ShowOaSdkCoverageCommand.start', [
                'schemaFile' => $schemaFile,
                'showUncovered' => (bool)$input->getOption(self::SHOW_UNCOVERED),
                'showSdkOnly' => (bool)$input->getOption(self::SHOW_SDK_ONLY),
            ]);

            $this->loadAllServiceClasses();
            $sdkClassNames = $this->getAllSdkClassNames('Bitrix24\\SDK');
            $sdkBasePath = dirname(__FILE__, 6) . '/';

            $oaMethodNames = $this->oaSchemaMethodReader->readMethodNames($schemaFile);
            $supportedInSdkApiMethods = $this->attributesParser->getSupportedInSdkApiMethods($sdkClassNames, $sdkBasePath);
            $coverageResult = $this->oaSdkCoverageCalculator->calculate($oaMethodNames, $supportedInSdkApiMethods);

            $output->writeln([
                sprintf('OpenAPI methods count: %d', $coverageResult->totalOaMethods),
                sprintf('Covered SDK v3 methods count: %d', $coverageResult->totalCoveredMethods),
                sprintf('Uncovered OpenAPI methods count: %d', count($coverageResult->uncoveredMethods)),
                sprintf('SDK-only v3 methods count: %d', count($coverageResult->sdkOnlyMethods)),
                sprintf('Coverage percentage: %s%%', $coverageResult->coveragePercentage),
                '',
                'Per-scope breakdown:',
            ]);

            $this->renderScopeBreakdown($output, $coverageResult->scopeBreakdown);

            if ($coverageResult->scopeMismatchDiagnostics !== []) {
                $io->warning(array_merge(
                    ['Detected SDK scope diagnostics:'],
                    $coverageResult->scopeMismatchDiagnostics
                ));
            }

            if ((bool)$input->getOption(self::SHOW_UNCOVERED)) {
                $output->writeln(['', 'Uncovered OpenAPI methods:']);
                $this->renderUncoveredMethodsTable($output, $coverageResult->uncoveredMethods);
            }

            if ((bool)$input->getOption(self::SHOW_SDK_ONLY)) {
                $output->writeln(['', 'SDK-only v3 methods:']);
                $output->writeln($coverageResult->sdkOnlyMethods);
            }

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $io->error(sprintf('runtime error: %s', $exception->getMessage()));

            return self::INVALID;
        }
    }
}
