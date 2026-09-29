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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\Vat\Result;

use Bitrix24\SDK\Services\Catalog\Vat\Result\VatItemResult;
use Bitrix24\SDK\Services\Catalog\Vat\Service\Vat;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(VatItemResult::class)]
class VatItemResultTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Vat $vatService;

    private int $vatId;

    #[\Override]
    protected function setUp(): void
    {
        $this->vatService = Fabric::getServiceBuilder()->getCatalogScope()->vat();
        $this->vatId = $this->vatService->add([
            'name' => sprintf('test vat annotations %s', time()),
            'rate' => 13,
            'sort' => 50,
        ])->vat()->id;
    }

    #[\Override]
    protected function tearDown(): void
    {
        $this->vatService->delete($this->vatId);
    }

    #[Test]
    #[TestDox('all fields in VatItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllFieldsAreAnnotated(): void
    {
        $rawItem = $this->vatService->get($this->vatId)->getCoreResponse()->getResponseData()->getResult()['vat'];
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($rawItem), VatItemResult::class);
    }

    #[Test]
    #[TestDox('all fields in VatItemResult have valid type casting in magic getters')]
    public function testAllFieldsHasValidTypeCastingInMagicGetters(): void
    {
        $vatItemResult = $this->vatService->get($this->vatId)->vat();
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($vatItemResult, VatItemResult::class);
    }
}
