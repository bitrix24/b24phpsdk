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

namespace Bitrix24\SDK\Infrastructure\Console\Commands\Generator;

use Bitrix24\SDK\CodeGenerator\ItemBuilderCodeGenerator;
use Bitrix24\SDK\OpenApi\Domain\OpenApiSchemaEntityReader;
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
use Throwable;

#[AsCommand(
    name: 'b24-dev:generate-item-builder',
    description: 'Generate an ItemBuilder class for a v3 operation from the OpenAPI schema',
    hidden: false
)]
class GenerateItemBuilderCommand extends Command
{
    private const string OPERATION_PATH = 'operation-path';
    private const string NAMESPACE = 'namespace';
    private const string CLASS_NAME = 'class-name';
    private const string OUTPUT = 'output';
    private const string SCHEMA_FILE = 'schema-file';

    public function __construct(
        private readonly OpenApiSchemaEntityReader $entityReader,
        private readonly ItemBuilderCodeGenerator $codeGenerator,
        private readonly Filesystem $filesystem,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setHelp(
                'Reads the checked-in OpenAPI snapshot and generates a ready-to-use *ItemBuilder PHP class ' .
                'with typed setter methods for all writable fields of the given operation.'
            )
            ->addArgument(
                self::OPERATION_PATH,
                InputArgument::REQUIRED,
                'OpenAPI path for the add operation, e.g. /tasks.task.add'
            )
            ->addOption(
                self::NAMESPACE,
                null,
                InputOption::VALUE_REQUIRED,
                'Target PHP namespace for the generated class (default: derived from operation path)'
            )
            ->addOption(
                self::CLASS_NAME,
                null,
                InputOption::VALUE_REQUIRED,
                'Class name for the generated builder (default: derived from operation path)'
            )
            ->addOption(
                self::OUTPUT,
                null,
                InputOption::VALUE_REQUIRED,
                'Output file path; prints to stdout when omitted'
            )
            ->addOption(
                self::SCHEMA_FILE,
                null,
                InputOption::VALUE_REQUIRED,
                'Path to the OpenAPI schema snapshot',
                'docs/open-api/openapi.json'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $schemaFile    = trim((string) $input->getOption(self::SCHEMA_FILE));
            $operationPath = trim((string) $input->getArgument(self::OPERATION_PATH));

            $writableFields = $this->entityReader->getWritableFields($schemaFile, $operationPath);

            $namespace = $this->resolveNamespace($input, $operationPath);
            $className = $this->resolveClassName($input, $operationPath);

            $code = $this->codeGenerator->generate($namespace, $className, $writableFields, $operationPath);

            $outputPath = $input->getOption(self::OUTPUT);
            if (is_string($outputPath) && $outputPath !== '') {
                $this->filesystem->dumpFile($outputPath, $code);
                $io->success(sprintf('Generated %s → %s', $className, $outputPath));
            } else {
                $output->write($code);
            }

            return self::SUCCESS;
        } catch (InvalidArgumentException | RuntimeException $e) {
            $io->error($e->getMessage());
            return self::INVALID;
        } catch (Throwable $e) {
            $io->error(sprintf('Runtime error: %s', $e->getMessage()));
            return self::FAILURE;
        }
    }

    private function resolveNamespace(InputInterface $input, string $operationPath): string
    {
        $ns = $input->getOption(self::NAMESPACE);
        if (is_string($ns) && $ns !== '') {
            return $ns;
        }

        // /tasks.task.add → Bitrix24\SDK\Services\Task\Service
        // /crm.deal.add   → Bitrix24\SDK\Services\CRM\Service
        $parts = explode('.', ltrim($operationPath, '/'));
        $module = isset($parts[0]) ? ucfirst($parts[0]) : 'Unknown';

        return sprintf('Bitrix24\\SDK\\Services\\%s\\Service', $module);
    }

    private function resolveClassName(InputInterface $input, string $operationPath): string
    {
        $cn = $input->getOption(self::CLASS_NAME);
        if (is_string($cn) && $cn !== '') {
            return $cn;
        }

        // /tasks.task.add → TaskItemBuilder  (module.entity.action → <PascalEntity>ItemBuilder)
        $parts = explode('.', ltrim($operationPath, '/'));
        $entity = isset($parts[1]) ? ucfirst($parts[1]) : 'Entity';

        return $entity . 'ItemBuilder';
    }
}
