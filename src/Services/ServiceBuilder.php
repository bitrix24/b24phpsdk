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

namespace Bitrix24\SDK\Services;

use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Contracts\BulkItemsReaderInterface;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Services\AI\AIServiceBuilder;
use Bitrix24\SDK\Services\Catalog\CatalogServiceBuilder;
use Bitrix24\SDK\Services\CRM\CRMServiceBuilder;
use Bitrix24\SDK\Services\Disk\DiskServiceBuilder;
use Bitrix24\SDK\Services\Entity\EntityServiceBuilder;
use Bitrix24\SDK\Services\Department\DepartmentServiceBuilder;
use Bitrix24\SDK\Services\Task\TaskServiceBuilder;
use Bitrix24\SDK\Services\IM\IMServiceBuilder;
use Bitrix24\SDK\Services\IMOpenLines\IMOpenLinesServiceBuilder;
use Bitrix24\SDK\Services\Log\LogServiceBuilder;
use Bitrix24\SDK\Services\Main\MainServiceBuilder;
use Bitrix24\SDK\Services\Telephony\TelephonyServiceBuilder;
use Bitrix24\SDK\Services\User\UserServiceBuilder;
use Bitrix24\SDK\Services\UserConsent\UserConsentServiceBuilder;
use Bitrix24\SDK\Services\Placement\PlacementServiceBuilder;
use Bitrix24\SDK\Services\Workflows\WorkflowsServiceBuilder;
use Bitrix24\SDK\Services\Sale\SaleServiceBuilder;
use Bitrix24\SDK\Services\Calendar\CalendarServiceBuilder;
use Bitrix24\SDK\Services\Paysystem\PaysystemServiceBuilder;
use Bitrix24\SDK\Services\SonetGroup\SonetGroupServiceBuilder;
use Psr\Log\LoggerInterface;

class ServiceBuilder extends AbstractServiceBuilder
{
    public function __construct(
        public CoreInterface $core,
        protected BatchOperationsInterface $batch,
        protected BulkItemsReaderInterface $bulkItemsReader,
        protected LoggerInterface $log
    ) {
        parent::__construct($core, $batch, $bulkItemsReader, $log);
    }

    public function getSaleScope(): SaleServiceBuilder
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new SaleServiceBuilder(
                $this->core,
                $this->batch,
                $this->bulkItemsReader,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function getCalendarScope(): CalendarServiceBuilder
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new CalendarServiceBuilder(
                $this->core,
                $this->batch,
                $this->bulkItemsReader,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function getTaskScope(): TaskServiceBuilder
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new TaskServiceBuilder(
                $this->core,
                $this->batch,
                $this->bulkItemsReader,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }
    
    public function getDepartmentScope(): DepartmentServiceBuilder
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new DepartmentServiceBuilder(
                $this->core,
                $this->batch,
                $this->bulkItemsReader,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }
    
    public function getEntityScope(): EntityServiceBuilder
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new EntityServiceBuilder(
                $this->core,
                $this->batch,
                $this->bulkItemsReader,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function getCRMScope(): CRMServiceBuilder
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new CRMServiceBuilder(
                $this->core,
                $this->batch,
                $this->bulkItemsReader,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function getDiskScope(): DiskServiceBuilder
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new DiskServiceBuilder(
                $this->core,
                $this->batch,
                $this->bulkItemsReader,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function getIMScope(): IMServiceBuilder
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new IMServiceBuilder(
                $this->core,
                $this->batch,
                $this->bulkItemsReader,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function getIMOpenLinesScope(): IMOpenLinesServiceBuilder
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new IMOpenLinesServiceBuilder(
                $this->core,
                $this->batch,
                $this->bulkItemsReader,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    /**
     * @return MainServiceBuilder
     */
    public function getMainScope(): MainServiceBuilder
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new MainServiceBuilder(
                $this->core,
                $this->batch,
                $this->bulkItemsReader,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function getUserConsentScope(): UserConsentServiceBuilder
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new UserConsentServiceBuilder(
                $this->core,
                $this->batch,
                $this->bulkItemsReader,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function getUserScope(): UserServiceBuilder
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new UserServiceBuilder(
                $this->core,
                $this->batch,
                $this->bulkItemsReader,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    /**
     * @return PlacementServiceBuilder
     */
    public function getPlacementScope(): PlacementServiceBuilder
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new PlacementServiceBuilder(
                $this->core,
                $this->batch,
                $this->bulkItemsReader,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function getPaysystemScope(): PaysystemServiceBuilder
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new PaysystemServiceBuilder(
                $this->core,
                $this->batch,
                $this->bulkItemsReader,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function getCatalogScope(): CatalogServiceBuilder
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new CatalogServiceBuilder(
                $this->core,
                $this->batch,
                $this->bulkItemsReader,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function getBizProcScope(): WorkflowsServiceBuilder
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new WorkflowsServiceBuilder(
                $this->core,
                $this->batch,
                $this->bulkItemsReader,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function getTelephonyScope(): TelephonyServiceBuilder
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new TelephonyServiceBuilder(
                $this->core,
                $this->batch,
                $this->bulkItemsReader,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function getLogScope(): LogServiceBuilder
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new LogServiceBuilder(
                $this->core,
                $this->batch,
                $this->bulkItemsReader,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function getAiAdminScope(): AIServiceBuilder
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new AIServiceBuilder(
                $this->core,
                $this->batch,
                $this->bulkItemsReader,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }
    
    public function getSonetGroupScope(): SonetGroupServiceBuilder
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new SonetGroupServiceBuilder(
                $this->core,
                $this->batch,
                $this->bulkItemsReader,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }
    
}
