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

namespace Bitrix24\SDK\Tests\Integration\Services\Task\FileField\Result;

use Bitrix24\SDK\Services\Task\FileField\Result\FileFieldItemResult;
use Bitrix24\SDK\Services\Task\FileField\Service\FileField;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(FileFieldItemResult::class)]
class FileFieldItemResultTest extends TestCase
{
    use CustomBitrix24Assertions;

    private FileField $fileFieldService;

    #[\Override]
    protected function setUp(): void
    {
        $this->fileFieldService = Factory::getServiceBuilder()->getTaskScope()->taskFileField();
    }

    #[Test]
    #[TestDox('all fields in FileFieldItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllFieldsAreAnnotated(): void
    {
        $rawItem = $this->fileFieldService->get('id')->getCoreResponse()
            ->getResponseData()->getResult()['item'];

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            array_keys($rawItem),
            FileFieldItemResult::class
        );
    }

    #[Test]
    #[TestDox('all fields in FileFieldItemResult have valid type casting in magic getters')]
    public function testAllFieldsHasValidTypeCastingInMagicGetters(): void
    {
        $fileFieldItemResult = $this->fileFieldService->get('id')->fileField();
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations(
            $fileFieldItemResult,
            FileFieldItemResult::class
        );
    }
}
