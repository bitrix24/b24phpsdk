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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\PriceTypeLang\Result;

use Bitrix24\SDK\Services\Catalog\PriceTypeLang\Result\LanguageItemResult;
use Bitrix24\SDK\Services\Catalog\PriceTypeLang\Service\PriceTypeLang;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(LanguageItemResult::class)]
class LanguageItemResultTest extends TestCase
{
    use CustomBitrix24Assertions;

    private PriceTypeLang $priceTypeLangService;

    #[\Override]
    protected function setUp(): void
    {
        $this->priceTypeLangService = Fabric::getServiceBuilder()->getCatalogScope()->priceTypeLang();
    }

    #[Test]
    #[TestDox('all fields in LanguageItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllFieldsAreAnnotated(): void
    {
        $rawItems = $this->priceTypeLangService->getLanguages()->getCoreResponse()->getResponseData()->getResult()['languages'];
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($rawItems[0]), LanguageItemResult::class);
    }

    #[Test]
    #[TestDox('all fields in LanguageItemResult have valid type casting in magic getters')]
    public function testAllFieldsHasValidTypeCastingInMagicGetters(): void
    {
        $languages = $this->priceTypeLangService->getLanguages()->getLanguages();
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($languages[0], LanguageItemResult::class);
    }
}
