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
use Bitrix24\SDK\Services\HumanResources\Result\NodeMemberItemResult;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversClass(NodeMemberItemResult::class)]
class NodeMemberItemResultAnnotationsTest extends AbstractHumanResourcesAnnotations
{
    private NodeMemberField $nodeMemberFieldService;

    #[\Override]
    protected function setUp(): void
    {
        $this->nodeMemberFieldService = Factory::getServiceBuilder()->getHumanResourcesScope()->nodeMemberField();
    }

    #[Test]
    #[TestDox('all system fields in NodeMemberItemResult are annotated')]
    public function testAllSystemFieldsAnnotated(): void
    {
        $fields = $this->callOrSkipIfHumanResourcesUnavailable(
            fn(): array => $this->nodeMemberFieldService->list()->getFieldsDescription()
        );

        $this->assertHumanResourcesFieldsAnnotated($fields, NodeMemberItemResult::class);
    }

    #[Test]
    #[TestDox('all system fields in NodeMemberItemResult have valid type annotations')]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $fields = $this->callOrSkipIfHumanResourcesUnavailable(
            fn(): array => $this->nodeMemberFieldService->list()->getFieldsDescription()
        );

        $this->assertHumanResourcesFieldsHaveValidTypeAnnotations($fields, NodeMemberItemResult::class);
    }
}
