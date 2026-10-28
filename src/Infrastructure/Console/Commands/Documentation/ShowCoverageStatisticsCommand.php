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
use Bitrix24\SDK\Deprecations\DeprecatedMethods;
use Bitrix24\SDK\Infrastructure\Console\Commands\SplashScreen;
use Bitrix24\SDK\Services\ServiceBuilderFactory;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Throwable;
use Bitrix24\SDK\Services\Main\Service\Main;

#[AsCommand(
    name: 'b24-dev:show-sdk-coverage-statistics',
    description: 'show statistics for coverage api-methods by sdk per scope',
    hidden: false
)]
class ShowCoverageStatisticsCommand extends Command
{
    private const WEBHOOK_URL = 'webhook';

    public function __construct(
        private readonly AttributesParser $attributesParser,
        private readonly ServiceBuilderFactory $serviceBuilderFactory,
        private readonly Finder $finder,
        private readonly SplashScreen $splashScreen,
        private readonly DeprecatedMethods $deprecatedMethods,
        private readonly LoggerInterface $logger,
        private readonly LegacySdkCoverageCalculator $coverageCalculator = new LegacySdkCoverageCalculator(),
    ) {
        // best practices recommend to call the parent constructor first and
        // then set your own properties. That wouldn't work in this case
        // because configure() needs the properties set in this constructor
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setHelp('Show legacy REST API (v1) coverage against methods available on the configured portal, overall and per scope.')
            ->addOption(
                self::WEBHOOK_URL,
                null,
                InputOption::VALUE_REQUIRED,
                'bitrix24 incoming webhook',
                ''
            )
            ->addOption('show', null, InputOption::VALUE_REQUIRED, 'Report: scope-stat or uncovered')
            ->addOption('scope', null, InputOption::VALUE_REQUIRED, 'Limit the report to a known scope')
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'Output format: table or json (default: table)');
    }

    private function loadAllServiceClasses(): void
    {
        $directory = ['src/Services', 'src/Legacy/Services'];
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
     * @return array
     */
    private function getAllSdkClassNames(string $namespace): array
    {
        $allClasses = get_declared_classes();
        return array_filter($allClasses, static fn ($class) => str_starts_with($class, $namespace));
    }

    private function formatCoverage(LegacySdkCoverageResult $coverage): string
    {
        return sprintf('%.2f%%', $coverage->coveragePercentage)
            . ($coverage->totalPortalMethods === 0 ? ' (no available methods)' : '');
    }

    private function validateOptions(InputInterface $input): void
    {
        $show = $input->getOption('show');
        $scope = $input->getOption('scope');
        $format = $input->getOption('format');
        if ($show === null) {
            if (!$input->isInteractive() || $scope !== null || $format !== null) {
                throw new InvalidArgumentException('Provide --show=scope-stat or --show=uncovered for non-interactive reports or when using --scope/--format.');
            }
        } elseif (!in_array($show, ['scope-stat', 'uncovered'], true)) {
            throw new InvalidArgumentException('Unknown --show value; use scope-stat or uncovered.');
        }

        if ($format !== null && !in_array($format, ['table', 'json'], true)) {
            throw new InvalidArgumentException('Unknown --format value; use table or json.');
        }

        if ($scope !== null && !in_array($scope, Scope::getAvailableScopeCodes(), true)) {
            throw new InvalidArgumentException('Unknown --scope value; use an available Bitrix24 scope code.');
        }

        if ($show === 'uncovered' && $scope === null) {
            throw new InvalidArgumentException('--show=uncovered requires --scope.');
        }

        if ((string)$input->getOption(self::WEBHOOK_URL) === '') {
            throw new InvalidArgumentException('Provide an incoming webhook with --webhook.');
        }
    }

    private function summary(LegacySdkCoverageResult $coverage, ?LegacySdkCoverageResult $inventory = null): array
    {
        return [
            'totalPortalMethods' => $coverage->totalPortalMethods,
            'coveredMethodsCount' => count($coverage->coveredMethods),
            'uncoveredMethodsCount' => count($coverage->uncoveredMethods),
            'sdkOnlyMethodsCount' => count(($inventory ?? $coverage)->sdkOnlyMethods),
            'coveragePercentage' => $coverage->coveragePercentage,
        ];
    }

    /** @param list<\Bitrix24\SDK\Attributes\Services\SupportedInSdkApiMethod> $supportedMethods */
    private function collectScope(Main $main, string $scope, array $classes, string $basePath, array $supportedMethods): array
    {
        $portalMethods = $main->getMethodsByScope($scope)->getResponseData()->getResult();
        $scopeMethods = $this->attributesParser->getSupportedInSdkApiMethods($classes, $basePath, Scope::initFromString($scope));
        // Portal membership determines coverage; scope metadata determines only SDK inventory.
        $coverage = $this->coverageCalculator->calculate($portalMethods, $supportedMethods);
        $inventory = $this->coverageCalculator->calculate($portalMethods, $scopeMethods);

        return [
            'scope' => $scope,
            ...$this->summary($coverage, $inventory),
            'uncoveredMethods' => array_values(array_diff($coverage->uncoveredMethods, array_map(strtolower(...), $this->deprecatedMethods->get()))),
            'uncoveredMethodsIncludingDeprecated' => $coverage->uncoveredMethods,
            'sdkOnlyMethods' => $inventory->sdkOnlyMethods,
        ];
    }

    private function renderScopeTable(OutputInterface $output, array $scopes): void
    {
        $rows = [];
        foreach ($scopes as $scope) {
            $rows[] = [
                $scope['scope'], $scope['totalPortalMethods'], $scope['coveredMethodsCount'],
                $scope['uncoveredMethodsCount'], $scope['sdkOnlyMethodsCount'],
                sprintf('%.2f%%', $scope['coveragePercentage']) . ($scope['totalPortalMethods'] === 0 ? ' (no available methods)' : ''),
            ];
        }

        $table = new Table($output);
        $table->setHeaders(['Scope', 'Portal methods', 'Covered', 'Uncovered', 'SDK-only', 'Coverage'])->setRows($rows)->render();
        $output->writeln([
            '', 'Scope totals may overlap; these are not unique global totals.',
            sprintf('Sum of portal methods by scope: %d', array_sum(array_column($scopes, 'totalPortalMethods'))),
            sprintf('Sum of covered methods by scope: %d', array_sum(array_column($scopes, 'coveredMethodsCount'))),
        ]);
    }

    private function renderUncovered(SymfonyStyle $io, OutputInterface $output, array $scope): void
    {
        $io->info(sprintf('Unsupported in SDK methods (with deprecated): %d', count($scope['uncoveredMethodsIncludingDeprecated'])));
        $output->writeln($scope['uncoveredMethodsIncludingDeprecated']);
        $io->info(sprintf('Unsupported in SDK methods: %d', count($scope['uncoveredMethods'])));
        $output->writeln($scope['uncoveredMethods']);
        $io->info(sprintf('SDK-only methods in scope: %d', count($scope['sdkOnlyMethods'])));
        $output->writeln($scope['sdkOnlyMethods']);
        $output->writeln('--------');
    }

    private function writeError(InputInterface $input, OutputInterface $output, string $message): void
    {
        if ($output instanceof ConsoleOutputInterface) {
            $output->getErrorOutput()->writeln($message, OutputInterface::OUTPUT_RAW);
        } elseif ($input->getOption('format') === 'json') {
            $output->writeln(json_encode(['error' => $message], JSON_THROW_ON_ERROR), OutputInterface::OUTPUT_RAW);
        } else {
            $output->writeln($message, OutputInterface::OUTPUT_RAW);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->validateOptions($input);
        } catch (InvalidArgumentException $exception) {
            $this->writeError($input, $output, $exception->getMessage());

            return self::INVALID;
        }

        $symfonyStyle = new SymfonyStyle($input, $output);
        try {
            $this->logger->debug('ShowCoverageStatisticsCommand.start');
            $main = $this->serviceBuilderFactory->initFromWebhook((string)$input->getOption(self::WEBHOOK_URL))->getMainScope()->main();
            $allApiMethods = $main->getAvailableMethods()->getResponseData()->getResult();
            $this->loadAllServiceClasses();
            $sdkClassNames = $this->getAllSdkClassNames('Bitrix24\\SDK');
            $sdkBasePath = dirname(__FILE__, 6) . '/';
            $supportedMethods = $this->attributesParser->getSupportedInSdkApiMethods($sdkClassNames, $sdkBasePath);
            $coverage = $this->coverageCalculator->calculate($allApiMethods, $supportedMethods);
            $show = $input->getOption('show');
            $scopeCode = $input->getOption('scope');
            $format = $input->getOption('format') ?? 'table';
            $collectScope = fn (string $scope): array => $this->collectScope($main, $scope, $sdkClassNames, $sdkBasePath, $supportedMethods);
            $scopeRows = static fn (array $scope): array => array_diff_key($scope, array_flip(['uncoveredMethods', 'uncoveredMethodsIncludingDeprecated', 'sdkOnlyMethods']));

            if ($show !== null) {
                $report = ['apiVersion' => 'v1', 'basis' => 'configured-portal', 'show' => $show, 'summary' => $this->summary($coverage)];
                if ($show === 'scope-stat') {
                    $report['scopes'] = array_map($scopeRows, array_map($collectScope, $scopeCode === null ? Scope::getAvailableScopeCodes() : [$scopeCode]));
                } else {
                    $scope = $collectScope($scopeCode);
                    $report += array_intersect_key($scope, array_flip(['scope', 'uncoveredMethods', 'uncoveredMethodsIncludingDeprecated', 'sdkOnlyMethods']));
                }

                if ($format === 'json') {
                    $output->writeln(json_encode($report, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_PRESERVE_ZERO_FRACTION), OutputInterface::OUTPUT_RAW);

                    return self::SUCCESS;
                }
            }

            $symfonyStyle->writeln($this->splashScreen::get());
            $output->writeln([
                '', 'Legacy REST API (v1) coverage', 'Basis: methods available on the configured portal', '',
                sprintf('Portal methods: %d', $coverage->totalPortalMethods),
                sprintf('Covered by SDK: %d', count($coverage->coveredMethods)),
                sprintf('Not covered by SDK: %d', count($coverage->uncoveredMethods)),
                sprintf('SDK-only methods: %d', count($coverage->sdkOnlyMethods)),
                sprintf('Coverage: %s', $this->formatCoverage($coverage)), '',
                sprintf('SDK batch wrappers (all API versions, inventory only): %d', count($this->attributesParser->getSupportedInSdkBatchMethods($sdkClassNames))), '',
            ]);

            if ($show === 'scope-stat') {
                $this->renderScopeTable($output, $report['scopes']);

                return self::SUCCESS;
            }

            if ($show === 'uncovered') {
                $this->renderUncovered($symfonyStyle, $output, $report);

                return self::SUCCESS;
            }

            while (true) {
                /** @var QuestionHelper $helper */
                $helper = $this->getHelper('question');
                $question = new ChoiceQuestion('Please, select command', [1 => 'show stat by scope', 2 => 'show not implemented methods in scope', 0 => 'exit🚪']);
                $question->setErrorMessage('Menu item « % s» is invalid . ');
                $menuItem = $helper->ask($input, $output, $question);
                $output->writeln(sprintf('You have just selected: %s', $menuItem));
                switch ($menuItem) {
                    case 'show stat by scope':
                        $this->renderScopeTable($output, array_map($scopeRows, array_map($collectScope, Scope::getAvailableScopeCodes())));
                        break;
                    case 'show not implemented methods in scope':
                        $menuScope = Scope::getAvailableScopeCodes();
                        array_unshift($menuScope, null);
                        unset($menuScope[0]);
                        $menuScope[0] = 'back ⬅️';
                        $question = new ChoiceQuestion('Please, select scope', $menuScope);
                        $question->setErrorMessage('Menu item « % s» is invalid . ');
                        $menuItem = $helper->ask($input, $output, $question);
                        $output->writeln(sprintf('You have just selected: %s', $menuItem));
                        if ($menuItem !== 'back ⬅️') {
                            $this->renderUncovered($symfonyStyle, $output, $collectScope($menuItem));
                        }

                        break;
                    case 'exit🚪':
                        $output->writeln('<info>See you later</info>');

                        return self::SUCCESS;
                }
            }
        } catch (Throwable) {
            $this->writeError($input, $output, 'Unable to generate coverage report. Check the portal connection and credentials.');

            return self::FAILURE;
        }
    }
}
