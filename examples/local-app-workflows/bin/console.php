<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';

use Bitrix24\SDK\Services\Workflows\Common\DocumentType;
use Bitrix24\SDK\Services\Workflows\Common\WorkflowAutoExecutionType;
use Bitrix24\SDK\Services\Workflows\Common\WorkflowDocumentType;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Services\Workflows\Common\WorkflowTaskCompleteStatusType;
use Examples\Workflows\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
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
                        3 => 'workflow template: delete added template',
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
                    case 'workflow template: delete added template':
                        $output->writeln('<info>try to delete added template by id...</info>');
                        $question = new Question('Please enter the workflow template id for delete: ');
                        $rawId = $helper->ask($input, $output, $question);
                        $templateId = (int)$rawId;
                        if ($templateId === 0) {
                            throw new InvalidArgumentException(sprintf('invalid workflow template id «%s»', $rawId));
                        }

                        $result = $sb->getBizProcScope()->template()->delete($templateId);
                        //todo add issue api returns emtpy result
                        $output->writeln(sprintf('workflow template delete result: %s', $result->isSuccess() ? 'success' : 'failure'));
                        break;
                    case 'workflow: start':
                        $output->writeln('<info>try to start workflow on contact...</info>');
                        $contactId = $sb->getCRMScope()->contact()->add([
                            'NAME' => sprintf('test_%s', time()),
                        ])->getId();

                        $question = new Question('Please enter the workflow template id: ');
                        $rawId = $helper->ask($input, $output, $question);
                        $templateId = (int)$rawId;
                        if ($templateId === 0) {
                            throw new InvalidArgumentException(sprintf('invalid workflow template id «%s»', $rawId));
                        }

                        try {
                            // try to run without required parameters )))))))
                            //todo add issue to rest-api, we now can run workflow without required parameters
                            $wfInstanceIdWithoutParams = $sb->getBizProcScope()->workflow()->start(
                                DocumentType::crmContact,
                                $templateId,
                                $contactId,
                                []
                            )->getRunningWorkflowInstanceId();
                            //todo add url to contact
                            $output->writeln(sprintf('running workflow id: «%s» for contact «%s»', $wfInstanceIdWithoutParams, $contactId));
                        } catch (Throwable $exception) {
                            $output->writeln(sprintf('<error>%s</error>', $exception->getMessage()));
                            $output->writeln(sprintf('<info>DETAILS: %s</info>', $exception->getTraceAsString()));
                        }

                        try {
                            // try to run with required parameters
                            $contactId = $sb->getCRMScope()->contact()->add([
                                'NAME' => sprintf('test_%s', time()),
                            ])->getId();
                            $wfInstanceIdWithParams = $sb->getBizProcScope()->workflow()->start(
                                DocumentType::crmContact,
                                $templateId,
                                $contactId,
                                [
                                    'discount_percentage' => 50,
                                    'comment' => sprintf('hello from demo application at %s', (new DateTime())->format('H:i:s d.m.Y'))
                                ]
                            )->getRunningWorkflowInstanceId();
                            //todo add url to contact
                            $output->writeln(sprintf('running workflow id: «%s» for contact «%s»', $wfInstanceIdWithParams, $contactId));
                        } catch (Throwable $exception) {
                            $output->writeln(sprintf('<error>%s</error>', $exception->getMessage()));
                            $output->writeln(sprintf('<info>DETAILS: %s</info>', $exception->getTraceAsString()));
                        }

                        break;
                    case 'workflow: instances':
                        $output->writeln('<info>get running workflow instances list...</info>');

                        $table = new Table($output);
                        $table->setHeaders(['ID', 'TEMPLATE_ID', 'STARTED', 'MODIFIED', 'OWNED_UNTIL', 'MODULE_ID', 'ENTITY', 'DOCUMENT_ID', 'STARTED_BY']);
                        foreach ($sb->getBizProcScope()->workflow()->instances()->getInstances() as $item) {
                            $table->addRow(
                                [
                                    $item->ID,
                                    $item->TEMPLATE_ID,
//todo fix optional fields
                                    $item->STARTED->format('H:i:s d.m.Y'),
                                    $item->MODIFIED->format('H:i:s d.m.Y'),
                                    $item->OWNED_UNTIL?->format('H:i:s d.m.Y'),
                                    $item->MODULE_ID,
                                    $item->ENTITY,
                                    $item->DOCUMENT_ID,
                                    $item->STARTED_BY
                                ]
                            );
                        }

                        $table->render();
                        break;
                    case 'workflow: terminate':
                        $output->writeln('<info>try to terminate workflow instance...</info>');
                        $question = new Question('Please enter running workflow id: ');
                        $rawId = $helper->ask($input, $output, $question);

                        try {
                            $result = $sb->getBizProcScope()->workflow()->terminate($rawId, sprintf('terminate from cli at %s', (new DateTime())->format('H:i:s d.m.Y')));
                            $output->writeln(sprintf('terminate result: %s', $result->isSuccess() ? 'succes' : 'failure'));
                        } catch (Throwable $exception) {
                            $output->writeln(sprintf('<error>%s</error>', $exception->getMessage()));
                        }

                        break;
                    case 'workflow: kill':
                        $output->writeln('<info>try to KILL workflow instance...</info>');
                        $question = new Question('Please enter running workflow id: ');
                        $rawId = $helper->ask($input, $output, $question);

                        try {
                            $result = $sb->getBizProcScope()->workflow()->kill($rawId);
                            $output->writeln(sprintf('kill result: %s', $result->isSuccess() ? 'succes' : 'failure'));
                        } catch (Throwable $exception) {
                            $output->writeln(sprintf('<error>%s</error>', $exception->getMessage()));
                        }

                        break;
                    case 'workflow task: list':
                        $output->writeln('<info>get workflow task list...</info>');

                        $table = new Table($output);
                        $table->setHeaders([
                            'ID',
                            'WORKFLOW_ID',
                            'DOCUMENT_NAME',
                            'DOCUMENT_ID',
                            'DOCUMENT_URL',
                            'WORKFLOW_STATE',
                            'NAME',
                            'STATUS',
                        ]);
                        foreach ($sb->getBizProcScope()->task()->list([], [], [
                            'ID',
                            'WORKFLOW_ID',
                            'DOCUMENT_NAME',
                            'DOCUMENT_ID',
                            'DOCUMENT_URL',
                            'NAME',
                            'WORKFLOW_STATE',
                            'STATUS'
                        ])->getTasks() as $item) {
                            $table->addRow(
                                [
                                    $item->ID,
                                    $item->WORKFLOW_ID,
                                    $item->DOCUMENT_NAME,
                                    $item->DOCUMENT_ID,
                                    $item->DOCUMENT_URL,
                                    $item->WORKFLOW_STATE,
                                    $item->NAME,
                                    $item->STATUS->name,
                                ]
                            );
                        }

                        $table->render();

                        break;
                    case 'workflow task: complete':
                        $output->writeln('<info>Try to workflow task complete...</info>');

                        $question = new Question('Please enter TASK id: ');
                        $rawId = $helper->ask($input, $output, $question);
                        $taskId = (int)$rawId;

                        try {
                            $result = $sb->getBizProcScope()->task()->complete(
                                $taskId,
                                WorkflowTaskCompleteStatusType::approved,
                                'approved from cli',
                            );
                            $output->writeln(sprintf('task complete status %s', $result->isSuccess() ? 'success' : 'failure'));
                        } catch (Throwable $exception) {
                            $output->writeln(sprintf('<error>%s</error>', $exception->getMessage()));
                        }

                        break;
                    case 'exit🚪':
                        Application::getLog()->debug('cliCommand.finish');
                        return Command::SUCCESS;
                }
            }
        } catch (Throwable $throwable) {
            Application::getLog()->debug('cliCommand.error', [
                'message' => $throwable->getMessage(),
                'trace' => $throwable->getTraceAsString()
            ]);
            $output->writeln(sprintf('<error>ERROR: %s</error>', $throwable->getMessage()));
            $output->writeln(sprintf('<info>DETAILS: %s</info>', $throwable->getTraceAsString()));

            return Command::FAILURE;
        }
    })
    ->run();