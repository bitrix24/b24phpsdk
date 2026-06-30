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

use Bitrix24\SDK\Services\HumanResources\NodeMemberField\Result\NodeMemberFieldItemResult;
use Bitrix24\SDK\Services\HumanResources\NodeMemberField\Service\NodeMemberField;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversClass(NodeMemberFieldItemResult::class)]
class NodeMemberFieldItemResultAnnotationsTest extends AbstractHumanResourcesAnnotations
{
    private NodeMemberField $nodeMemberFieldService;

    #[\Override]
    protected function setUp(): void
    {
        $this->nodeMemberFieldService = Factory::getServiceBuilder()->getHumanResourcesScope()->nodeMemberField();
    }

    #[Test]
    #[TestDox('all system fields in NodeMemberFieldItemResult are annotated')]
    public function testAllSystemFieldsAnnotated(): void
    {
        $rawItem = $this->callOrSkipIfHumanResourcesUnavailable(fn(): array => $this->getRawFieldItem());

        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($rawItem), NodeMemberFieldItemResult::class);
    }

    #[Test]
    #[TestDox('all system fields in NodeMemberFieldItemResult have valid type annotations')]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $fieldItem = $this->callOrSkipIfHumanResourcesUnavailable(
            fn(): NodeMemberFieldItemResult => $this->nodeMemberFieldService->list()->getNodeMemberFields()[0]
        );

        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($fieldItem, NodeMemberFieldItemResult::class);
    }

    /**
     * @return array<string, mixed>
     */
    private function getRawFieldItem(): array
    {
        $result = $this->nodeMemberFieldService->list()->getCoreResponse()->getResponseData()->getResult();
        $items = $result['items'] ?? $result;

        return $items[0];
    }
}
