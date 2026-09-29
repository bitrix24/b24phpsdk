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
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Throwable;

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
            );
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
        return array_filter($allClasses, static function ($class) use ($namespace) {
            return strncmp($class, $namespace, strlen($namespace)) === 0;
        });
    }

    private function formatCoverage(LegacySdkCoverageResult $coverage): string
    {
        return sprintf('%.2f%%', $coverage->coveragePercentage)
            . ($coverage->totalPortalMethods === 0 ? ' (no available methods)' : '');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        try {
            $b24Webhook = (string)$input->getOption(self::WEBHOOK_URL);
            if ($b24Webhook === '') {
                throw new InvalidArgumentException('you must provide a webhook url in argument «webhook»');
            }
            $this->logger->debug('ShowCoverageStatisticsCommand.start', [
                'b24Webhook' => $b24Webhook,
            ]);

            // get all available api methods
            $sb = $this->serviceBuilderFactory->initFromWebhook($b24Webhook);
            $allApiMethods = $sb->getMainScope()->main()->getAvailableMethods()->getResponseData()->getResult();

            // Discover both current and legacy service wrappers.
            $this->loadAllServiceClasses();
            $sdkClassNames = $this->getAllSdkClassNames('Bitrix24\SDK');
            // get sdk root path, change magic number if move current file to another folder depth
            $sdkBasePath = dirname(__FILE__, 6) . '/';

            $io->writeln($this->splashScreen::get());

            $supportedInSdkMethods = $this->attributesParser->getSupportedInSdkApiMethods(
                $sdkClassNames,
                $sdkBasePath
            );

            $supportedInSdkBatchMethods = $this->attributesParser->getSupportedInSdkBatchMethods(
                $sdkClassNames
            );

            $coverage = $this->coverageCalculator->calculate($allApiMethods, $supportedInSdkMethods);

            $output->writeln([
                '',
                'Legacy REST API (v1) coverage',
                'Basis: methods available on the configured portal',
                '',
                sprintf('Portal methods: %d', $coverage->totalPortalMethods),
                sprintf('Covered by SDK: %d', count($coverage->coveredMethods)),
                sprintf('Not covered by SDK: %d', count($coverage->uncoveredMethods)),
                sprintf('SDK-only methods: %d', count($coverage->sdkOnlyMethods)),
                sprintf('Coverage: %s', $this->formatCoverage($coverage)),
                '',
                sprintf('SDK batch wrappers (all API versions, inventory only): %d', count($supportedInSdkBatchMethods)),
                '',
            ]);

            while (true) {
                /**
                 * @var QuestionHelper $helper
                 */
                $helper = $this->getHelper('question');
                $question = new ChoiceQuestion(
                    'Please, select command',
                    [
                        1 => 'show stat by scope',
                        2 => 'show not implemented methods in scope',
                        0 => 'exit🚪'
                    ],
                    null
                );
                $question->setErrorMessage('Menu item « % s» is invalid . ');
                $menuItem = $helper->ask($input, $output, $question);
                $output->writeln(sprintf('You have just selected: %s', $menuItem));

                switch ($menuItem) {
                    case 'show stat by scope':
                        $progressBar = new ProgressBar($output, count(Scope::getAvailableScopeCodes()));
                        $methodsByScope = [];

                        $totalMethodsCnt = 0;
                        $supportedInSdkMethodsCnt = 0;
                        foreach (Scope::getAvailableScopeCodes() as $scopeCode) {
                            $progressBar->advance();
                            $apiMethods = $sb->getMainScope()->main()->getMethodsByScope($scopeCode)->getResponseData(
                            )->getResult();

                            $sdkMethods = $this->attributesParser->getSupportedInSdkApiMethods(
                                $sdkClassNames,
                                $sdkBasePath,
                                Scope::initFromString($scopeCode),
                            );

                            // Portal membership determines coverage; SDK scope metadata only determines its own inventory.
                            $scopeCoverage = $this->coverageCalculator->calculate($apiMethods, $supportedInSdkMethods);
                            $scopeInventory = $this->coverageCalculator->calculate($apiMethods, $sdkMethods);
                            $totalMethodsCnt += $scopeCoverage->totalPortalMethods;
                            $supportedInSdkMethodsCnt += count($scopeCoverage->coveredMethods);

                            $methodsByScope[] = [
                                $scopeCode,
                                $scopeCoverage->totalPortalMethods,
                                count($scopeCoverage->coveredMethods),
                                count($scopeCoverage->uncoveredMethods),
                                count($scopeInventory->sdkOnlyMethods),
                                $this->formatCoverage($scopeCoverage),
                            ];
                        }
                        $progressBar->finish();

                        $table = new Table($output);
                        $table
                            ->setHeaders(
                                ['Scope', 'Portal methods', 'Covered', 'Uncovered', 'SDK-only', 'Coverage']
                            )
                            ->setRows($methodsByScope);
                        $table->render();

                        $io->writeln(
                            [
                                '',
                                'Scope totals may overlap; these are not unique global totals.',
                                sprintf('Sum of portal methods by scope: %d', $totalMethodsCnt),
                                sprintf('Sum of covered methods by scope: %d', $supportedInSdkMethodsCnt)
                            ]
                        );
                        break;
                    case 'show not implemented methods in scope':
                        $menuScope = Scope::getAvailableScopeCodes();
                        array_unshift($menuScope, null);
                        unset($menuScope[0]);
                        $menuScope[0] = 'back ⬅️';

                        $question = new ChoiceQuestion(
                            'Please, select scope',
                            $menuScope,
                            null
                        );
                        $question->setErrorMessage('Menu item « % s» is invalid . ');
                        $menuItem = $helper->ask($input, $output, $question);
                        $output->writeln(sprintf('You have just selected: %s', $menuItem));

                        $apiMethods = $sb->getMainScope()->main()->getMethodsByScope($menuItem)->getResponseData()->getResult();
                        $sdkMethods = $this->attributesParser->getSupportedInSdkApiMethods(
                            $sdkClassNames,
                            $sdkBasePath,
                            Scope::initFromString($menuItem),
                        );
                        // Portal membership determines coverage; SDK scope metadata only determines its own inventory.
                        $scopeCoverage = $this->coverageCalculator->calculate($apiMethods, $supportedInSdkMethods);
                        $scopeInventory = $this->coverageCalculator->calculate($apiMethods, $sdkMethods);

                        $io->info(sprintf('Unsupported in SDK methods (with deprecated): %d', count($scopeCoverage->uncoveredMethods)));
                        $output->writeln($scopeCoverage->uncoveredMethods);

                        $unsupportedMethods = array_values(array_diff(
                            $scopeCoverage->uncoveredMethods,
                            array_map(strtolower(...), $this->deprecatedMethods->get()),
                        ));
                        $io->info(sprintf('Unsupported in SDK methods: %d', count($unsupportedMethods)));
                        $output->writeln($unsupportedMethods);

                        $io->info(sprintf('SDK-only methods in scope: %d', count($scopeInventory->sdkOnlyMethods)));
                        $output->writeln($scopeInventory->sdkOnlyMethods);
                        $output->writeln('--------');

                        break;
                    case 'exit🚪':
                        $output->writeln('<info>See you later</info>');
                        return Command::SUCCESS;
                }
            }
        } catch (Throwable $exception) {
            $io->error(sprintf('runtime error: %s', $exception->getMessage()));
            $io->info($exception->getTraceAsString());

            return self::INVALID;
        }
    }
}
