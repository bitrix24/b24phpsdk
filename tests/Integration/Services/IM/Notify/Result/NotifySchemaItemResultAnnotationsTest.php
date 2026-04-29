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
use Bitrix24\SDK\Services\IM\Notify\Result\NotifySchemaItemResult;
use Bitrix24\SDK\Services\IM\Notify\Service\Notify;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(NotifySchemaItemResult::class)]
class NotifySchemaItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Notify $notifyService;

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('all fields in NotifySchemaItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllSystemFieldsAnnotated(): void
    {
        $rawResult = $this->notifyService->getSchema()
            ->getCoreResponse()->getResponseData()->getResult();

        $modules = array_filter($rawResult, 'is_array');
        $this->assertNotEmpty($modules, 'Schema returned no modules — cannot verify annotations');

        $rawItem = reset($modules);
        $this->assertBitrix24AllResultItemFieldsAnnotated(
            array_keys($rawItem),
            NotifySchemaItemResult::class
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('all fields in NotifySchemaItemResult have valid type casting in magic getters')]
    public function testAllSystemFieldsHasValidTypeCastingInMagicGetters(): void
    {
        $items = $this->notifyService->getSchema()->schema();
        $this->assertNotEmpty($items, 'Schema returned no modules — cannot verify type casting');

        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations(
            $items[0],
            NotifySchemaItemResult::class
        );
    }

    #[\Override]
    protected function setUp(): void
    {
        $this->notifyService = Factory::getServiceBuilder()->getIMScope()->notify();
    }
}
