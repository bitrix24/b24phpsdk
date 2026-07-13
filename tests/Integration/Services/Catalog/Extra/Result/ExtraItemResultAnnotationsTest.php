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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\Extra\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Catalog\Extra\Result\ExtraItemResult;
use Bitrix24\SDK\Services\Catalog\Extra\Service\Extra;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(ExtraItemResult::class)]
class ExtraItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Extra $extraService;

    #[\Override]
    protected function setUp(): void
    {
        $this->extraService = Fabric::getServiceBuilder()->getCatalogScope()->extra();
    }

    /**
     * catalog.extra has no REST method to create a markup — markups are portal-configured.
     * If the portal has none, this test is skipped as there is no way to fabricate one via REST.
     *
     * @return array<string, mixed>
     * @throws BaseException
     * @throws TransportException
     */
    private function getFirstExtraRawItem(): array
    {
        $rawItems = $this->extraService->list()
            ->getCoreResponse()->getResponseData()->getResult()['extras'];

        if ($rawItems === []) {
            $this->markTestSkipped('portal has no markups (catalog.extra) configured to test annotations against');
        }

        return $rawItems[0];
    }

    #[Test]
    #[TestDox('all fields in ExtraItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllSystemFieldsAnnotated(): void
    {
        $rawItem = $this->getFirstExtraRawItem();

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            array_keys($rawItem),
            ExtraItemResult::class
        );
    }

    #[Test]
    #[TestDox('all fields in ExtraItemResult have valid type casting in magic getters')]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $rawItem = $this->getFirstExtraRawItem();
        $extraItemResult = new ExtraItemResult($rawItem);

        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations(
            $extraItemResult,
            ExtraItemResult::class
        );
    }
}
