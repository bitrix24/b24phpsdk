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

use Bitrix24\SDK\Services\HumanResources\NodeField\Service\NodeField;
use Bitrix24\SDK\Services\HumanResources\Result\NodeItemResult;
use Bitrix24\SDK\Services\HumanResources\Service\Node;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversClass(NodeItemResult::class)]
class NodeItemResultAnnotationsTest extends AbstractHumanResourcesAnnotations
{
    private Node $nodeService;

    private NodeField $nodeFieldService;

    #[\Override]
    protected function setUp(): void
    {
        $humanResourcesScope = Factory::getServiceBuilder()->getHumanResourcesScope();
        $this->nodeService = $humanResourcesScope->node();
        $this->nodeFieldService = $humanResourcesScope->nodeField();
    }

    #[Test]
    #[TestDox('all system fields in NodeItemResult are annotated')]
    public function testAllSystemFieldsAnnotated(): void
    {
        $fields = $this->callOrSkipIfHumanResourcesUnavailable(
            fn(): array => $this->nodeFieldService->list()->getFieldsDescription()
        );

        $this->assertHumanResourcesFieldsAnnotated($fields, NodeItemResult::class);

        $rawItem = $this->callOrSkipIfHumanResourcesUnavailable(fn(): array => $this->getSampleNodeRawItem());
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($rawItem), NodeItemResult::class);
    }

    #[Test]
    #[TestDox('all system fields in NodeItemResult have valid type annotations')]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $fields = $this->callOrSkipIfHumanResourcesUnavailable(
            fn(): array => $this->nodeFieldService->list()->getFieldsDescription()
        );

        $this->assertHumanResourcesFieldsHaveValidTypeAnnotations($fields, NodeItemResult::class);

        $nodeItemResult = $this->callOrSkipIfHumanResourcesUnavailable(fn(): NodeItemResult => $this->getSampleNodeItem());
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($nodeItemResult, NodeItemResult::class);
    }

    /**
     * @return array<string, mixed>
     */
    private function getSampleNodeRawItem(): array
    {
        $nodeResult = $this->nodeService->get(
            $this->getSampleNodeId(),
            array_keys($this->nodeFieldService->list()->getFieldsDescription())
        );

        return $nodeResult->getCoreResponse()->getResponseData()->getResult()['item'] ?? [];
    }

    private function getSampleNodeItem(): NodeItemResult
    {
        return $this->nodeService->get(
            $this->getSampleNodeId(),
            array_keys($this->nodeFieldService->list()->getFieldsDescription())
        )->getNode();
    }

    private function getSampleNodeId(): int
    {
        $nodes = $this->nodeService->list('DEPARTMENT', ['id'], ['limit' => 1])->getNodes();
        if ($nodes === []) {
            self::markTestSkipped('No humanresources nodes available to validate annotations.');
        }

        return (int)$nodes[0]->id;
    }
}
