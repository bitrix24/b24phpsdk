<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';

use Bitrix24\SDK\Services\Workflows\Common\WorkflowAutoExecutionType;
use Bitrix24\SDK\Services\Workflows\Common\WorkflowDocumentType;
use Examples\Workflows\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\SingleCommandApplication;

(new SingleCommandApplication())
    ->setCode(function (InputInterface $input, OutputInterface $output): int {
        Application::getLog()->debug('cliCommand.start');
        try {
            while (true) {
                /**
                 * @var QuestionHelper $helper
                 *
                 * method «setCode» override «execute» method for object Command
                 * we use SingleCommandApplication for reduce code in this example
                 */
                // @phpstan-ignore-next-line
                $helper = $this->getHelper('question');
                $question = new ChoiceQuestion(
                    'Please select command',
                    [
                        1 => 'workflow template: list',
                        2 => 'workflow template: add template from file',
                        3 => 'workflow template: delete added templates',
                        4 => 'workflow: start',
                        5 => 'workflow: instances',
                        6 => 'workflow: terminate',
                        7 => 'workflow: kill',
                        8 => 'workflow task: list',
                        9 => 'workflow task: complete',
                        0 => 'exit🚪'
                    ],
                    null
                );
                $question->setErrorMessage('Menu item «%s» is invalid.');
                $menuItem = $helper->ask($input, $output, $question);
                $output->writeln(sprintf('You have just selected: %s', $menuItem));

                $sb = Application::getB24Service();
                switch ($menuItem) {
                    case 'workflow template: list':
                        $output->writeln('<info>get workflow template list...</info>');

                        $table = new Table($output);
                        $table->setHeaders(['ID', 'NAME', 'MODULE_ID', 'ENTITY', 'DOCUMENT_TYPE', 'AUTO_EXECUTE']);
                        foreach ($sb->getBizProcScope()->template()->list()->getTemplates() as $template) {
                            $table->addRow(
                                [
                                    $template->ID,
                                    $template->NAME,
                                    $template->MODULE_ID,
                                    $template->ENTITY,
                                    implode(' ', $template->DOCUMENT_TYPE),
                                    $template->AUTO_EXECUTE->name
                                ]
                            );
                        }
                        $table->render();
                        break;
                    case 'workflow template: add template from file':
                        $output->writeln('<info>try to add new workflow template from file...</info>');
                        $result = $sb->getBizProcScope()->template()->add(
                            WorkflowDocumentType::buildForContact(),
                            'test_workflow_for_contact',
                            'Test workflow for contact with demonstration options',
                            WorkflowAutoExecutionType::withoutAutoExecution,
                            dirname(__DIR__) . '/templates/contact-demo-percentage.bpt'
                        );

                        $output->writeln(sprintf('added template id: %s', $result->getId()));
                        break;
                    case 'exit🚪':
                        Application::getLog()->debug('cliCommand.finish');
                        return Command::SUCCESS;
                }
            }
        } catch (Throwable $exception) {
            Application::getLog()->debug('cliCommand.error', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString()
            ]);
            $output->writeln(sprintf('<error>ERROR: %s</error>', $exception->getMessage()));
            $output->writeln(sprintf('<info>DETAILS: %s</info>', $exception->getTraceAsString()));

            return Command::FAILURE;
        }
    })
    ->run();