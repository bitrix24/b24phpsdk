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
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversClass(NodeItemResult::class)]
class NodeItemResultAnnotationsTest extends AbstractHumanResourcesAnnotations
{
    private NodeField $nodeFieldService;

    #[\Override]
    protected function setUp(): void
    {
        $this->nodeFieldService = Factory::getServiceBuilder()->getHumanResourcesScope()->nodeField();
    }

    #[Test]
    #[TestDox('all system fields in NodeItemResult are annotated')]
    public function testAllSystemFieldsAnnotated(): void
    {
        $fields = $this->callOrSkipIfHumanResourcesUnavailable(
            fn(): array => $this->nodeFieldService->list()->getFieldsDescription()
        );

        $this->assertHumanResourcesFieldsAnnotated($fields, NodeItemResult::class);
    }

    #[Test]
    #[TestDox('all system fields in NodeItemResult have valid type annotations')]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $fields = $this->callOrSkipIfHumanResourcesUnavailable(
            fn(): array => $this->nodeFieldService->list()->getFieldsDescription()
        );

        $this->assertHumanResourcesFieldsHaveValidTypeAnnotations($fields, NodeItemResult::class);
    }
}
