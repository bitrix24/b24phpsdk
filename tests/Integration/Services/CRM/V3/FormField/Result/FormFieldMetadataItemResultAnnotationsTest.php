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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\V3\FormField\Result;

use Bitrix24\SDK\Services\CRM\V3\FormField\Result\FormFieldMetadataItemResult;
use Bitrix24\SDK\Services\CRM\V3\FormField\Service\FormField;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(FormFieldMetadataItemResult::class)]
class FormFieldMetadataItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    private FormField $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->service = Factory::getServiceBuilder()->getCRMScope()->v3()->formField();
    }

    #[Test]
    #[TestDox('all fields in FormFieldMetadataItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllSystemFieldsAnnotated(): void
    {
        $rawItem = $this->service->get('name')->getCoreResponse()->getResponseData()->getResult()['item'];
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($rawItem), FormFieldMetadataItemResult::class);
    }

    #[Test]
    #[TestDox('all fields in FormFieldMetadataItemResult have valid type casting in magic getters')]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations(
            $this->service->get('name')->field(),
            FormFieldMetadataItemResult::class
        );
    }
}
