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

namespace Bitrix24\SDK\Tests\Integration\Services\HumanResources\Result;

use Bitrix24\SDK\Services\HumanResources\NodeField\Result\NodeFieldItemResult;
use Bitrix24\SDK\Services\HumanResources\NodeField\Service\NodeField;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversClass(NodeFieldItemResult::class)]
class NodeFieldItemResultAnnotationsTest extends AbstractHumanResourcesAnnotations
{
    private NodeField $nodeFieldService;

    #[\Override]
    protected function setUp(): void
    {
        $this->nodeFieldService = Factory::getServiceBuilder()->getHumanResourcesScope()->nodeField();
    }

    #[Test]
    #[TestDox('all system fields in NodeFieldItemResult are annotated')]
    public function testAllSystemFieldsAnnotated(): void
    {
        $rawItem = $this->callOrSkipIfHumanResourcesUnavailable(fn(): array => $this->getRawFieldItem());

        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($rawItem), NodeFieldItemResult::class);
    }

    #[Test]
    #[TestDox('all system fields in NodeFieldItemResult have valid type annotations')]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $fieldItem = $this->callOrSkipIfHumanResourcesUnavailable(
            fn(): NodeFieldItemResult => $this->nodeFieldService->list()->getNodeFields()[0]
        );

        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($fieldItem, NodeFieldItemResult::class);
    }

    /**
     * @return array<string, mixed>
     */
    private function getRawFieldItem(): array
    {
        $result = $this->nodeFieldService->list()->getCoreResponse()->getResponseData()->getResult();
        $items = $result['items'] ?? $result;

        return $items[0];
    }
}
