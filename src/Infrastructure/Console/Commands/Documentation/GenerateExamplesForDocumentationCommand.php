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
use Bitrix24\SDK\Core\Exceptions\FileNotFoundException;
use Bitrix24\SDK\Services\CRM\CRMServiceBuilder;
use Bitrix24\SDK\Services\ServiceBuilder;
use InvalidArgumentException;
use OpenAI;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
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
    const PROMPT_TEMPLATE_FILE = 'prompt-template';
    const EXAMPLE_TEMPLATE_FILE = 'example-template';
    const OPEN_AI_API_KEY = 'openai-api-key';
    const OPEN_AI_MODEL = 'openai-model';

    public function __construct(
        private readonly TyphoonReflector $typhoonReflector,
        private readonly AttributesParser $attributesParser,
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
            )
            ->addOption(
                self::PROMPT_TEMPLATE_FILE,
                null,
                InputOption::VALUE_REQUIRED,
                'master prompt template markdown file',
                ''
            )
            ->addOption(
                self::EXAMPLE_TEMPLATE_FILE,
                null,
                InputOption::VALUE_REQUIRED,
                'example template php file',
                ''
            )
            ->addOption(
                self::OPEN_AI_MODEL,
                null,
                InputOption::VALUE_REQUIRED,
                'open ai model code',
                'gpt-4o-mini'
            )
            ->addOption(
                self::OPEN_AI_API_KEY,
                null,
                InputOption::VALUE_REQUIRED,
                'open ai API key',
                ''
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        try {
            $targetFolder = (string)$input->getOption(self::TARGET_FOLDER);
            if ($targetFolder === '') {
                throw new InvalidArgumentException(sprintf('you must provide a folder to save generated examples in option «%s»', self::TARGET_FOLDER));
            }
            $promptTemplateFile = (string)$input->getOption(self::PROMPT_TEMPLATE_FILE);
            if ($promptTemplateFile === '') {
                throw new InvalidArgumentException(sprintf('you must provide a markdown file with prompt template in option «%s»', self::PROMPT_TEMPLATE_FILE));
            }
            $exampleTemplateFile = (string)$input->getOption(self::EXAMPLE_TEMPLATE_FILE);
            if ($exampleTemplateFile === '') {
                throw new InvalidArgumentException(sprintf('you must provide a example template file with php template in option «%s»', self::EXAMPLE_TEMPLATE_FILE));
            }
            $openAiModel = (string)$input->getOption(self::OPEN_AI_MODEL);
            if ($openAiModel === '') {
                throw new InvalidArgumentException(sprintf('you must provide an open ai model code in option «%s»', self::OPEN_AI_MODEL));
            }
            $openAiKey = (string)$input->getOption(self::OPEN_AI_API_KEY);
            if ($openAiKey === '') {
                throw new InvalidArgumentException(sprintf('you must provide an open ai api key in option «%s»', self::OPEN_AI_API_KEY));
            }

            // get sdk root path, change magic number if move current file to another folder depth
            $sdkBasePath = dirname(__FILE__, 6) . '/';
            $this->logger->debug('GenerateExamplesForDocumentationCommand.start', [
                'targetFolder' => $targetFolder,
                'sdkBasePath' => $sdkBasePath,
                'promptTemplateFile' => $promptTemplateFile
            ]);
            $io->info('Generate prompts for each service api method...');

            // require all SDK services
            $this->requireAllClassesByPath('src/Services');
            $sdkClassNames = $this->getAllSdkClassNames();
            $supportedInSdkMethods = $this->attributesParser->getSupportedInSdkApiMethods($sdkClassNames, $sdkBasePath);

            while (true) {
                /**
                 * @var QuestionHelper $helper
                 */
                $helper = $this->getHelper('question');
                $question = new ChoiceQuestion(
                    'Please select command',
                    [
                        1 => 'generate prompts',
                        2 => 'generate examples with GPT',
                        3 => 'build examples in php files',
                        0 => 'exit🚪'
                    ],
                    null
                );
                $question->setErrorMessage('Menu item «%s» is invalid.');
                $menuItem = $helper->ask($input, $output, $question);
                $output->writeln(sprintf('You have just selected: %s', $menuItem));

                switch ($menuItem) {
                    case 'generate prompts':
                        $output->writeln(['<info>Generate prompts for each supported in SDK method...</info>', '']);
                        $progressBar = new ProgressBar($output, count($supportedInSdkMethods));
                        $promptTemplate = $this->loadContentsFromFile($promptTemplateFile);
                        foreach ($supportedInSdkMethods as $apiMethod => $sdkMethod) {
                            $promptFileName = sprintf('%s/prompts/%s/%s.md',
                                $targetFolder,
                                $apiMethod,
                                $apiMethod,
                            );
                            $data = $this->prepareDataForPromptByServiceMethod(
                                CRMServiceBuilder::class,
                                $sdkMethod['sdk_class_name'],
                                $sdkMethod['sdk_method_name']
                            );
                            $prompt = $this->fillDataToTemplate(
                                $promptTemplate,
                                $data
                            );
                            $this->savePrompt($promptFileName, $prompt);
                            $progressBar->advance();
                        }
                        $progressBar->finish();
                        $output->writeln(['', sprintf('<comment>All prompts generated and stored in folder «%s»</comment>', $targetFolder), '']);
                        break;
                    case 'generate examples with GPT':
                        // generate examples based on prompts
                        $output->writeln(['<info>Generate examples based on prompts for each supported in SDK method...</info>', '']);
                        $generatedExamples = [];
                        $generatedExamplesFolder = $sdkBasePath . $targetFolder . '/var/prompts';
                        foreach ((new Finder())->in($generatedExamplesFolder)->directories()->sortByName() as $directory) {
                            $methodName = $directory->getFilename();
                            $generatedExamples[$methodName] = sprintf('%s/%s.md', $methodName, $methodName);
                        }

                        // generate examples
                        $progressBar = new ProgressBar($output, count($generatedExamples));
                        foreach ($generatedExamples as $methodName => $promptPath) {
                            $exampleFilePath = sprintf('%s/var/examples/%s/%s.md',
                                $targetFolder,
                                $methodName,
                                $methodName
                            );
                            if ($this->filesystem->exists($exampleFilePath)) {
                                $progressBar->advance();
                                continue;
                            }
                            $promptFilePath = sprintf('%s%s/var/prompts/%s',
                                $sdkBasePath,
                                $targetFolder,
                                $promptPath
                            );

                            $promptBody = file_get_contents($promptFilePath);


                            $result = $this->generateExampleFromGpt($openAiModel, $openAiKey, $promptBody);
                            $this->saveToFile($exampleFilePath, $result);
                            $progressBar->advance();
                        }
                        $progressBar->finish();
                        $output->writeln(['', sprintf('<comment>All examples generated and stored in folder «%s/examples»</comment>', $targetFolder), '']);
                        break;
                    case 'build examples in php files':
                        $output->writeln(['<info>Generate examples from GPT-codegen results for each supported in SDK method...</info>', '']);
                        $exampleTemplate = $this->loadContentsFromFile($exampleTemplateFile);

                        $generatedExamples = [];
                        $generatedExamplesFolder = $sdkBasePath . $targetFolder . '/var/examples';
                        foreach ((new Finder())->in($generatedExamplesFolder)->directories()->sortByName() as $directory) {
                            $methodName = $directory->getFilename();
                            $generatedExamples[$methodName] = sprintf('%s/%s.md', $methodName, $methodName);
                        }

                        // build examples
                        $progressBar = new ProgressBar($output, count($generatedExamples));
                        foreach ($generatedExamples as $methodName => $examplePath) {
                            $generatedExamplePath = sprintf('%s%s/var/examples/%s',
                                $sdkBasePath,
                                $targetFolder,
                                $examplePath
                            );
                            $generatedExample = $this->loadContentsFromFile($generatedExamplePath);
                            $finalExample = $this->fillDataToTemplate(
                                $exampleTemplate,
                                [
                                    '```php' => '',
                                    '```' => '',
                                    '###GENERATED_EXAMPLE_CODE###' => $generatedExample
                                ]
                            );
                            $finalExampleFilePath = sprintf('%s/result/%s/%s.php',
                                $targetFolder,
                                $methodName,
                                $methodName
                            );
                            $this->saveToFile($finalExampleFilePath, $finalExample);
                            $progressBar->advance();
                        }
                        $progressBar->finish();
                        $output->writeln(['', sprintf('<comment>All examples generated and stored in folder «%s/result»</comment>', $targetFolder), '']);
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

    /**
     * @return array
     */
    private function getAllSdkClassNames(): array
    {
        $allClasses = get_declared_classes();
        $namespace = 'Bitrix24\SDK\Services';
        return array_filter($allClasses, static function ($class) use ($namespace) {
            return strncmp($class, $namespace, 21) === 0;
        });
    }

    /**
     * @param non-empty-string $targetNamespace
     * @param non-empty-string $className
     * @return bool
     */
    private function isClassInTargetNamespace(string $targetNamespace, string $className): bool
    {
        return strncmp($className, $targetNamespace, strlen($targetNamespace)) === 0;
    }

    /**
     * @param non-empty-string $path
     */
    private function requireAllClassesByPath(string $path): void
    {
        foreach ((new Finder())->files()->in($path)->name('*.php') as $file) {
            if ($file->isDir()) {
                continue;
            }

            $absoluteFilePath = $file->getRealPath();
            require_once $absoluteFilePath;
        }
    }

    /**
     * @param non-empty-string $filename
     * @param string $examplePayload
     * @return void
     */
    private function saveToFile(string $filename, string $examplePayload): void
    {
        $this->filesystem->dumpFile($filename, $examplePayload);
    }

    /**
     * @param non-empty-string $model
     * @param non-empty-string $apiKey
     * @param non-empty-string $prompt
     * @return ?string
     */
    private function generateExampleFromGpt(string $model, string $apiKey, string $prompt): ?string
    {
        $client = OpenAI::client($apiKey);
        $result = $client->chat()->create([
            'model' => $model,
            'temperature' => 0.5,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $prompt
                ],
            ],
        ]);

        return $result->choices[0]->message->content;
    }

    private function savePrompt($fileName, $content): void
    {
        $this->filesystem->dumpFile($fileName, $content);
    }

    private function fillDataToTemplate(string $template, array $data): string
    {
        //todo validate template and keys in data
        return str_replace(array_keys($data), array_values($data), $template);
    }

    /**
     * @param non-empty-string $fileName
     * @throws FileNotFoundException
     */
    private function loadContentsFromFile(string $fileName): string
    {
        if (!$this->filesystem->exists($fileName)) {
            throw new FileNotFoundException(sprintf('file not found: %s', $fileName));
        }

        return file_get_contents($fileName);
    }

    /**
     * @throws FileNotFoundException
     * @throws \Bitrix24\SDK\Core\Exceptions\InvalidArgumentException
     */
    protected function prepareDataForPromptByServiceMethod(
        string $serviceBuilderClassName,
        string $serviceClassName,
        string $serviceMethodName,
    ): array
    {
        $this->logger->debug('generateGptPromptByServiceMethod.start');

        // pack method parameters
        $methodParameters = PHP_EOL;
        foreach ($this->getMethodParameters($serviceClassName, $serviceMethodName) as $parameter) {
            $methodParameters .= sprintf('%s%s $%s',
                    $parameter['is_optional'] === true ? '?' : '',
                    $parameter['type'],
                    $parameter['name'],

                ) . PHP_EOL;
        }

        // pack root service builder methods
        $rootSbMethods = '';
        foreach ($this->getClassMethods(ServiceBuilder::class) as $method) {
            $rootSbMethods .= sprintf('%s:%s',
                    $method['method_name'],
                    $method['method_return_type']
                ) . PHP_EOL;
        }

        // pack service builder methods
        $sbMethods = '';
        foreach ($this->getClassMethods($serviceBuilderClassName) as $method) {
            $sbMethods .= sprintf('%s:%s',
                    $method['method_name'],
                    $method['method_return_type']
                ) . PHP_EOL;
        }

        $returnResultClassName = $this->getMethodReturnResultType($serviceClassName, $serviceMethodName);

        // get methods list for result class
        $returnResultClassMethods = $this->getClassMethods($returnResultClassName);
        $subordinateClassesSourceCode = '';
        foreach ($returnResultClassMethods as $returnResultClassMethod) {
            if (!$returnResultClassMethod['is_declared_in_current_file']) {
                continue;
            }

            $itemType = $returnResultClassMethod['method_return_type'];
            // it can be
            //  - array
            if ($this->isListType($itemType)) {
                $itemType = $this->extractItemTypeFromListType($returnResultClassMethod['method_return_type']);
            }
            //  - one item
            // skip int, bool, string, CarbonImmutable etc
            if (!$this->isClassInTargetNamespace('Bitrix24\SDK\Services', $itemType)) {
                continue;
            }

            $subordinateClassesSourceCode .= $this->getClassSourceCode($itemType, false);
        }

        $result = [
            '#PHP_VERSION#' => PHP_VERSION,
            '#METHOD_NAME#' => $serviceMethodName,
            '#CLASS_NAME#' => $serviceClassName,
            '#METHOD_RETURN_RESULT_TYPE#' => $returnResultClassName,
            '#RETURN_RESULT_CLASS_SOURCE_CODE#' => $this->getClassSourceCode($returnResultClassName, false),
            '#RETURN_RESULT_SUBORDINATE_CLASSES_SOURCE_CODE#' => $subordinateClassesSourceCode,
            '#METHOD_PARAMETERS#' => $methodParameters,
            //todo cache?
            '#ROOT_SERVICE_BUILDER_METHODS#' => $rootSbMethods,
            //todo cache?
            '#SCOPE_SERVICE_BUILDER_METHODS#' => $sbMethods,
            //todo cache?
            '#CLASS_SOURCE_CODE#' => $this->getClassSourceCode($serviceClassName)
        ];

        $this->logger->debug('generateGptPromptByServiceMethod.finish', [
            'result' => $result
        ]);

        return $result;
    }

    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\InvalidArgumentException
     */
    private function extractItemTypeFromListType(string $listType): string
    {
        if (!$this->isListType($listType)) {
            throw new \Bitrix24\SDK\Core\Exceptions\InvalidArgumentException(sprintf('type «%s» is not list type', $listType));
        }
        // skip "array<" and ">"
        return substr($listType, 6, -1);
    }

    /**
     * @param non-empty-string $type
     */
    private function isListType(string $type): bool
    {
        return str_starts_with($type, 'array<');
    }

    /**
     * @throws FileNotFoundException
     */
    private function getClassSourceCode(string $className, bool $isSkipHeader = true): string
    {
        $typhoonClassMeta = $this->typhoonReflector->reflectClass($className);
        $fileName = $typhoonClassMeta->file();

        if (!$this->filesystem->exists($fileName)) {
            throw new FileNotFoundException(sprintf('for class «%s» file «%s» not found', $className, $fileName));
        }

        $source = file($fileName);
        if ($isSkipHeader) {
            $length = $typhoonClassMeta->location()->endLine - $typhoonClassMeta->location()->startLine;
            return implode("", array_slice($source, $typhoonClassMeta->location()->startLine - 1, $length + 1));
        }

        return implode("", array_slice($source, 1, $typhoonClassMeta->location()->endLine));
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
                'is_declared_in_current_file' => $method->file() === $typhoonClassMeta->file(),
            ];
        }
        return $methods;
    }

    /**
     * @param non-empty-string $className
     * @param non-empty-string $methodName
     * @return string
     */
    private function getMethodReturnResultType(string $className, string $methodName): string
    {
        $class = $this->typhoonReflector->reflectClass($className);
        $method = $class->methods()[$methodName];
        return stringify($method->returnType());
    }

    /**
     * @param class-string $className
     * @param non-empty-string $methodName
     * @return array<int<0, max>, array{is_optional: bool, name: non-empty-string, type: non-empty-string, is_has_default_value: bool, default_value: mixed}>
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