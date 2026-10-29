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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\V3\FormScenarioField\Result;

use Bitrix24\SDK\Services\CRM\V3\FormScenarioField\Result\FormScenarioFieldItemResult;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(FormScenarioFieldItemResult::class)]
class FormScenarioFieldItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    #[Test]
    public function testAllSystemFieldsAnnotated(): void
    {
        $raw = Factory::getServiceBuilder()->getCRMScope()->v3()->formScenarioField()->get('id')->getCoreResponse()->getResponseData()->getResult()['item'];
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($raw), FormScenarioFieldItemResult::class);
    }

    #[Test]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations(
            Factory::getServiceBuilder()->getCRMScope()->v3()->formScenarioField()->get('id')->field(),
            FormScenarioFieldItemResult::class
        );
    }
}
