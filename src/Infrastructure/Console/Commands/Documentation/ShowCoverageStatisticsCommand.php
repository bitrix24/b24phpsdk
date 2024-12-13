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
use Symfony\Component\Filesystem\Filesystem;
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
        private readonly LoggerInterface $logger
    ) {
        // best practices recommend to call the parent constructor first and
        // then set your own properties. That wouldn't work in this case
        // because configure() needs the properties set in this constructor
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setHelp('show statistics for coverage api-methods by sdk per scope')
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
     * @return array
     */
    private function getAllSdkClassNames(string $namespace): array
    {
        $allClasses = get_declared_classes();
        return array_filter($allClasses, static function ($class) use ($namespace) {
            return strncmp($class, $namespace, 12) === 0;
        });
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

            // load and filter classes in namespace Bitrix24\SDK from folder src/Services
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

            $allApiMethodsCnt = count($allApiMethods);
            $supportedInSdkMethodsCnt = count($supportedInSdkMethods);
            $supportedInSdkBatchMethodsCnt = count($supportedInSdkBatchMethods);

            $output->writeln([
                '',
                sprintf('Bitrix24 API-methods count: %d', $allApiMethodsCnt),
                sprintf('Supported in bitrix24-php-sdk methods count: %d', $supportedInSdkMethodsCnt),
                sprintf(
                    'Coverage percentage: %s%% 🚀',
                    round(($supportedInSdkMethodsCnt * 100) / $allApiMethodsCnt, 2)
                ),
                '',
                sprintf(
                    'Supported in bitrix24-php-sdk methods with batch wrapper count: %d',
                    $supportedInSdkBatchMethodsCnt
                ),
                ''
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

                            // calculate metrics
                            $totalMethodsCnt += count($apiMethods);
                            $supportedInSdkMethodsCnt += count($sdkMethods);

                            $percentage = '–';
                            if (count($apiMethods) > 0 && count($sdkMethods) > 0) {
                                $percentage = round(count($sdkMethods) * 100 / count($apiMethods), 2);
                            }

                            $methodsByScope[] = [
                                $scopeCode,
                                count($apiMethods),
                                count($sdkMethods),
                                $percentage
                            ];
                        }
                        $progressBar->finish();

                        $table = new Table($output);
                        $table
                            ->setHeaders(
                                ['Scope', 'API-methods count', 'SDK supported count', 'SDK supported percentage']
                            )
                            ->setRows($methodsByScope);
                        $table->render();

                        $io->writeln(
                            [
                                '',
                                sprintf('total methods by scope count: %s', $totalMethodsCnt),
                                sprintf('total methods supported in SDK count: %s', $supportedInSdkMethodsCnt)
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

                        $apiMethods = array_map(
                            'strtolower',
                            $sb->getMainScope()->main()->getMethodsByScope($menuItem)->getResponseData()->getResult()
                        );
                        $sdkMethods = array_map(
                            'strtolower',
                            array_column(
                                $this->attributesParser->getSupportedInSdkApiMethods(
                                    $sdkClassNames,
                                    $sdkBasePath,
                                    Scope::initFromString($menuItem),
                                ),
                                'name'
                            )
                        );
                        sort($apiMethods);
                        sort($sdkMethods);

                        $unsupportedMethods = array_diff($apiMethods, $sdkMethods);
                        $io->info(sprintf('Unsupported in SDK methods (with deprecated): %s', count($unsupportedMethods)));
                        $output->writeln($unsupportedMethods);

                        $actualMethods = array_diff($apiMethods, $this->deprecatedMethods->get());
                        $unsupportedMethods = array_diff($actualMethods, $sdkMethods);
                        $io->info(sprintf('Unsupported in SDK methods: %s', count($unsupportedMethods)));

                        $output->writeln($unsupportedMethods);
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
