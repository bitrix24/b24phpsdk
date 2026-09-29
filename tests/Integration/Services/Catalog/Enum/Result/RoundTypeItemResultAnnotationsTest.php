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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\Enum\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Catalog\Enum\Result\RoundTypeItemResult;
use Bitrix24\SDK\Services\Catalog\Enum\Service\CatalogEnum;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(RoundTypeItemResult::class)]
class RoundTypeItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    private CatalogEnum $catalogEnumService;

    #[\Override]
    protected function setUp(): void
    {
        $this->catalogEnumService = Fabric::getServiceBuilder()->getCatalogScope()->catalogEnum();
    }

    /**
     * @return array<string, mixed>
     * @throws BaseException
     * @throws TransportException
     */
    private function getFirstRoundTypeRawItem(): array
    {
        $rawItems = $this->catalogEnumService->getRoundTypes()
            ->getCoreResponse()->getResponseData()->getResult()['enum'];

        self::assertNotEmpty($rawItems, 'getRoundTypes() must return at least one item to run this test');

        return $rawItems[0];
    }

    #[Test]
    #[TestDox('all fields in RoundTypeItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllSystemFieldsAnnotated(): void
    {
        $rawItem = $this->getFirstRoundTypeRawItem();

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            array_keys($rawItem),
            RoundTypeItemResult::class
        );
    }
}
