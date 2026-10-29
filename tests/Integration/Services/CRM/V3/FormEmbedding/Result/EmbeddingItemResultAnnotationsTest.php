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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\V3\FormEmbedding\Result;

use Bitrix24\SDK\Services\CRM\V3\CRMV3ServiceBuilder;
use Bitrix24\SDK\Services\CRM\V3\FormEmbedding\Result\EmbeddingItemResult;
use Bitrix24\SDK\Services\CRM\V3\FormEmbedding\Result\EmbeddingVariantCodeItemResult;
use Bitrix24\SDK\Services\CRM\V3\FormEmbedding\Result\EmbeddingVariantItemResult;
use Bitrix24\SDK\Services\CRM\V3\FormEmbedding\Service\EmbeddingVariant;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use Bitrix24\SDK\Tests\Integration\Services\CRM\V3\DisposableFormTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(EmbeddingItemResult::class)]
#[CoversClass(EmbeddingVariantItemResult::class)]
#[CoversClass(EmbeddingVariantCodeItemResult::class)]
class EmbeddingItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;
    use DisposableFormTrait;

    private CRMV3ServiceBuilder $crm;

    #[\Override]
    protected function setUp(): void
    {
        $this->crm = Factory::getServiceBuilder()->getCRMScope()->v3();
    }

    #[\Override]
    protected function tearDown(): void
    {
        $this->deleteDisposableForms();
    }

    #[Test]
    public function testAllSystemFieldsAnnotated(): void
    {
        $fields = $this->crm->formEmbeddingField()->list()->getFieldsDescription();
        self::assertNotEmpty($fields);
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($fields), EmbeddingItemResult::class);
    }

    #[Test]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $fields = $this->crm->formEmbeddingField()->list()->getFieldsDescription();
        self::assertNotEmpty($fields);
        // the placement variants are typed EmbeddingVariantItemResult objects; their keys are checked below
        foreach (['inline', 'click', 'auto'] as $variant) {
            self::assertSame('object', $fields[$variant]['type']);
            unset($fields[$variant]);
        }

        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation($fields, EmbeddingItemResult::class);
    }

    #[Test]
    public function testAllSystemFieldsHasValidTypeCasting(): void
    {
        $id = $this->createDisposableForm();
        $result = $this->crm->formEmbedding()->get($id);
        $raw = $result->getCoreResponse()->getResponseData()->getResult()['item'];
        $embedding = $result->embedding();

        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($embedding, EmbeddingItemResult::class);
        foreach (['inline', 'click', 'auto'] as $variant) {
            $this->assertInstanceOf(EmbeddingVariantItemResult::class, $embedding->$variant);
            $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($raw[$variant]), EmbeddingVariantItemResult::class);
            $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($embedding->$variant, EmbeddingVariantItemResult::class);
        }

        // code variants return {variant, code}, the link variant returns {variant, url}: the item covers both shapes
        $variantKeys = [];
        foreach ([EmbeddingVariant::click, EmbeddingVariant::link] as $variant) {
            $variantResult = $this->crm->formEmbedding()->getVariant($id, $variant);
            $variantKeys = [...$variantKeys, ...array_keys($variantResult->getCoreResponse()->getResponseData()->getResult()['item'])];
            $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($variantResult->variant(), EmbeddingVariantCodeItemResult::class);
        }

        $this->assertBitrix24AllResultItemFieldsAnnotated(array_values(array_unique($variantKeys)), EmbeddingVariantCodeItemResult::class);
    }
}
