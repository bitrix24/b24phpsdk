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

namespace Bitrix24\SDK\Tests\Integration\Services\Task\AccessField\Service;

use Bitrix24\SDK\Services\Task\AccessField\Result\AccessFieldItemResult;
use Bitrix24\SDK\Services\Task\AccessField\Service\AccessField;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(AccessField::class)]
class AccessFieldTest extends TestCase
{
    use CustomBitrix24Assertions;

    private AccessField $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->service = Factory::getServiceBuilder()->getTaskScope()->taskAccessField();
    }

    #[Test]
    public function testList(): void
    {
        $fields = $this->service->list()->getAccessFields();
        $this->assertIsArray($fields);
        $this->assertNotEmpty($fields);
    }

    #[Test]
    public function testGet(): void
    {
        $accessFieldItemResult = $this->service->get('id')->accessField();
        $this->assertNotEmpty($accessFieldItemResult->name);
        $this->assertNotEmpty($accessFieldItemResult->type);
        $this->assertNotEmpty($accessFieldItemResult->title);
    }

    #[Test]
    public function testAllFieldsAnnotated(): void
    {
        $rawItems = $this->service->list()->getCoreResponse()->getResponseData()->getResult()['items'];
        $this->assertNotEmpty($rawItems);
        $fieldCodesFromApi = array_keys($rawItems[0]);
        $this->assertBitrix24AllResultItemFieldsAnnotated($fieldCodesFromApi, AccessFieldItemResult::class);
    }
}
