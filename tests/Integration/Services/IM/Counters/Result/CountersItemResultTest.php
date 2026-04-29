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

namespace Bitrix24\SDK\Tests\Integration\Services\IM\Counters\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\IM\Counters\Result\CountersItemResult;
use Bitrix24\SDK\Services\IM\Counters\Service\Counters;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(CountersItemResult::class)]
class CountersItemResultTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Counters $countersService;

    #[\Override]
    protected function setUp(): void
    {
        $this->countersService = Factory::getServiceBuilder()->getIMScope()->counters();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('all fields in CountersItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllFieldsAreAnnotated(): void
    {
        $rawItem = $this->countersService->get()
            ->getCoreResponse()
            ->getResponseData()
            ->getResult();

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            array_keys($rawItem),
            CountersItemResult::class
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('all fields in CountersItemResult have valid type casting in magic getters')]
    public function testAllFieldsHasValidTypeCastingInMagicGetters(): void
    {
        $countersItemResult = $this->countersService->get()->counters();

        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations(
            $countersItemResult,
            CountersItemResult::class
        );
    }
}
