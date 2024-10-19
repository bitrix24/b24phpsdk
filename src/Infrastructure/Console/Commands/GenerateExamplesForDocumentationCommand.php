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
use Bitrix24\SDK\Core\Exceptions\FileNotFoundException;
use Bitrix24\SDK\Services\CRM\CRMServiceBuilder;
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
use Typhoon\Reflection\TyphoonReflector;
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
        private TyphoonReflector          $typhoonReflector,
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

            $data = $this->prepareDataForPromptByServiceMethod(
                CRMServiceBuilder::class,
                \Bitrix24\SDK\Services\CRM\Deal\Service\Deal::class,
                'list'
            );


//            dd($data);


//            dd($this->getClassSourceCode(\Bitrix24\SDK\Services\CRM\Deal\Service\Deal::class));

//            $this->attributesParser->getMethodParameters(
//                \Bitrix24\SDK\Services\CRM\Deal\Service\Deal::class,
//                'list'
//            );

//            $rootSbMethods = $this->attributesParser->getClassMethods(ServiceBuilder::class);
//
//            foreach ($rootSbMethods as $method) {
//                print(sprintf('%s:%s',
//                    $method['method_name'],
//                    $method['method_return_type']
//                ).PHP_EOL);
//            }
//
//            $rootSbMethods = $this->attributesParser->getClassMethods('Bitrix24\SDK\Services\CRM\CRMServiceBuilder');
//            foreach ($rootSbMethods as $method) {
//                print(sprintf('%s:%s',
//                        $method['method_name'],
//                        $method['method_return_type']
//                    ).PHP_EOL);
//            }


        } catch (Throwable $exception) {
            $io->error(sprintf('runtime error: %s', $exception->getMessage()));
            $io->info($exception->getTraceAsString());

            return self::INVALID;
        }
        return self::SUCCESS;
    }

    protected function prepareDataForPromptByServiceMethod(
        string $serviceBuilderClassName,
        string $serviceClassName,
        string $serviceMethodName,
    ): array
    {
        $this->logger->debug('generateGptPromptByServiceMethod.start', [

        ]);

        $result = [
            '#PHP_VERSION#' => PHP_VERSION,
            '#METHOD_NAME#' => $serviceMethodName,
            '#CLASS_NAME#' => $serviceClassName,
            '#METHOD_PARAMETERS#' => $this->getMethodParameters($serviceClassName, $serviceMethodName),
            //todo cache?
            '#ROOT_SERVICE_BUILDER_METHODS#' => $this->getClassMethods(ServiceBuilder::class),
            //todo cache?
            '#SCOPE_SERVICE_BUILDER_METHODS#' => $this->getClassMethods($serviceBuilderClassName),
            //todo cache?
            '#CLASS_SOURCE_CODE#' => $this->getClassSourceCode($serviceClassName)
        ];

        $this->logger->debug('generateGptPromptByServiceMethod.finish', [
            'result' => $result
        ]);

        return $result;
    }

    /**
     * @throws FileNotFoundException
     */
    private function getClassSourceCode(string $className): string
    {
        $typhoonClassMeta = $this->typhoonReflector->reflectClass($className);
        $fileName = $typhoonClassMeta->file();

        if (!$this->filesystem->exists($fileName)) {
            throw new FileNotFoundException(sprintf('for class «%s» file «%s» not found', $className, $fileName));
        }

        $source = file($fileName);
        $length = $typhoonClassMeta->location()->endLine - $typhoonClassMeta->location()->startLine;
        return implode("", array_slice($source, $typhoonClassMeta->location()->startLine - 1, $length));
    }

    /**
     * @return array<int<0,max>, array<string,string>>
     */
    private function getClassMethods(string $className): array
    {
        $typhoonClassMeta = $this->typhoonReflector->reflectClass($className);
        $methods = [];
        foreach ($typhoonClassMeta->methods() as $method) {
            if (!$method->isPublic()) {
                continue;
            }
            // skip __construct()
            if (stringify($method->returnType()) === 'mixed') {
                continue;
            }
            $methods[] = [
                'method_name' => str_replace('method ', '', $method->id->describe()),
                'method_return_type' => stringify($method->returnType()),
            ];
        }
        return $methods;
    }

    /**
     * @param class-string $className
     * @param non-empty-string $methodName
     * @return array
     */
    private function getMethodParameters(string $className, string $methodName): array
    {
        $class = $this->typhoonReflector->reflectClass($className);
        $method = $class->methods()[$methodName];

        $parameters = [];
        foreach ($method->parameters() as $parameterName => $parameterData) {
            $parameters[] = [
                'is_optional' => $parameterData->isOptional(),
                'name' => $parameterName,
                'type' => stringify($parameterData->type()),
                'is_has_default_value' => $parameterData->hasDefaultValue(),
                'default_value' => $parameterData->evaluateDefault()
            ];
        }
        return $parameters;
    }
}