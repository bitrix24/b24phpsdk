<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Infrastructure\Console\Commands\Generator;

use Bitrix24\SDK\CodeGenerator\ResultItemCodeGenerator;
use Bitrix24\SDK\Infrastructure\Console\Commands\Metadata\DevWebhookResolver;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\Provider\OpenApiResultItemPayloadProvider;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\Payload\ResultItemPayloadBuilder;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\Payload\ResultItemPayload;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\Payload\ResultItemPayloadField;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\Payload\ResultItemPayloadSerializer;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\PhpDoc\ResultItemPhpDocTypeResolver;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\Path\ResultItemTaskPathResolver;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\Verification\ResultItemVerificationApplier;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\Verification\ResultItemPayloadVerifier;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\Verification\ResultItemVerificationReportSerializer;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\Provider\RestDocsResultItemPayloadProvider;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Throwable;

final readonly class DefaultResultItemGeneratorWorkflow
{
    private const string IM_DIALOG_GET_SAMPLE_DIALOG_ID_ENV = 'BITRIX24_PHP_SDK_IM_DIALOG_GET_SAMPLE_DIALOG_ID';

    /**
     * @var \Closure(string): ?string|null
     */
    private ?\Closure $documentationMarkdownPathResolver;

    /**
     * @var \Closure(string): array{namespace: string, className: string, path: string}|null
     */
    private ?\Closure $generationTargetResolver;

    public function __construct(
        private OpenApiResultItemPayloadProvider $openApiPayloadProvider,
        private RestDocsResultItemPayloadProvider $restDocsPayloadProvider,
        private ResultItemPayloadBuilder $payloadBuilder,
        private ResultItemPayloadSerializer $payloadSerializer,
        private ResultItemPayloadVerifier $payloadVerifier,
        private ResultItemVerificationReportSerializer $reportSerializer,
        private ResultItemVerificationApplier $verificationApplier,
        private ResultItemCodeGenerator $codeGenerator,
        private ApiEndpointDocumentationUrlResolver $documentationUrlResolver,
        private DevWebhookResolver $webhookResolver,
        private Filesystem $filesystem,
        private string $schemaFile = 'docs/open-api/openapi.json',
        ?\Closure $documentationMarkdownPathResolver = null,
        ?\Closure $generationTargetResolver = null,
    ) {
        $this->documentationMarkdownPathResolver = $documentationMarkdownPathResolver;
        $this->generationTargetResolver = $generationTargetResolver;
    }

    public function build(string $methodName, string $payloadPath): void
    {
        $openApiPayload = $this->buildOpenApiPayload($methodName);
        $docsPayload = $this->buildDocsPayload($methodName);
        $payload = $this->payloadBuilder->build($openApiPayload, $docsPayload);

        $this->writeFile($payloadPath, $this->payloadSerializer->encode($payload));
    }

    public function verify(string $methodName, string $payloadPath, string $reportPath): void
    {
        $payload = $this->readPayload($payloadPath);
        $report = $this->payloadVerifier->verify(
            $payload,
            $this->webhookResolver->resolve(null),
            $this->resolveSampleParams($methodName),
            $this->resolveResponsePath($methodName),
        );

        $this->writeFile($reportPath, $this->reportSerializer->encode($report));
    }

    public function apply(string $methodName, string $payloadPath, string $reportPath): void
    {
        $payload = $this->readPayload($payloadPath);
        $report = $this->readReport($reportPath);
        $updatedPayload = $this->verificationApplier->apply($payload, $report);

        $this->writeFile($payloadPath, $this->payloadSerializer->encode($updatedPayload));
    }

    public function generate(string $methodName, string $payloadPath): string
    {
        $payload = $this->readPayload($payloadPath);
        $target = $this->resolveGenerationTarget($methodName);
        $code = $this->codeGenerator->generateFromPayload(
            $target['namespace'],
            $target['className'],
            $payload,
        );

        $this->writeFile($target['path'], $code);

        return $target['path'];
    }

    private function buildOpenApiPayload(string $methodName): ?\Bitrix24\SDK\OpenApi\Domain\ResultItem\Payload\ResultItemPayload
    {
        if (!$this->filesystem->exists($this->schemaFile)) {
            return null;
        }

        $entityKey = $this->resolveOpenApiEntityKey($methodName);
        if ($entityKey === null) {
            return null;
        }

        return $this->openApiPayloadProvider->provide(
            schemaFile: $this->schemaFile,
            method: $methodName,
            entityKey: $entityKey,
        );
    }

    private function buildDocsPayload(string $methodName): \Bitrix24\SDK\OpenApi\Domain\ResultItem\Payload\ResultItemPayload
    {
        if ($this->documentationMarkdownPathResolver !== null) {
            $markdownPath = ($this->documentationMarkdownPathResolver)($methodName);
            if ($markdownPath === null) {
                throw new RuntimeException(sprintf(
                    'REST docs payload is required for "%s", but the docs source is unavailable.',
                    $methodName,
                ));
            }

            return $this->provideRestDocsPayload($markdownPath, $methodName);
        }

        $documentationUrl = $this->documentationUrlResolver->resolve($methodName);
        if ($documentationUrl === null) {
            throw new RuntimeException(sprintf(
                'REST docs payload is required for "%s", but the documentation URL could not be resolved.',
                $methodName,
            ));
        }

        $markdownPath = $this->downloadRestDocsMarkdown($documentationUrl);
        try {
            return $this->provideRestDocsPayload($markdownPath, $methodName);
        } finally {
            $this->filesystem->remove($markdownPath);
        }
    }

    private function provideRestDocsPayload(string $markdownPath, string $methodName): ResultItemPayload
    {
        try {
            return $this->restDocsPayloadProvider->provide(
                markdownFile: $markdownPath,
                method: $methodName,
                object: $this->resolveRestDocsObject($methodName),
            );
        } catch (RuntimeException $exception) {
            if ($methodName !== 'im.chat.get') {
                throw $exception;
            }

            return new ResultItemPayload(
                method: $methodName,
                object: 'result',
                generatedFrom: ['b24restdocs'],
                fields: [
                    new ResultItemPayloadField(
                        code: 'ID',
                        sourceType: 'integer',
                        phpdocType: 'int',
                        format: null,
                        required: true,
                        nullable: false,
                        source: 'b24restdocs',
                        description: 'Chat identifier returned by im.chat.get',
                        notes: 'REST docs describe result.ID in the response example without a dedicated Result Object table.',
                    ),
                ],
                sections: [],
            );
        }
    }

    /**
     * @return array{namespace: string, className: string, path: string}
     */
    private function resolveGenerationTarget(string $methodName): array
    {
        if ($this->generationTargetResolver !== null) {
            return ($this->generationTargetResolver)($methodName);
        }

        if ($methodName === 'im.dialog.messages.get') {
            return [
                'namespace' => 'Bitrix24\\SDK\\Services\\IM\\Dialog\\Result',
                'className' => 'MessageItemResult',
                'path' => 'src/Services/IM/Dialog/Result/MessageItemResult.php',
            ];
        }

        if ($methodName === 'im.dialog.users.list') {
            return [
                'namespace' => 'Bitrix24\\SDK\\Services\\IM\\Dialog\\Result',
                'className' => 'DialogUserItemResult',
                'path' => 'src/Services/IM/Dialog/Result/DialogUserItemResult.php',
            ];
        }

        if ($methodName === 'im.dialog.read') {
            return [
                'namespace' => 'Bitrix24\\SDK\\Services\\IM\\Dialog\\Result',
                'className' => 'DialogReadStateItemResult',
                'path' => 'src/Services/IM/Dialog/Result/DialogReadStateItemResult.php',
            ];
        }

        $segments = array_values(array_filter(explode('.', $methodName), static fn (string $segment): bool => $segment !== ''));
        if (count($segments) < 2) {
            throw new RuntimeException(sprintf('Unable to resolve generation target for method "%s".', $methodName));
        }

        $serviceSegments = array_map(
            fn (string $segment): string => $this->classifySegment($segment),
            array_slice($segments, 0, -1),
        );

        $resultBaseName = $serviceSegments[array_key_last($serviceSegments)];
        $className = $resultBaseName . 'ItemResult';

        return [
            'namespace' => 'Bitrix24\\SDK\\Services\\' . implode('\\', $serviceSegments) . '\\Result',
            'className' => $className,
            'path' => 'src/Services/' . implode('/', $serviceSegments) . '/Result/' . $className . '.php',
        ];
    }

    private function classifySegment(string $segment): string
    {
        $normalized = strtolower($segment);

        return match ($normalized) {
            'im' => 'IM',
            'crm' => 'CRM',
            'imopenlines' => 'IMOpenLines',
            default => implode('', array_map(
                static fn (string $chunk): string => ucfirst(strtolower($chunk)),
                preg_split('/[^a-zA-Z0-9]+/', $segment, -1, PREG_SPLIT_NO_EMPTY) ?: [$segment],
            )),
        };
    }

    private function downloadRestDocsMarkdown(string $documentationUrl): string
    {
        $rawMarkdownUrl = $this->toRestDocsRawMarkdownUrl($documentationUrl);
        if ($rawMarkdownUrl === null) {
            throw new RuntimeException(sprintf(
                'REST docs payload is required, but "%s" cannot be mapped to a raw markdown source.',
                $documentationUrl,
            ));
        }

        $markdown = @file_get_contents($rawMarkdownUrl);
        if (!is_string($markdown) || trim($markdown) === '') {
            throw new RuntimeException(sprintf(
                'REST docs payload is required, but markdown download failed for "%s".',
                $rawMarkdownUrl,
            ));
        }

        $temporaryFile = tempnam(sys_get_temp_dir(), 'result-item-docs-');
        if ($temporaryFile === false) {
            throw new RuntimeException('Unable to allocate a temporary file for REST docs markdown.');
        }

        $this->writeFile($temporaryFile, $markdown);

        return $temporaryFile;
    }

    private function toRestDocsRawMarkdownUrl(string $documentationUrl): ?string
    {
        $path = parse_url($documentationUrl, PHP_URL_PATH);
        if (!is_string($path) || !str_ends_with($path, '.html')) {
            return null;
        }

        return 'https://raw.githubusercontent.com/bitrix24/b24restdocs/main'
            . substr($path, 0, -5)
            . '.md';
    }

    private function readPayload(string $payloadPath): \Bitrix24\SDK\OpenApi\Domain\ResultItem\Payload\ResultItemPayload
    {
        return $this->payloadSerializer->decode($this->readFile($payloadPath));
    }

    private function readReport(string $reportPath): \Bitrix24\SDK\OpenApi\Domain\ResultItem\Verification\ResultItemVerificationReport
    {
        return $this->reportSerializer->decode($this->readFile($reportPath));
    }

    private function readFile(string $path): string
    {
        if (!$this->filesystem->exists($path)) {
            throw new RuntimeException(sprintf('Required file "%s" was not found.', $path));
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new RuntimeException(sprintf('Unable to read file "%s".', $path));
        }

        return $contents;
    }

    private function writeFile(string $path, string $contents): void
    {
        $directory = dirname($path);
        if ($directory !== '' && $directory !== '.') {
            $this->filesystem->mkdir($directory);
        }

        $this->filesystem->dumpFile($path, $contents);
    }

    private function resolveOpenApiEntityKey(string $methodName): ?string
    {
        $schemaJson = file_get_contents($this->schemaFile);
        if ($schemaJson === false) {
            return null;
        }

        /** @var array<string, mixed> $schema */
        $schema = json_decode($schemaJson, true, 512, JSON_THROW_ON_ERROR);
        foreach (($schema['paths'] ?? []) as $pathItem) {
            if (!is_array($pathItem)) {
                continue;
            }

            foreach ($pathItem as $operation) {
                if (!is_array($operation) || !$this->operationContainsMethodName($operation, $methodName)) {
                    continue;
                }

                $reference = $this->findFirstSchemaReference($operation['responses'] ?? null);
                if ($reference === null) {
                    continue;
                }

                $prefix = '#/components/schemas/';
                if (!str_starts_with($reference, $prefix)) {
                    continue;
                }

                return substr($reference, strlen($prefix));
            }
        }

        return null;
    }

    private function operationContainsMethodName(array $operation, string $methodName): bool
    {
        foreach ($operation as $value) {
            if (is_string($value) && str_contains($value, $methodName)) {
                return true;
            }

            if (is_array($value) && $this->operationContainsMethodName($value, $methodName)) {
                return true;
            }
        }

        return false;
    }

    private function findFirstSchemaReference(mixed $node): ?string
    {
        if (!is_array($node)) {
            return null;
        }

        if (isset($node['$ref']) && is_string($node['$ref'])) {
            return $node['$ref'];
        }

        foreach ($node as $value) {
            $reference = $this->findFirstSchemaReference($value);
            if ($reference !== null) {
                return $reference;
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveSampleParams(string $methodName): array
    {
        return match ($methodName) {
            'im.dialog.get' => ['DIALOG_ID' => $this->requireImDialogGetSampleDialogId()],
            'im.dialog.messages.get' => ['DIALOG_ID' => $this->requireImDialogGetSampleDialogId(), 'LIMIT' => 10],
            'im.dialog.users.list' => ['DIALOG_ID' => $this->requireImDialogGetSampleDialogId(), 'LIMIT' => 20],
            default => [],
        };
    }

    private function requireImDialogGetSampleDialogId(): string
    {
        $dialogId = $_ENV[self::IM_DIALOG_GET_SAMPLE_DIALOG_ID_ENV]
            ?? $_SERVER[self::IM_DIALOG_GET_SAMPLE_DIALOG_ID_ENV]
            ?? getenv(self::IM_DIALOG_GET_SAMPLE_DIALOG_ID_ENV)
            ?: null;

        if (!is_string($dialogId) || trim($dialogId) === '') {
            throw new RuntimeException(sprintf(
                'Method "im.dialog.get" verification requires env var %s to be set.',
                self::IM_DIALOG_GET_SAMPLE_DIALOG_ID_ENV,
            ));
        }

        return trim($dialogId);
    }

    private function resolveResponsePath(string $methodName): string
    {
        return match ($methodName) {
            'im.dialog.messages.get' => 'messages',
            default => '',
        };
    }

    private function resolveRestDocsObject(string $methodName): string
    {
        return match ($methodName) {
            'im.dialog.messages.get' => 'message',
            'im.chat.get', 'im.dialog.read', 'im.dialog.users.list' => 'result',
            default => 'result-item',
        };
    }
}

#[AsCommand(
    name: 'b24-dev:result-item-generator',
    description: 'Build, verify, apply, and generate ResultItem payloads',
    hidden: false
)]
class ResultItemGeneratorCommand extends Command
{
    private const string METHOD_NAME = 'method-name';
    private const string STAGE = 'stage';

    /**
     * @var list<string>
     */
    private const array STAGES = ['build', 'verify', 'apply', 'generate', 'all'];

    public function __construct(
        private readonly BranchIssueIdResolver $branchIssueIdResolver,
        private readonly ResultItemTaskPathResolver $taskPathResolver,
        private readonly ?object $workflow = null,
    ) {
        parent::__construct();
    }

    public static function createDefaultWorkflow(
        OpenApiResultItemPayloadProvider $openApiPayloadProvider,
        RestDocsResultItemPayloadProvider $restDocsPayloadProvider,
        ResultItemPayloadBuilder $payloadBuilder,
        ResultItemPayloadSerializer $payloadSerializer,
        ResultItemPayloadVerifier $payloadVerifier,
        ResultItemVerificationReportSerializer $reportSerializer,
        ResultItemVerificationApplier $verificationApplier,
        ResultItemCodeGenerator $codeGenerator,
        ApiEndpointDocumentationUrlResolver $documentationUrlResolver,
        DevWebhookResolver $webhookResolver,
        Filesystem $filesystem,
        string $schemaFile = 'docs/open-api/openapi.json',
        ?\Closure $documentationMarkdownPathResolver = null,
        ?\Closure $generationTargetResolver = null,
    ): object {
        return new DefaultResultItemGeneratorWorkflow(
            $openApiPayloadProvider,
            $restDocsPayloadProvider,
            $payloadBuilder,
            $payloadSerializer,
            $payloadVerifier,
            $reportSerializer,
            $verificationApplier,
            $codeGenerator,
            $documentationUrlResolver,
            $webhookResolver,
            $filesystem,
            $schemaFile,
            $documentationMarkdownPathResolver,
            $generationTargetResolver,
        );
    }

    protected function configure(): void
    {
        $this
            ->addArgument(self::METHOD_NAME, InputArgument::REQUIRED, 'Bitrix24 REST method name, e.g. im.dialog.get')
            ->addOption(
                self::STAGE,
                null,
                InputOption::VALUE_REQUIRED,
                'Pipeline stage: build, verify, apply, generate, or all',
                'all'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $methodName = trim((string) $input->getArgument(self::METHOD_NAME));
            if ($methodName === '') {
                throw new InvalidArgumentException('Method name cannot be empty');
            }

            $stage = strtolower(trim((string) $input->getOption(self::STAGE)));
            if ($stage === '') {
                $stage = 'all';
            }

            if (!in_array($stage, self::STAGES, true)) {
                $io->error(sprintf(
                    'Unsupported stage "%s". Allowed values are: %s',
                    $stage,
                    implode(', ', self::STAGES)
                ));

                return self::INVALID;
            }

            $issueId = $this->branchIssueIdResolver->resolve($this->resolveCurrentBranch());
            $payloadPath = $this->taskPathResolver->payloadPath($issueId, $methodName);
            $verificationReportPath = $this->taskPathResolver->verificationReportPath($issueId, $methodName);
            $workflow = $this->workflow ?? throw new RuntimeException('Result item generator workflow is not configured.');
            $this->assertWorkflow($workflow);

            foreach ($this->resolveStages($stage) as $stageName) {
                match ($stageName) {
                    'build' => $this->runBuildStage($workflow, $methodName, $payloadPath, $io),
                    'verify' => $this->runVerifyStage($workflow, $methodName, $payloadPath, $verificationReportPath, $io),
                    'apply' => $this->runApplyStage($workflow, $methodName, $payloadPath, $verificationReportPath, $io),
                    'generate' => $this->runGenerateStage($workflow, $methodName, $payloadPath, $io),
                    default => throw new RuntimeException(sprintf('Unsupported stage "%s".', $stageName)),
                };
            }

            return self::SUCCESS;
        } catch (InvalidArgumentException $exception) {
            $io->error($exception->getMessage());

            return self::INVALID;
        } catch (RuntimeException $exception) {
            $io->error($exception->getMessage());

            return self::FAILURE;
        } catch (Throwable $exception) {
            $io->error(sprintf('Runtime error: %s', $exception->getMessage()));

            return self::FAILURE;
        }
    }

    protected function resolveCurrentBranch(): string
    {
        $process = new Process(['git', 'branch', '--show-current']);
        $process->run();

        $branchName = trim($process->getOutput());
        if ($process->isSuccessful() && $branchName !== '') {
            return $branchName;
        }

        $branchName = $this->resolveCurrentBranchFromGitHead();
        if ($branchName === null) {
            throw new RuntimeException('Unable to determine the current git branch');
        }

        return $branchName;
    }

    private function resolveCurrentBranchFromGitHead(): ?string
    {
        $headPath = '.git/HEAD';
        if (!is_file($headPath)) {
            return null;
        }

        $head = trim((string) file_get_contents($headPath));
        $prefix = 'ref: refs/heads/';
        if (!str_starts_with($head, $prefix)) {
            return null;
        }

        $branchName = substr($head, strlen($prefix));

        return $branchName !== '' ? $branchName : null;
    }

    /**
     * @return list<string>
     */
    private function resolveStages(string $stage): array
    {
        if ($stage === 'all') {
            return ['build', 'verify', 'apply', 'generate'];
        }

        return [$stage];
    }

    private function runBuildStage(
        object $workflow,
        string $methodName,
        string $payloadPath,
        SymfonyStyle $io,
    ): void {
        $workflow->build($methodName, $payloadPath);
        $io->writeln(sprintf('Built payload: %s', $payloadPath));
    }

    private function runVerifyStage(
        object $workflow,
        string $methodName,
        string $payloadPath,
        string $verificationReportPath,
        SymfonyStyle $io,
    ): void {
        $workflow->verify($methodName, $payloadPath, $verificationReportPath);
        $io->writeln(sprintf('Wrote verification report: %s', $verificationReportPath));
    }

    private function runApplyStage(
        object $workflow,
        string $methodName,
        string $payloadPath,
        string $verificationReportPath,
        SymfonyStyle $io,
    ): void {
        $workflow->apply($methodName, $payloadPath, $verificationReportPath);
        $io->writeln(sprintf('Applied verification report to payload: %s', $payloadPath));
    }

    private function runGenerateStage(
        object $workflow,
        string $methodName,
        string $payloadPath,
        SymfonyStyle $io,
    ): void {
        $generatedPath = $workflow->generate($methodName, $payloadPath);
        $io->writeln(sprintf('Generated ResultItem class: %s', $generatedPath));
    }

    private function assertWorkflow(object $workflow): void
    {
        foreach (['build', 'verify', 'apply', 'generate'] as $method) {
            if (!method_exists($workflow, $method)) {
                throw new RuntimeException(sprintf(
                    'Result item generator workflow must provide a "%s" method.',
                    $method,
                ));
            }
        }
    }
}
