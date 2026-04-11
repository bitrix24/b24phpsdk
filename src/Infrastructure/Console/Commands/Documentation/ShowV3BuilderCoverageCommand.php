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

use Bitrix24\SDK\OpenApi\Domain\V3BuilderCoverageAuditor;
use Bitrix24\SDK\OpenApi\Domain\V3BuilderCoverageReport;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Throwable;

#[AsCommand(
    name: 'b24-dev:show-v3-builder-coverage',
    description: 'Audit SelectBuilder / ItemBuilder coverage for all OpenAPI v3 entities in a given scope',
    hidden: false
)]
class ShowV3BuilderCoverageCommand extends Command
{
    private const string ARGUMENT_SCOPE = 'scope';
    private const string OPTION_SCHEMA_FILE = 'schema-file';
    private const string OPTION_SHOW_UNMAPPED = 'show-unmapped';
    private const string OPTION_SHOW_MISSING_SELECT = 'show-missing-select';
    private const string OPTION_SHOW_MISSING_ITEM = 'show-missing-item';
    private const string OPTION_SHOW_INVALID = 'show-invalid';
    private const string OPTION_SHOW_SELECT_MISMATCHES = 'show-select-mismatches';
    private const string OPTION_SHOW_DUPLICATES = 'show-duplicates';
    private const string OPTION_FORMAT = 'format';

