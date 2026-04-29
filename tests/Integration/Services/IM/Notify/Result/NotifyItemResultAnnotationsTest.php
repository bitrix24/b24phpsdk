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

namespace Bitrix24\SDK\Tests\Integration\Services\IM\Notify\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\IM\Notify\Result\NotifyItemResult;
use Bitrix24\SDK\Services\IM\Notify\Service\Notify;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(NotifyItemResult::class)]
class NotifyItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Notify $notifyService;

    private int $currentUserId;

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('all fields in NotifyItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllSystemFieldsAnnotated(): void
    {
        $this->notifyService->fromSystem($this->currentUserId, sprintf('Annotation test at %s', time()));

        $rawResult = $this->notifyService->getList(null, null, 1)
            ->getCoreResponse()->getResponseData()->getResult();

        $notifications = $rawResult['notifications'] ?? [];
        $this->assertNotEmpty($notifications, 'No notifications returned — cannot verify annotations');

        $rawItem = $notifications[0];
        $this->assertBitrix24AllResultItemFieldsAnnotated(
            array_keys($rawItem),
            NotifyItemResult::class
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('all fields in NotifyItemResult have valid type casting in magic getters')]
    public function testAllSystemFieldsHasValidTypeCastingInMagicGetters(): void
    {
        $this->notifyService->fromSystem($this->currentUserId, sprintf('Type cast test at %s', time()));

        $items = $this->notifyService->getList(null, null, 1)->notifications();
        $this->assertNotEmpty($items, 'No notifications returned — cannot verify type casting');

        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations(
            $items[0],
            NotifyItemResult::class
        );
    }

    #[\Override]
    protected function setUp(): void
    {
        $this->notifyService = Factory::getServiceBuilder()->getIMScope()->notify();
        $this->currentUserId = (int)$this->notifyService->core->call('PROFILE')
            ->getResponseData()->getResult()['ID'];
    }
}
