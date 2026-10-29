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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\V3\MeasureField\Result;

use Bitrix24\SDK\Services\Catalog\V3\MeasureField\Result\MeasureFieldItemResult;
use Bitrix24\SDK\Services\Catalog\V3\MeasureField\Service\MeasureField;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(MeasureFieldItemResult::class)]
class MeasureFieldItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    private MeasureField $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->service = Factory::getServiceBuilder()->getCatalogScope()->v3()->measureField();
    }

    #[Test]
    #[TestDox('all fields in MeasureFieldItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllSystemFieldsAnnotated(): void
    {
        $rawItem = $this->service->get('code')->getCoreResponse()->getResponseData()->getResult()['item'];
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($rawItem), MeasureFieldItemResult::class);
    }

    #[Test]
    #[TestDox('all fields in MeasureFieldItemResult have valid type casting in magic getters')]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations(
            $this->service->get('code')->field(),
            MeasureFieldItemResult::class
        );
    }
}
