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

namespace Bitrix24\SDK\Tests\Integration\Services\Task\AccessField\Result;

use Bitrix24\SDK\Services\Task\AccessField\Result\AccessFieldItemResult;
use Bitrix24\SDK\Services\Task\AccessField\Service\AccessField;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(AccessFieldItemResult::class)]
class AccessFieldItemResultTest extends TestCase
{
    use CustomBitrix24Assertions;

    private AccessField $accessFieldService;

    #[\Override]
    protected function setUp(): void
    {
        $this->accessFieldService = Factory::getServiceBuilder()->getTaskScope()->taskAccessField();
    }

    #[Test]
    #[TestDox('all fields in AccessFieldItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllFieldsAreAnnotated(): void
    {
        $rawItem = $this->accessFieldService->get('id')->getCoreResponse()
            ->getResponseData()->getResult()['item'];

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            array_keys($rawItem),
            AccessFieldItemResult::class
        );
    }

    #[Test]
    #[TestDox('all fields in AccessFieldItemResult have valid type casting in magic getters')]
    public function testAllFieldsHasValidTypeCastingInMagicGetters(): void
    {
        $accessFieldItemResult = $this->accessFieldService->get('id')->accessField();
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations(
            $accessFieldItemResult,
            AccessFieldItemResult::class
        );
    }
}
