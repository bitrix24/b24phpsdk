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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\V3\FormOpenLine\Result;

use Bitrix24\SDK\Services\CRM\V3\CRMV3ServiceBuilder;
use Bitrix24\SDK\Services\CRM\V3\FormOpenLine\Result\OpenLineItemResult;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use Bitrix24\SDK\Tests\Integration\Services\CRM\V3\NestedItemAnnotationsTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(OpenLineItemResult::class)]
class OpenLineItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;
    use NestedItemAnnotationsTrait;

    private CRMV3ServiceBuilder $crm;

    #[\Override]
    protected function setUp(): void
    {
        $this->crm = Factory::getServiceBuilder()->getCRMScope()->v3();
    }

    #[Test]
    public function testAllSystemFieldsAnnotated(): void
    {
        $fields = $this->crm->formOpenLineField()->list()->getFieldsDescription();
        self::assertNotEmpty($fields);
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($fields), OpenLineItemResult::class);
    }

    #[Test]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $fields = $this->crm->formOpenLineField()->list()->getFieldsDescription();
        self::assertNotEmpty($fields);
        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation($fields, OpenLineItemResult::class);
    }

    #[Test]
    public function testAllSystemFieldsHasValidTypeCasting(): void
    {
        $formId = $this->crm->form()->list()->getForms()[0]->id;
        $result = $this->crm->formOpenLine()->list($formId);
        $this->assertNestedItems($result->getCoreResponse()->getResponseData()->getResult()['items'], $result->getOpenLines(), OpenLineItemResult::class);
    }
}
