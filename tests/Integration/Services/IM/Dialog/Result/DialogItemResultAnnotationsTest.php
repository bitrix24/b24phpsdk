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
use Bitrix24\SDK\Services\IM\Dialog\Result\DialogItemResult;
use Bitrix24\SDK\Services\IM\Dialog\Service\Dialog;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Services\IM\Dialog\Service\DialogChatTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversClass(DialogItemResult::class)]
#[CoversMethod(Dialog::class, 'get')]
final class DialogItemResultAnnotationsTest extends DialogChatTestCase
{
    use CustomBitrix24Assertions;

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAllSystemFieldsAnnotated(): void
    {
        $dialogItemResult = $this->getDialogItem();

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            array_keys(iterator_to_array($dialogItemResult)),
            DialogItemResult::class
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations(
            $this->getDialogItem(),
            DialogItemResult::class
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    private function getDialogItem(): DialogItemResult
    {
        $dialog = $this->dialogService->get(
            $this->createDialogId($this->createChat())
        )->dialog();

        self::assertInstanceOf(DialogItemResult::class, $dialog);

        return $dialog;
    }
}
