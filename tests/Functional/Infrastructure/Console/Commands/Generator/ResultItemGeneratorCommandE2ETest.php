<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Functional\Infrastructure\Console\Commands\Generator;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

final class ResultItemGeneratorCommandE2ETest extends TestCase
{
    private const string METHOD_NAME = 'im.dialog.get';
    private const string ISSUE_ID = '425';
    private const string IM_DIALOG_GET_SAMPLE_DIALOG_ID_ENV = 'BITRIX24_PHP_SDK_IM_DIALOG_GET_SAMPLE_DIALOG_ID';
    private const string REGENERATION_SENTINEL = 'TASK6_E2E_REGENERATION_SENTINEL';

    private Filesystem $filesystem;
    private string $projectRoot;
    private string $payloadPath;
    private string $reportPath;
    private string $generatedClassPath;
    private string $gitShimDirectory;
    private bool $generatedClassExisted;
    private ?string $originalGeneratedClassContents;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filesystem = new Filesystem();
        $this->projectRoot = dirname(__DIR__, 6);
        $taskDirectory = sprintf(
            '%s/.tasks/%s/%s',
            $this->projectRoot,
            self::ISSUE_ID,
            self::METHOD_NAME,
        );

        $this->payloadPath = $taskDirectory . '/result-item.payload.yaml';
        $this->reportPath = $taskDirectory . '/result-item.verification-report.yaml';
        $this->generatedClassPath = $this->projectRoot . '/src/Services/IM/Dialog/Result/DialogItemResult.php';
        $this->gitShimDirectory = sys_get_temp_dir() . '/result-item-generator-git-shim-' . uniqid('', true);
        $this->generatedClassExisted = $this->filesystem->exists($this->generatedClassPath);
        $this->originalGeneratedClassContents = $this->generatedClassExisted
            ? (string) file_get_contents($this->generatedClassPath)
            : null;

        $this->filesystem->mkdir($this->gitShimDirectory);
        $gitShimPath = $this->gitShimDirectory . '/git';
        $this->filesystem->dumpFile($gitShimPath, <<<'SH'
#!/bin/sh

if [ "$1" = "branch" ] && [ "$2" = "--show-current" ]; then
  printf '%s\n' 'feature/425-add-im-dialog-service'
  exit 0
fi

printf '%s\n' "Unsupported git invocation: $*" >&2
exit 1
SH);
        chmod($gitShimPath, 0755);
    }

    protected function tearDown(): void
    {
        $this->filesystem->remove($this->gitShimDirectory);

        if ($this->generatedClassExisted && $this->originalGeneratedClassContents !== null) {
            $this->filesystem->dumpFile($this->generatedClassPath, $this->originalGeneratedClassContents);
        }

        if (!$this->generatedClassExisted) {
            $this->filesystem->remove($this->generatedClassPath);
        }

        parent::tearDown();
    }

    #[Test]
    public function allStageProducesArtifactsAndGeneratesNonNullableDateCreateAnnotation(): void
    {
        $sampleDialogId = $this->resolveSampleDialogId();

        $this->filesystem->remove([$this->payloadPath, $this->reportPath]);
        $this->filesystem->dumpFile($this->generatedClassPath, <<<'PHP'
<?php

declare(strict_types=1);

// TASK6_E2E_REGENERATION_SENTINEL
PHP);
        self::assertStringContainsString(
            self::REGENERATION_SENTINEL,
            (string) file_get_contents($this->generatedClassPath),
        );

        $process = new Process(
            ['php', 'bin/console', 'b24-dev:result-item-generator', self::METHOD_NAME, '--stage=all'],
            $this->projectRoot,
            array_merge($_ENV, [
                'PATH' => $this->gitShimDirectory . ':' . ((string) getenv('PATH')),
                self::IM_DIALOG_GET_SAMPLE_DIALOG_ID_ENV => $sampleDialogId,
            ]),
        );
        $process->setTimeout(300);
        $process->run();

        self::assertTrue(
            $process->isSuccessful(),
            sprintf(
                "Command failed with exit code %s.\nSTDOUT:\n%s\nSTDERR:\n%s",
                (string) $process->getExitCode(),
                $process->getOutput(),
                $process->getErrorOutput(),
            ),
        );

        self::assertFileExists($this->payloadPath);
        self::assertFileExists($this->reportPath);
        $generatedContents = (string) file_get_contents($this->generatedClassPath);
        self::assertFileExists($this->generatedClassPath);
        self::assertStringNotContainsString(self::REGENERATION_SENTINEL, $generatedContents);
        self::assertStringContainsString(
            '@property-read Carbon\\CarbonImmutable $date_create',
            $generatedContents,
        );
    }

    private function resolveSampleDialogId(): string
    {
        $sampleDialogId = $_ENV[self::IM_DIALOG_GET_SAMPLE_DIALOG_ID_ENV]
            ?? getenv(self::IM_DIALOG_GET_SAMPLE_DIALOG_ID_ENV)
            ?: null;

        if (!is_string($sampleDialogId) || $sampleDialogId === '') {
            self::markTestSkipped(sprintf(
                'Set %s to a valid dialog id to run the functional ResultItem generator E2E test.',
                self::IM_DIALOG_GET_SAMPLE_DIALOG_ID_ENV,
            ));
        }

        return $sampleDialogId;
    }
}
