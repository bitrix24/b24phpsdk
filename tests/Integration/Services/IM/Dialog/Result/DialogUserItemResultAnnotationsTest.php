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

namespace Bitrix24\SDK\Tests\Integration\Services\IM\Dialog\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\IM\Dialog\Result\DialogUserItemResult;
use Bitrix24\SDK\Services\IM\Dialog\Service\Dialog;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Services\IM\Dialog\Service\DialogChatTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversClass(DialogUserItemResult::class)]
#[CoversMethod(Dialog::class, 'usersList')]
final class DialogUserItemResultAnnotationsTest extends DialogChatTestCase
{
    use CustomBitrix24Assertions;

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAllSystemFieldsAnnotated(): void
    {
        $dialogUserItemResult = $this->getDialogUserItem();

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            array_keys(iterator_to_array($dialogUserItemResult)),
            DialogUserItemResult::class
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations(
            $this->getDialogUserItem(),
            DialogUserItemResult::class
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    private function getDialogUserItem(): DialogUserItemResult
    {
        $user = $this->dialogService->usersList(
            $this->createDialogId($this->createChat()),
            limit: 50
        )->users()[0] ?? null;

        self::assertInstanceOf(DialogUserItemResult::class, $user);

        return $user;
    }
}
