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

namespace Bitrix24\SDK\Tests\Integration\Services\IM\Disk\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\IM\Disk\Service\Disk;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Disk::class)]
class DiskTest extends TestCase
{
    private Disk $diskService;

    #[\Override]
    protected function setUp(): void
    {
        $this->diskService = Factory::getServiceBuilder()->getIMScope()->disk();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetFolderId(): void
    {
        $userId = (int)$this->diskService->core->call('PROFILE')->getResponseData()->getResult()['ID'];
        $chatId = (int)$this->diskService->core->call(
            'im.chat.add',
            [
                'USERS' => [$userId],
                'TYPE' => 'CHAT',
                'TITLE' => sprintf('IM Disk Test %s', time()),
            ]
        )->getResponseData()->getResult()[0];

        try {
            $folderId = $this->diskService->getFolderId(dialogId: 'chat' . $chatId)->getId();

            $this->assertGreaterThan(0, $folderId);
        } finally {
            $this->diskService->core->call('im.chat.leave', ['CHAT_ID' => $chatId]);
        }
    }
}
