<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\IMBot\Command\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\IMBot\Bot\BotEventMode;
use Bitrix24\SDK\Services\IMBot\Bot\BotType;
use Bitrix24\SDK\Services\IMBot\Bot\Service\Bot;
use Bitrix24\SDK\Services\IMBot\Command\Service\Command;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Command::class)]
class CommandTest extends TestCase
{
    private Command $commandService;

    private Bot $botService;

    private int $botId = 0;

    /**
     * @var list<int>
     */
    private array $registeredCommandIds = [];

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $imBotServiceBuilder = Fabric::getServiceBuilder(true)->getIMBotScope();
        $this->commandService = $imBotServiceBuilder->command();
        $this->botService = $imBotServiceBuilder->bot();

        $code = sprintf('test_cmd_bot_%s', uniqid('', true));
        $botResult = $this->botService->register(
            code: $code,
            properties: ['name' => 'Command Test Bot'],
            type: BotType::bot,
            eventMode: BotEventMode::fetch
        );
        $this->botId = $botResult->bot()->id;
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function tearDown(): void
    {
        foreach ($this->registeredCommandIds as $registeredCommandId) {
            try {
                $this->commandService->unregister($this->botId, $registeredCommandId);
            } catch (BaseException) {
                // command may already be deleted
            }
        }

        try {
            $this->botService->unregister($this->botId);
        } catch (BaseException) {
            // bot may already be deleted
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('imbot.v2.Command.register registers a slash command and returns CommandItemResult')]
    public function testRegister(): void
    {
        $commandName = sprintf('cmd_%s', substr(uniqid('', true), 0, 8));

        $commandResult = $this->commandService->register(
            botId: $this->botId,
            command: $commandName,
            title: ['en' => 'Test command'],
            hidden: true
        );

        $command = $commandResult->command();
        $this->registeredCommandIds[] = $command->id;

        $this->assertGreaterThan(0, $command->id);
        $this->assertSame($this->botId, $command->botId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('imbot.v2.Command.list returns list of registered commands')]
    public function testList(): void
    {
        $commandName = sprintf('cmd_%s', substr(uniqid('', true), 0, 8));
        $commandId = $this->commandService->register(
            botId: $this->botId,
            command: $commandName,
            title: ['en' => 'Test command'],
            hidden: true
        )->command()->id;
        $this->registeredCommandIds[] = $commandId;

        $commandsResult = $this->commandService->list($this->botId);

        $this->assertNotEmpty($commandsResult->commands());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('imbot.v2.Command.unregister removes the command')]
    public function testUnregister(): void
    {
        $commandName = sprintf('cmd_%s', substr(uniqid('', true), 0, 8));
        $commandId = $this->commandService->register(
            botId: $this->botId,
            command: $commandName,
            title: ['en' => 'Test command'],
            hidden: true
        )->command()->id;

        $emptyResult = $this->commandService->unregister($this->botId, $commandId);

        // EmptyResult — no exception means success
        $this->assertNotNull($emptyResult->getCoreResponse());
    }
}
