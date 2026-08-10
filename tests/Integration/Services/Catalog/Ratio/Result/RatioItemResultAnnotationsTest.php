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
use Bitrix24\SDK\Core\Fields\FieldsFilter;
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
     * catalog.ratio.getFields wraps the actual field descriptions under a «ratio» key,
     * unlike most other bitrix24 *.getFields methods that return a flat field map.
     *
     * @return array<string, array<string, mixed>>
     * @throws BaseException
     * @throws TransportException
     */
    private function getRatioFieldsDescription(): array
    {
        return $this->ratioService->fields()->getFieldsDescription()['ratio'];
    }

    #[Test]
    #[TestDox('all fields in RatioItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllSystemFieldsAnnotated(): void
    {
        $propListFromApi = (new FieldsFilter())->filterSystemFields(array_keys($this->getRatioFieldsDescription()));

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            $propListFromApi,
            RatioItemResult::class
        );
    }

    #[Test]
    #[TestDox('all fields in RatioItemResult have valid type casting in magic getters')]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $allFields = $this->getRatioFieldsDescription();
        $systemFieldsCodes = (new FieldsFilter())->filterSystemFields(array_keys($allFields));
        $systemFields = array_filter($allFields, static fn ($code): bool => in_array($code, $systemFieldsCodes, true), ARRAY_FILTER_USE_KEY);

        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $systemFields,
            RatioItemResult::class
        );
    }
}
