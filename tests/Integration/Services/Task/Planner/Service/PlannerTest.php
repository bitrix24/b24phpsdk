<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Vadim Soluyanov <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Task\Planner\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core;
use Bitrix24\SDK\Services\Task\Planner\Service\Planner;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class PlannerTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Task\Planner\Service
 */
#[CoversMethod(Planner::class,'getList')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\Task\Planner\Service\Planner::class)]
class PlannerTest extends TestCase
{
    use CustomBitrix24Assertions;
    
    protected Planner $plannerService;
    
    protected function setUp(): void
    {
        $this->plannerService = Factory::getServiceBuilder()->getTaskScope()->planner();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetList(): void
    {
        self::assertIsArray($this->plannerService->getList());
    }
}
