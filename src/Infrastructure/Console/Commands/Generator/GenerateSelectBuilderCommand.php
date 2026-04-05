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

use Bitrix24\SDK\CodeGenerator\SelectBuilderCodeGenerator;
use Bitrix24\SDK\OpenApi\Domain\OpenApiSchemaEntityReader;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Throwable;

#[AsCommand(
    name: 'b24-dev:generate-select-builder',
    description: 'Generate a SelectBuilder class for a v3 entity from the OpenAPI schema',
    hidden: false
)]
class GenerateSelectBuilderCommand extends Command
{
    private const ENTITY = 'entity';
    private const NAMESPACE = 'namespace';
    private const CLASS_NAME = 'class-name';
    private const OUTPUT = 'output';
    private const SCHEMA_FILE = 'schema-file';

    public function __construct(
        private readonly OpenApiSchemaEntityReader $entityReader,
        private readonly SelectBuilderCodeGenerator $codeGenerator,
        private readonly Filesystem $filesystem,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setHelp(
                'Reads the checked-in OpenAPI snapshot, lets you choose a v3 entity, ' .
                'and generates a ready-to-use *SelectBuilder PHP class.'
            )
            ->addArgument(
                self::ENTITY,
                InputArgument::OPTIONAL,
                'Entity key from components.schemas, e.g. bitrix.tasks.taskdto'
            )
            ->addOption(
                self::NAMESPACE,
                null,
                InputOption::VALUE_REQUIRED,
                'Target PHP namespace for the generated class (default: derived from entity key)'
            )
            ->addOption(
                self::CLASS_NAME,
                null,
                InputOption::VALUE_REQUIRED,
                'Class name for the generated builder (default: derived from entity key)'
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
            $schemaFile = trim((string) $input->getOption(self::SCHEMA_FILE));
            $entityKey = $this->resolveEntityKey($input, $output, $schemaFile);
            $fields = $this->entityReader->getSelectableFields($schemaFile, $entityKey);

            $namespace = $this->resolveNamespace($input, $entityKey);
            $className = $this->resolveClassName($input, $entityKey);

            $code = $this->codeGenerator->generate($namespace, $className, $fields, $entityKey);

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

    private function resolveEntityKey(InputInterface $input, OutputInterface $output, string $schemaFile): string
    {
        $entityKey = trim((string) $input->getArgument(self::ENTITY));
        if ($entityKey !== '') {
            return $entityKey;
        }

        if (!$input->isInteractive()) {
            throw new InvalidArgumentException('Entity argument is required in non-interactive mode');
        }

        $keys = $this->entityReader->getEntityKeys($schemaFile);
        if ($keys === []) {
            throw new RuntimeException('No entity schemas found in the OpenAPI schema');
        }

        /** @var \Symfony\Component\Console\Helper\QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion('Select v3 entity', $keys);
        $question->setErrorMessage('Entity "%s" is invalid.');

        return (string) $helper->ask($input, $output, $question);
    }

    private function resolveNamespace(InputInterface $input, string $entityKey): string
    {
        $ns = $input->getOption(self::NAMESPACE);
        if (is_string($ns) && $ns !== '') {
            return $ns;
        }

        // bitrix.<module>.<entity>dto → Bitrix24\SDK\Services\<PascalModule>\Service
        $parts = explode('.', $entityKey);
        $module = isset($parts[1]) ? ucfirst($parts[1]) : 'Unknown';

        return sprintf('Bitrix24\\SDK\\Services\\%s\\Service', $module);
    }

    private function resolveClassName(InputInterface $input, string $entityKey): string
    {
        $cn = $input->getOption(self::CLASS_NAME);
        if (is_string($cn) && $cn !== '') {
            return $cn;
        }

        // bitrix.<module>.<entity>dto → <PascalEntity>SelectBuilder
        $parts = explode('.', $entityKey);
        $entityRaw = isset($parts[2]) ? str_replace('dto', '', $parts[2]) : 'Entity';

        return ucfirst($entityRaw) . 'SelectBuilder';
    }
}
