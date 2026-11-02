<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Category\Service;

use Bitrix24\SDK\Services\CRM\Type\Service\Type;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Type::class)]
class TypeDeletionTest extends TestCase
{
    public function testEmptyTypeIsActuallyRemovedByInternalId(): void
    {
        $types = Factory::getServiceBuilder()->getCRMScope()->type();
        $type = $types->add('SDK deletion test ' . uniqid())->type();
        try {
            self::assertTrue($types->delete($type->id)->isSuccess());
            self::assertSame([], $types->list(filter: ['id' => $type->id])->getTypes());
        } finally {
            if ($types->list(filter: ['id' => $type->id])->getTypes() !== []) {
                $types->delete($type->id)->isSuccess();
            }
        }
    }
}
