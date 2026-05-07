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

namespace Bitrix24\SDK\OpenApi\Infrastructure\Console;

use Bitrix24\SDK\Core\Credentials\WebhookUrl;
use Bitrix24\SDK\Infrastructure\Console\Commands\SplashScreen;
use Bitrix24\SDK\Services\ServiceBuilderFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'b24-dev:build-schema',
    // this short description is shown when running "php bin/console list"
    description: 'Build OpenAPI schema',
    // this is shown when running the command with the "--help" option
    help: 'This command builds OpenAPI schema for Bitrix24 API.',
)]
class SchemaBuilder
{
    protected const string WEBHOOK = 'webhook';

    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly SplashScreen $splashScreen,
        protected LoggerInterface $logger
    ) {
    }

    public function __invoke(
        InputInterface $input,
        OutputInterface $output,
        #[Option]
        string $webhook = self::WEBHOOK,
    ): int {
        $style = new SymfonyStyle($input, $output);
        try {
            $style->writeln($this->splashScreen::get());
            $style->writeln('Building OpenAPI schema...');

            $sb = ServiceBuilderFactory::createServiceBuilderFromWebhook($webhook, null, $this->logger);
            $payload = $sb->getMainScope()->documentation()->getSchema()->getPayload();

            $this->filesystem->dumpFile('docs/open-api/openapi.json', $payload);
            $style->success('Schema built successfully into docs/open-api/openapi.json file');
        } catch (\Throwable $exception) {
            $this->logger->critical('ShemaBuilder.error', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString()
            ]);
            $style->caution('fatal error');
            $style->text(
                [
                    $exception->getMessage(),
                    $exception->getTraceAsString(),
                ]
            );

            return Command::FAILURE;
        }
        return Command::SUCCESS;
    }
}
