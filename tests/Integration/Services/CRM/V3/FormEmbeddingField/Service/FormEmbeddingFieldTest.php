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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\V3\FormEmbeddingField\Service;

use Bitrix24\SDK\Services\CRM\V3\FormEmbeddingField\Service\FormEmbeddingField;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(FormEmbeddingField::class)]
class FormEmbeddingFieldTest extends TestCase
{
    private FormEmbeddingField $embeddingField;

    #[\Override]
    protected function setUp(): void
    {
        $this->embeddingField = Factory::getServiceBuilder()->getCRMScope()->v3()->formEmbeddingField();
    }

    #[Test]
    public function testListReturnsEmbeddingFields(): void
    {
        $names = array_map(static fn ($field): string => $field->name, $this->embeddingField->list()->getFields());

        foreach (['formActive', 'resourcesReady', 'publicUrl', 'inline', 'click', 'auto'] as $name) {
            $this->assertContains($name, $names);
        }
    }

    #[Test]
    public function testOnlyClickAndAutoAreEditable(): void
    {
        $this->assertSame(['update'], $this->embeddingField->get('click')->field()->editableGroups);
        $this->assertSame(['update'], $this->embeddingField->get('auto')->field()->editableGroups);
        $this->assertFalse($this->embeddingField->get('inline')->field()->editable);
    }
}
