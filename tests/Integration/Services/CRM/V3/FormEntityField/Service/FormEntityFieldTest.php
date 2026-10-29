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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\V3\FormEntityField\Service;

use Bitrix24\SDK\Services\CRM\V3\FormEntityField\Service\FormEntityField;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(FormEntityField::class)]
class FormEntityFieldTest extends TestCase
{
    #[Test]
    public function testListReturnsFieldsOfRequestedEntities(): void
    {
        $fields = Factory::getServiceBuilder()->getCRMScope()->v3()->formEntityField()->list(['LEAD', 'CONTACT'])->getEntityFields();

        $this->assertNotEmpty($fields);
        $entityNames = array_unique(array_map(static fn ($field): ?string => $field->entityName, $fields));
        sort($entityNames);
        $this->assertSame(['CONTACT', 'LEAD'], $entityNames);
        $this->assertContains('LEAD_NAME', array_map(static fn ($field): ?string => $field->name, $fields));
    }
}
