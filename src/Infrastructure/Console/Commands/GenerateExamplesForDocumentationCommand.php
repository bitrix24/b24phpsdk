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

namespace Bitrix24\SDK\Infrastructure\Console\Commands;

use Bitrix24\SDK\Attributes\Services\AttributesParser;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Services\ServiceBuilderFactory;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Throwable;
use function Typhoon\Type\stringify;

#[AsCommand(
    name: 'b24-dev:generate-examples',
    description: 'generate examples for documentation',
    hidden: false
)]
class GenerateExamplesForDocumentationCommand extends Command
{
    const TARGET_FOLDER = 'folder';

    public function __construct(
        private readonly AttributesParser $attributesParser,
        private readonly Finder           $finder,
        private readonly Filesystem       $filesystem,
        private readonly LoggerInterface  $logger)
    {
        // best practices recommend to call the parent constructor first and
        // then set your own properties. That wouldn't work in this case
        // because configure() needs the properties set in this constructor
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                self::TARGET_FOLDER,
                null,
                InputOption::VALUE_REQUIRED,
                'folder for generated examples',
                ''
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        try {
            $targetFolder = (string)$input->getOption(self::TARGET_FOLDER);
            if ($targetFolder === '') {
                throw new InvalidArgumentException('you must provide a folder to save generated examples');
            }
            // get sdk root path, change magic number if move current file to another folder depth
            $sdkBasePath = dirname(__FILE__, 5) . '/';
            $this->logger->debug('GenerateExamplesForDocumentationCommand.start', [

                'targetFolder' => $targetFolder,
                'sdkBasePath' => $sdkBasePath
            ]);
            $io->info('Generate api examples...');

            $rootSbMethods = $this->attributesParser->getClassMethods(ServiceBuilder::class);
            foreach ($rootSbMethods as $method) {
                print(sprintf('%s:%s',
                    $method['method_name'],
                    $method['method_return_type']
                ).PHP_EOL);
            }

            $rootSbMethods = $this->attributesParser->getClassMethods('Bitrix24\SDK\Services\CRM\CRMServiceBuilder');
            foreach ($rootSbMethods as $method) {
                print(sprintf('%s:%s',
                        $method['method_name'],
                        $method['method_return_type']
                    ).PHP_EOL);
            }


        } catch (Throwable $exception) {
            $io->error(sprintf('runtime error: %s', $exception->getMessage()));
            $io->info($exception->getTraceAsString());

            return self::INVALID;
        }
        return self::SUCCESS;
    }

    protected function prepareDataForPrompt(
        string $methodName,
        string $className,
    ):array
    {

//
//
//
//        return [
//            '#PHP_VERSION#' => '',
//            '#METHOD_NAME#' => $methodName,
//            '#CLASS_NAME#' => $className,
//            '#METHOD_ARGUMENTS#' => $methodArguments
//            '#ROOT_SERVICE_BUILDER_METHODS#' => $methodArguments
//            '#SCOPE_SERVICE_BUILDER_METHODS#' => $methodArguments
//            '#CLASS_SOURCE_CODE#' => $methodArguments
//        ];
    }

    protected function generateGptPromptByMethod():string
    {

    }
}