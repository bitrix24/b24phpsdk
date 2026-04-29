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

namespace Bitrix24\SDK\Tests\Integration\Services\IM\User\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\IM\User\Result\UserItemResult;
use Bitrix24\SDK\Services\IM\User\Service\User;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(UserItemResult::class)]
final class UserItemResultTest extends TestCase
{
    use CustomBitrix24Assertions;

    private User $userService;

    #[\Override]
    protected function setUp(): void
    {
        $this->userService = Factory::getServiceBuilder()->getIMScope()->user();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('all fields in UserItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllSystemFieldsAnnotated(): void
    {
        $rawResult = $this->userService->get()->getCoreResponse()
            ->getResponseData()->getResult();

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            array_keys($rawResult),
            UserItemResult::class
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('all fields in UserItemResult have valid type casting in magic getters')]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $userItemResult = $this->userService->get()->user();
        $this->assertInstanceOf(UserItemResult::class, $userItemResult);

        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations(
            $userItemResult,
            UserItemResult::class
        );
    }
}