    public function __construct(
        private readonly V3BuilderCoverageAuditor $auditor,
        private readonly Finder $finder,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setHelp(
                'Scans src/Services/<scope>/ for #[OpenApiEntity] annotations, ' .
                'runs the audit against the OpenAPI snapshot, and reports builder coverage gaps.'
            )
            ->addArgument(
                self::ARGUMENT_SCOPE,
                InputArgument::REQUIRED,
                'Lowercase Bitrix24 scope name (e.g. "task" → src/Services/Task/)'
            )
            ->addOption(
                self::OPTION_SCHEMA_FILE,
                null,
                InputOption::VALUE_REQUIRED,
                'Path to the checked-in OpenAPI schema snapshot',
                'docs/open-api/openapi.json'
            )
            ->addOption(self::OPTION_SHOW_UNMAPPED, null, InputOption::VALUE_NONE, 'Print DTOs without an SDK mapping')
            ->addOption(self::OPTION_SHOW_MISSING_SELECT, null, InputOption::VALUE_NONE, 'Print entities missing a selectBuilder')
            ->addOption(self::OPTION_SHOW_MISSING_ITEM, null, InputOption::VALUE_NONE, 'Print entities missing an itemBuilder')
            ->addOption(self::OPTION_SHOW_INVALID, null, InputOption::VALUE_NONE, 'Print broken builder class references')
            ->addOption(self::OPTION_SHOW_SELECT_MISMATCHES, null, InputOption::VALUE_NONE, 'Print SelectBuilder field coverage mismatches')
            ->addOption(self::OPTION_SHOW_DUPLICATES, null, InputOption::VALUE_NONE, 'Print result classes sharing the same entityKey')
            ->addOption(
                self::OPTION_FORMAT,
                null,
                InputOption::VALUE_REQUIRED,
                'Output format: "table" or "json"',
                'table'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $format = (string) $input->getOption(self::OPTION_FORMAT);
            if (!in_array($format, ['table', 'json'], true)) {
                $io->error(sprintf('Invalid format "%s". Allowed values: table, json.', $format));
                return self::INVALID;
            }

            $scope = (string) $input->getArgument(self::ARGUMENT_SCOPE);
            $scanDirectory = sprintf('src/Services/%s', ucfirst($scope));

            if (!is_dir($scanDirectory)) {
                $io->error(sprintf('Scope directory "%s" does not exist.', $scanDirectory));
                return self::INVALID;
            }

            $schemaFile = (string) $input->getOption(self::OPTION_SCHEMA_FILE);

            $this->logger->debug('ShowV3BuilderCoverageCommand.start', [
                'scope' => $scope,
                'scanDirectory' => $scanDirectory,
                'schemaFile' => $schemaFile,
            ]);

            $sdkClassNames = $this->loadSdkClasses($scanDirectory);
            $report = $this->auditor->audit($schemaFile, $sdkClassNames);

            if ($format === 'json') {
                $output->writeln(json_encode($this->reportToArray($report), JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
                return self::SUCCESS;
            }

            $this->renderSummary($output, $report);

            if ((bool) $input->getOption(self::OPTION_SHOW_UNMAPPED) && $report->unmappedEntities !== []) {
                $output->writeln('');
                $output->writeln('<comment>Unmapped OpenAPI DTOs:</comment>');
                $this->renderSingleColumnTable($output, ['Entity key'], $report->unmappedEntities);
            }

            if ((bool) $input->getOption(self::OPTION_SHOW_MISSING_SELECT) && $report->missingSelectBuilders !== []) {
                $output->writeln('');
                $output->writeln('<comment>Missing select builders:</comment>');
                $this->renderSingleColumnTable($output, ['Entity key'], $report->missingSelectBuilders);
            }

            if ((bool) $input->getOption(self::OPTION_SHOW_MISSING_ITEM) && $report->missingItemBuilders !== []) {
                $output->writeln('');
                $output->writeln('<comment>Missing item builders:</comment>');
                $this->renderSingleColumnTable($output, ['Entity key'], $report->missingItemBuilders);
            }

            if ((bool) $input->getOption(self::OPTION_SHOW_INVALID) && $report->invalidBuilderReferences !== []) {
                $output->writeln('');
                $output->writeln('<comment>Invalid builder references:</comment>');
                $rows = array_map(
                    static fn ($r) => [$r['entityKey'], $r['class'], $r['reason']],
                    $report->invalidBuilderReferences
                );
                $table = new Table($output);
                $table->setHeaders(['Entity key', 'Class', 'Reason'])->setRows($rows)->render();
            }

            if ((bool) $input->getOption(self::OPTION_SHOW_SELECT_MISMATCHES) && $report->selectCoverageMismatches !== []) {
                $output->writeln('');
                $output->writeln('<comment>SelectBuilder field coverage mismatches:</comment>');
                $rows = array_map(
                    static fn ($r) => [$r['entityKey'], $r['builderClass'], implode(', ', $r['missingFields'])],
                    $report->selectCoverageMismatches
                );
                $table = new Table($output);
                $table->setHeaders(['Entity key', 'Builder class', 'Missing fields'])->setRows($rows)->render();
            }

            if ((bool) $input->getOption(self::OPTION_SHOW_DUPLICATES) && $report->duplicateEntityKeyMappings !== []) {
                $output->writeln('');
                $output->writeln('<comment>Duplicate entity key mappings:</comment>');
                $rows = array_map(
                    static fn ($r) => [$r['entityKey'], implode(', ', $r['resultClasses'])],
                    $report->duplicateEntityKeyMappings
                );
                $table = new Table($output);
                $table->setHeaders(['Entity key', 'Result classes'])->setRows($rows)->render();
            }

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $io->error(sprintf('Runtime error: %s', $exception->getMessage()));
            return self::INVALID;
        }
    }

    /**
     * @return list<class-string>
     */
    private function loadSdkClasses(string $scanDirectory): array
    {
        $finder = clone $this->finder;
        $finder->files()->in($scanDirectory)->name('*.php');
        foreach ($finder as $file) {
            require_once $file->getRealPath();
        }

        $allClasses = get_declared_classes();
        return array_values(array_filter(
            $allClasses,
            static fn (string $class): bool => strncmp($class, 'Bitrix24\\SDK', 12) === 0
        ));
    }

    private function renderSummary(OutputInterface $output, V3BuilderCoverageReport $report): void
    {
        $output->writeln([
            sprintf('OpenAPI DTO count:          %d', $report->totalOpenApiEntities),
            sprintf('Mapped SDK entities:        %d', $report->mappedEntities),
            sprintf('Entities with selectBuilder:%d', $report->entitiesWithSelectBuilder),
            sprintf('Entities with itemBuilder:  %d', $report->entitiesWithItemBuilder),
            sprintf('Unmapped OpenAPI DTOs:      %d', count($report->unmappedEntities)),
            sprintf('Missing select builders:    %d', count($report->missingSelectBuilders)),
            sprintf('Missing item builders:      %d', count($report->missingItemBuilders)),
            sprintf('Invalid builder references: %d', count($report->invalidBuilderReferences)),
            sprintf('Select coverage mismatches: %d', count($report->selectCoverageMismatches)),
            sprintf('SDK-only mappings:          %d', count($report->sdkOnlyMappings)),
            sprintf('Duplicate entity keys:      %d', count($report->duplicateEntityKeyMappings)),
        ]);
    }

    /**
     * @param list<string> $headers
     * @param list<string> $rows
     */
    private function renderSingleColumnTable(OutputInterface $output, array $headers, array $rows): void
    {
        $table = new Table($output);
        $table->setHeaders($headers)->setRows(array_map(static fn ($r) => [$r], $rows))->render();
    }

    /**
     * @return array<string, mixed>
     */
    private function reportToArray(V3BuilderCoverageReport $report): array
    {
        return [
            'totalOpenApiEntities' => $report->totalOpenApiEntities,
            'mappedEntities' => $report->mappedEntities,
            'entitiesWithSelectBuilder' => $report->entitiesWithSelectBuilder,
            'entitiesWithItemBuilder' => $report->entitiesWithItemBuilder,
            'unmappedEntities' => $report->unmappedEntities,
            'missingSelectBuilders' => $report->missingSelectBuilders,
            'missingItemBuilders' => $report->missingItemBuilders,
            'invalidBuilderReferences' => $report->invalidBuilderReferences,
            'selectCoverageMismatches' => $report->selectCoverageMismatches,
            'sdkOnlyMappings' => $report->sdkOnlyMappings,
            'duplicateEntityKeyMappings' => $report->duplicateEntityKeyMappings,
        ];
    }
}
