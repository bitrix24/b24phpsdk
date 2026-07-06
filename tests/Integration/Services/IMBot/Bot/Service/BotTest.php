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

namespace Bitrix24\SDK\Tests\Integration\Services\IMBot\Bot\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\IMBot\Bot\BotEventMode;
use Bitrix24\SDK\Services\IMBot\Bot\BotType;
use Bitrix24\SDK\Services\IMBot\Bot\Service\Bot;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Bot::class)]
class BotTest extends TestCase
{
    private Bot $botService;

    /**
     * @var list<int>
     */
    private array $registeredBotIds = [];

    #[\Override]
    protected function setUp(): void
    {
        $this->botService = Fabric::getServiceBuilder(true)->getIMBotScope()->bot();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function tearDown(): void
    {
        foreach ($this->registeredBotIds as $registeredBotId) {
            try {
                $this->botService->unregister($registeredBotId);
            } catch (BaseException) {
                // bot may already be deleted by the test
            }
        }

        $this->registeredBotIds = [];
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('imbot.v2.Bot.register creates a new bot and returns valid BotItemResult')]
    public function testRegister(): void
    {
        $code = sprintf('test_bot_%s', uniqid('', true));

        $botResult = $this->botService->register(
            code: $code,
            properties: ['name' => 'Test Bot'],
            botType: BotType::bot,
            botEventMode: BotEventMode::fetch
        );

        $bot = $botResult->bot();
        $this->registeredBotIds[] = $bot->id;

        $this->assertGreaterThan(0, $bot->id);
        $this->assertSame($code, $bot->code);
        $this->assertSame('bot', $bot->type);
        $this->assertFalse($bot->isHidden);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('imbot.v2.Bot.get returns bot information by code')]
    public function testGet(): void
    {
        $code = sprintf('test_bot_%s', uniqid('', true));

        $botResult = $this->botService->register(
            code: $code,
            properties: ['name' => 'Test Bot'],
        );
        $botId = $botResult->bot()->id;
        $this->registeredBotIds[] = $botId;

        $result = $this->botService->get(code: $code);
        $bot = $result->bot();

        $this->assertSame($botId, $bot->id);
        $this->assertSame($code, $bot->code);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('imbot.v2.Bot.list returns array of bots')]
    public function testList(): void
    {
        $botsResult = $this->botService->list();

        $this->assertIsArray($botsResult->bots());
        $this->assertIsBool($botsResult->hasNextPage());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('imbot.v2.Bot.unregister deletes the bot successfully')]
    public function testUnregister(): void
    {
        $code = sprintf('test_bot_%s', uniqid('', true));

        $botResult = $this->botService->register(
            code: $code,
            properties: ['name' => 'Test Bot'],
        );
        $botId = $botResult->bot()->id;

        $emptyResult = $this->botService->unregister($botId);

        // EmptyResult — no exception means success
        $this->assertNotNull($emptyResult->getCoreResponse());
    }
}
