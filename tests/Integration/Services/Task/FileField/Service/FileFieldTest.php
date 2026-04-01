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

namespace Bitrix24\SDK\Tests\Integration\Services\Task\FileField\Service;

use Bitrix24\SDK\Services\Task\FileField\Result\FileFieldItemResult;
use Bitrix24\SDK\Services\Task\FileField\Service\FileField;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(FileField::class)]
class FileFieldTest extends TestCase
{
    use CustomBitrix24Assertions;

    private FileField $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->service = Factory::getServiceBuilder()->getTaskScope()->taskFileField();
    }

    #[Test]
    public function testList(): void
    {
        $fields = $this->service->list()->getFileFields();
        $this->assertIsArray($fields);
        $this->assertNotEmpty($fields);
    }

    #[Test]
    public function testGet(): void
    {
        $fileFieldItemResult = $this->service->get('id')->fileField();
        $this->assertNotEmpty($fileFieldItemResult->name);
        $this->assertNotEmpty($fileFieldItemResult->type);
        $this->assertNotEmpty($fileFieldItemResult->title);
    }

    #[Test]
    public function testAllFieldsAnnotated(): void
    {
        $rawItems = $this->service->list()->getCoreResponse()->getResponseData()->getResult()['items'];
        $this->assertNotEmpty($rawItems);
        $fieldCodesFromApi = array_keys($rawItems[0]);
        $this->assertBitrix24AllResultItemFieldsAnnotated($fieldCodesFromApi, FileFieldItemResult::class);
    }
}
