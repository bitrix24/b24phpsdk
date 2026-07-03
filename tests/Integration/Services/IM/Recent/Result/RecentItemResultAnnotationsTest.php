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

namespace Bitrix24\SDK\Tests\Integration\Services\IM\Recent\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\IM\Recent\Result\RecentItemResult;
use Bitrix24\SDK\Services\IM\Recent\Service\Recent;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(RecentItemResult::class)]
class RecentItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Recent $recentService;

    #[\Override]
    protected function setUp(): void
    {
        $this->recentService = Factory::getServiceBuilder()->getIMScope()->recent();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('all fields in RecentItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllSystemFieldsAnnotated(): void
    {
        $rawResult = $this->recentService->get()
            ->getCoreResponse()
            ->getResponseData()
            ->getResult();

        if ($rawResult === []) {
            $this->markTestSkipped('No recent items available to validate annotations');
        }

        $firstItem = reset($rawResult);
        if (!is_array($firstItem)) {
            $this->markTestSkipped('Unexpected response shape for im.recent.get');
        }

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            array_keys($firstItem),
            RecentItemResult::class
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('all fields in RecentItemResult have valid type casting in magic getters')]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $items = $this->recentService->get()->items();

        if ($items === []) {
            $this->markTestSkipped('No recent items available to validate type casting');
        }

        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations(
            $items[0],
            RecentItemResult::class
        );
    }
}
