<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <titarx@gmail.com>
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
}
