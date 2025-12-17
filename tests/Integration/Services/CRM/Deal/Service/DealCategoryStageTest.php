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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Deal\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\CRM\Deal\Service\DealCategory;
use Bitrix24\SDK\Services\CRM\Deal\Service\DealCategoryStage;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\TestCase;

/**
 * Class DealCategoryStageTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\CRM\Deal\Service
 */
class DealCategoryStageTest extends TestCase
{
    protected DealCategoryStage $dealCategoryStage;

    protected DealCategory $dealCategory;

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        $newCategoryId = $this->dealCategory->add(['NAME' => 'php unit test'])->getId();
        $dealCategoryStagesResult = $this->dealCategoryStage->list($newCategoryId);
        $this::assertGreaterThan(1, count($dealCategoryStagesResult->getDealCategoryStages()));
    }

    /**
     * @throws InvalidArgumentException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->dealCategoryStage = Factory::getServiceBuilder()->getCRMScope()->dealCategoryStage();
        $this->dealCategory = Factory::getServiceBuilder()->getCRMScope()->dealCategory();
    }
}