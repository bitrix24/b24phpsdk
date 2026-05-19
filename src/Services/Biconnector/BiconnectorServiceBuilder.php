<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Biconnector;

use Bitrix24\SDK\Attributes\ApiServiceBuilderMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Services\AbstractServiceBuilder;
use Bitrix24\SDK\Services\Biconnector\Connector\Batch as ConnectorBatch;
use Bitrix24\SDK\Services\Biconnector\Connector\Service\Batch;
use Bitrix24\SDK\Services\Biconnector\Connector\Service\Connector;
use Bitrix24\SDK\Services\Biconnector\Dataset\Batch as DatasetBatch;
use Bitrix24\SDK\Services\Biconnector\Dataset\Service\Batch as DatasetServiceBatch;
use Bitrix24\SDK\Services\Biconnector\Dataset\Service\Dataset;
use Bitrix24\SDK\Services\Biconnector\Source\Batch as SourceBatch;
use Bitrix24\SDK\Services\Biconnector\Source\Service\Batch as SourceServiceBatch;
use Bitrix24\SDK\Services\Biconnector\Source\Service\Source;

#[ApiServiceBuilderMetadata(new Scope(['biconnector']))]
class BiconnectorServiceBuilder extends AbstractServiceBuilder
{
    /**
     * Get the Connector service
     *
     * Uses a specialized ConnectorBatch to handle biconnector.connector.* REST API differences:
     * - list uses 'page' parameter (page number) instead of standard 'start' (offset)
     * - delete uses lowercase 'id' instead of 'ID'
     */
    public function connector(): Connector
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            // Use specialized Batch for Connector to ensure correct REST parameter mapping
            $connectorBatch = new ConnectorBatch(
                $this->core,
                $this->log
            );
            $this->serviceCache[__METHOD__] = new Connector(
                new Batch($connectorBatch, $this->log),
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Get the Dataset service
     *
     * Uses a specialized DatasetBatch to handle biconnector.dataset.* REST API differences:
     * - list uses 'page' parameter (page number) instead of standard 'start' (offset)
     * - delete uses lowercase 'id' instead of 'ID'
     */
    public function dataset(): Dataset
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $datasetBatch = new DatasetBatch(
                $this->core,
                $this->log
            );
            $this->serviceCache[__METHOD__] = new Dataset(
                new DatasetServiceBatch($datasetBatch, $this->log),
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Get the Source service
     *
     * Uses a specialized SourceBatch to handle biconnector.source.* REST API differences:
     * - delete uses lowercase 'id' instead of 'ID'
     */
    public function source(): Source
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $sourceBatch = new SourceBatch(
                $this->core,
                $this->log
            );
            $this->serviceCache[__METHOD__] = new Source(
                new SourceServiceBatch($sourceBatch, $this->log),
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }
}
