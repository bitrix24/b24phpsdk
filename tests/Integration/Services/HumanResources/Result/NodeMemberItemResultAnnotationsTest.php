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

use Bitrix24\SDK\Services\HumanResources\NodeMemberField\Service\NodeMemberField;
use Bitrix24\SDK\Services\HumanResources\Result\NodeItemResult;
use Bitrix24\SDK\Services\HumanResources\Result\NodeMemberItemResult;
use Bitrix24\SDK\Services\HumanResources\Service\Node;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversClass(NodeMemberItemResult::class)]
class NodeMemberItemResultAnnotationsTest extends AbstractHumanResourcesAnnotations
{
    private Node $nodeService;

    private NodeMemberField $nodeMemberFieldService;

    #[\Override]
    protected function setUp(): void
    {
        $humanResourcesScope = Factory::getServiceBuilder()->getHumanResourcesScope();
        $this->nodeService = $humanResourcesScope->node();
        $this->nodeMemberFieldService = $humanResourcesScope->nodeMemberField();
    }

    #[Test]
    #[TestDox('all system fields in NodeMemberItemResult are annotated')]
    public function testAllSystemFieldsAnnotated(): void
    {
        $fields = $this->callOrSkipIfHumanResourcesUnavailable(
            fn(): array => $this->nodeMemberFieldService->list()->getFieldsDescription()
        );

        $this->assertHumanResourcesFieldsAnnotated($fields, NodeMemberItemResult::class);

        $rawItem = $this->callOrSkipIfHumanResourcesUnavailable(fn(): array => $this->getSampleNodeMemberRawItem());
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($rawItem), NodeMemberItemResult::class);
    }

    #[Test]
    #[TestDox('all system fields in NodeMemberItemResult have valid type annotations')]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $fields = $this->callOrSkipIfHumanResourcesUnavailable(
            fn(): array => $this->nodeMemberFieldService->list()->getFieldsDescription()
        );

        $this->assertHumanResourcesFieldsHaveValidTypeAnnotations($fields, NodeMemberItemResult::class);

        $nodeMemberItemResult = $this->callOrSkipIfHumanResourcesUnavailable(
            fn(): NodeMemberItemResult => new NodeMemberItemResult($this->getSampleNodeMemberRawItem())
        );
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations(
            $nodeMemberItemResult,
            NodeMemberItemResult::class
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function getSampleNodeMemberRawItem(): array
    {
        $node = $this->nodeService->list('DEPARTMENT', ['id'], ['limit' => 10])->getNodes()[0] ?? null;
        if (!$node instanceof NodeItemResult) {
            self::markTestSkipped('No humanresources nodes available to validate node members.');
        }

        $nodeResult = $this->nodeService->get((int)$node->id, ['members']);
        $rawNode = $nodeResult->getCoreResponse()->getResponseData()->getResult()['item'] ?? [];
        $rawMember = $rawNode['members'][0] ?? null;
        if (!is_array($rawMember)) {
            self::markTestSkipped('No humanresources node members available to validate annotations.');
        }

        return $rawMember;
    }
}
