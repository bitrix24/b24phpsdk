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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\Ratio\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Catalog\Ratio\Result\RatioItemResult;
use Bitrix24\SDK\Services\Catalog\Ratio\Service\Ratio;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(RatioItemResult::class)]
class RatioItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Ratio $ratioService;

    #[\Override]
    protected function setUp(): void
    {
        $this->ratioService = Fabric::getServiceBuilder()->getCatalogScope()->ratio();
    }

    /**
     * catalog.ratio has no REST method to create a ratio — ratios are created implicitly when a
     * product's measurement unit ratio is configured.
     * If the portal has none, this test is skipped as there is no way to fabricate one via REST.
     *
     * @return array<string, mixed>
     * @throws BaseException
     * @throws TransportException
     */
    private function getFirstRatioRawItem(): array
    {
        $rawItems = $this->ratioService->list()
            ->getCoreResponse()->getResponseData()->getResult()['ratios'];

        if ($rawItems === []) {
            $this->markTestSkipped('portal has no catalog ratios (catalog.ratio) configured to test annotations against');
        }

        return $rawItems[0];
    }

    #[Test]
    #[TestDox('all fields in RatioItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllSystemFieldsAnnotated(): void
    {
        $rawItem = $this->getFirstRatioRawItem();

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            array_keys($rawItem),
            RatioItemResult::class
        );
    }

    #[Test]
    #[TestDox('all fields in RatioItemResult have valid type casting in magic getters')]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $rawItem = $this->getFirstRatioRawItem();
        $ratioItemResult = new RatioItemResult($rawItem);

        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations(
            $ratioItemResult,
            RatioItemResult::class
        );
    }
}
