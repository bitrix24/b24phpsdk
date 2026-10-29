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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\V3\FormEntityFieldField\Service;

use Bitrix24\SDK\Services\CRM\V3\FormEntityFieldField\Service\FormEntityFieldField;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(FormEntityFieldField::class)]
class FormEntityFieldFieldTest extends TestCase
{
    #[Test]
    public function testListAndGetReturnDescriptors(): void
    {
        $service = Factory::getServiceBuilder()->getCRMScope()->v3()->formEntityFieldField();
        $names = array_map(static fn ($field): string => $field->name, $service->list()->getFields());

        foreach (['name', 'entityName', 'items'] as $name) {
            $this->assertContains($name, $names);
            $this->assertSame($name, $service->get($name)->field()->name);
        }
    }
}
