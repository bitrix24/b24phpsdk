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

namespace Bitrix24\SDK\Infrastructure\Console\Commands\Metadata;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\OpenApi\Domain\OaFieldListMethodResolver;
use InvalidArgumentException;
use JsonException;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

#[AsCommand(
    name: 'b24-dev:show-v3-field-metadata',
    description: 'show v3 field metadata for an exact entity from the checked-in OpenAPI snapshot',
    hidden: false
)]
class ShowV3FieldMetadataCommand extends Command
{
    private const ENTITY = 'entity';
    private const FORMAT = 'format';
    private const WEBHOOK = 'webhook';
    private const SCHEMA_FILE = 'schema-file';

    public function __construct(
        private readonly OaFieldListMethodResolver $oaFieldListMethodResolver,
        private readonly DevWebhookResolver $devWebhookResolver,
        private readonly Bitrix24V3FieldMetadataFetcher $v3FieldMetadataFetcher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setHelp(
                'Read the checked-in OpenAPI snapshot, resolve an exact v3 entity key, call <entity>.field.list, and print field metadata as json or table.'
            )
            ->addArgument(
                self::ENTITY,
                InputArgument::OPTIONAL,
                'Exact OA entity key without the .field.list suffix, for example tasks.task'
            )
            ->addOption(
                self::FORMAT,
                null,
                InputOption::VALUE_REQUIRED,
                'Output format: json or table',
                'json'
            )
            ->addOption(
                self::WEBHOOK,
                null,
                InputOption::VALUE_REQUIRED,
                'Bitrix24 incoming webhook'
            )
            ->addOption(
                self::SCHEMA_FILE,
                null,
                InputOption::VALUE_REQUIRED,
                'Path to checked-in OpenAPI schema snapshot',
                'docs/open-api/openapi.json'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $schemaFile = trim((string)$input->getOption(self::SCHEMA_FILE));
            if ($schemaFile === '') {
                throw new InvalidArgumentException('Schema file path cannot be empty');
            }

            $format = strtolower(trim((string)$input->getOption(self::FORMAT)));
            if (!in_array($format, ['json', 'table'], true)) {
                throw new InvalidArgumentException(sprintf('Unsupported format "%s"', $format));
            }

            $entityKey = $this->resolveEntityKey($input, $output, $schemaFile);
            $methodName = $this->oaFieldListMethodResolver->resolveFieldListMethodName($schemaFile, $entityKey);
            $webhook = $this->devWebhookResolver->resolve($input->getOption(self::WEBHOOK));
            $fieldMetadata = $this->v3FieldMetadataFetcher->fetch($webhook, $methodName);
            $outputPayload = $this->unwrapMetadataCollection($fieldMetadata) ?? $this->normalizeFieldMetadata($fieldMetadata);

            if ($format === 'table') {
                $this->renderTable($output, $outputPayload);
            } else {
                $output->writeln($this->encodeJson($outputPayload, true));
            }

            return self::SUCCESS;
        } catch (InvalidArgumentException|RuntimeException|JsonException $exception) {
            $io->error($exception->getMessage());

            return self::INVALID;
        } catch (BaseException $exception) {
            $io->error(sprintf('Bitrix24 error: %s', $exception->getMessage()));

            return self::FAILURE;
        } catch (Throwable $exception) {
            $io->error(sprintf('runtime error: %s', $exception->getMessage()));

            return self::FAILURE;
        }
    }

