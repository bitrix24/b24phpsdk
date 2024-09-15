<?php
require_once dirname(__DIR__). '/vendor/autoload.php';

use App\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;

(new SingleCommandApplication())
    ->setCode(function (InputInterface $input, OutputInterface $output): int {
        Application::getLog()->debug('cliCommand.start');
        try {
            $output->writeln(['<info>Example CLI application</info>','']);
            $output->writeln('Try to connect to bitrix24 with local application credentials and call method «server.time»...');

            // get auth data stored in file and call bitrix24 rest api
            $sb = Application::getB24Service();

            // see server time from bitrix24 rest api response
            $output->writeln(sprintf('server time: %s',$sb->getMainScope()->main()->getServerTime()->time()->format('Y-m-d H-i-s')));

        } catch (Throwable $exception) {
            Application::getLog()->debug('cliCommand.error', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString()
            ]);
            $output->writeln(sprintf('<error>ERROR: %s</error>', $exception->getMessage()));
            $output->writeln(sprintf('<info>DETAILS: %s</info>', $exception->getTraceAsString()));

            return Command::FAILURE;
        }
        Application::getLog()->debug('cliCommand.finish');
        return Command::SUCCESS;
    })
    ->run();