    private function resolveEntityKey(InputInterface $input, OutputInterface $output, string $schemaFile): string
    {
        $entityKey = trim((string)$input->getArgument(self::ENTITY));
        if ($entityKey !== '') {
            return $entityKey;
        }

        if (!$input->isInteractive()) {
            throw new InvalidArgumentException('Entity is required in non-interactive mode');
        }

        $entityKeys = $this->oaFieldListMethodResolver->getEntityKeys($schemaFile);
        if ($entityKeys === []) {
            throw new RuntimeException('No v3 field metadata entities found in the OpenAPI schema');
        }

        /** @var \Symfony\Component\Console\Helper\QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion('Select v3 entity key', $entityKeys);
        $question->setErrorMessage('Entity "%s" is invalid.');

        return (string)$helper->ask($input, $output, $question);
    }

    /**
     * @param array<string, mixed> $fieldMetadata
     * @return list<array{
     *     code: string,
     *     title: string,
     *     metadata: mixed
     * }>
     */
    private function normalizeFieldMetadata(array $fieldMetadata): array
    {
        $normalizedFieldMetadata = [];
        foreach ($fieldMetadata as $code => $metadata) {
            $normalizedFieldMetadata[] = [
                'code' => (string)$code,
                'title' => $this->extractTitle((string)$code, $metadata),
                'metadata' => $metadata,
            ];
        }

        return $normalizedFieldMetadata;
    }

    private function extractTitle(string $code, mixed $metadata): string
    {
        if (!is_array($metadata)) {
            return $code;
        }

        foreach ($metadata as $key => $value) {
            if (strtolower((string)$key) !== 'title') {
                continue;
            }

            return is_scalar($value) ? (string)$value : $code;
        }

        return $code;
    }

    /**
     * @param list<array<string, mixed>>|list<array{
     *     code: string,
     *     title: string,
     *     metadata: mixed
     * }> $tablePayload
     *
     * @throws JsonException
     */
    private function renderTable(OutputInterface $output, array $tablePayload): void
    {
        if ($this->isMetadataCollectionRows($tablePayload)) {
            $this->renderMetadataCollectionTable($output, $tablePayload);

            return;
        }

        $rows = [];
        foreach ($tablePayload as $fieldMetadata) {
            $rows[] = [
                $fieldMetadata['code'],
                $fieldMetadata['title'],
                $this->encodeJson($fieldMetadata['metadata'], false),
            ];
        }

        (new Table($output))
            ->setHeaders(['code', 'title', 'metadata'])
            ->setRows($rows)
            ->render();
    }

    /**
     * @param array<string, mixed> $fieldMetadata
     * @return list<array<string, mixed>>|null
     */
    private function unwrapMetadataCollection(array $fieldMetadata): ?array
    {
        if (count($fieldMetadata) !== 1) {
            return null;
        }

        $metadataCollection = array_values($fieldMetadata)[0];
        if (!is_array($metadataCollection) || !array_is_list($metadataCollection)) {
            return null;
        }

        foreach ($metadataCollection as $metadataItem) {
            if (!is_array($metadataItem)) {
                return null;
            }
        }

        /** @var list<array<string, mixed>> $metadataCollection */
        return $metadataCollection;
    }

    /**
     * @param list<array<string, mixed>>|list<array{
     *     code: string,
     *     title: string,
     *     metadata: mixed
     * }> $rows
     */
    private function isMetadataCollectionRows(array $rows): bool
    {
        if ($rows === []) {
            return false;
        }

        $firstRow = $rows[0];

        return is_array($firstRow) && array_key_exists('name', $firstRow) && !array_key_exists('metadata', $firstRow);
    }

    /**
     * @param list<array<string, mixed>> $metadataCollection
     *
     * @throws JsonException
     */
    private function renderMetadataCollectionTable(OutputInterface $output, array $metadataCollection): void
    {
        $headers = [];
        foreach ($metadataCollection as $metadataItem) {
            foreach (array_keys($metadataItem) as $key) {
                if (!in_array($key, $headers, true)) {
                    $headers[] = $key;
                }
            }
        }

        $rows = [];
        foreach ($metadataCollection as $metadataItem) {
            $row = [];
            foreach ($headers as $header) {
                $row[] = $this->stringifyTableValue($metadataItem[$header] ?? null);
            }
            $rows[] = $row;
        }

        (new Table($output))
            ->setHeaders($headers)
            ->setRows($rows)
            ->render();
    }

    /**
     * @throws JsonException
     */
    private function stringifyTableValue(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return (string)$value;
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if ($value === null) {
            return 'null';
        }

        return $this->encodeJson($value, false);
    }

    /**
     * @throws JsonException
     */
    private function encodeJson(mixed $payload, bool $pretty): string
    {
        $flags = JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
        if ($pretty) {
            $flags |= JSON_PRETTY_PRINT;
        }

        return json_encode($payload, $flags);
    }
}
