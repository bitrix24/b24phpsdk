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
use Psr\Log\LoggerInterface;

abstract class AbstractServiceBuilder
{
    /**
     * @var array<string, mixed>
     */
    protected array $serviceCache;

    /**
     * AbstractServiceBuilder constructor.
     *
     * @param CoreInterface $core
     * @param BatchOperationsInterface $batch
     * @param BulkItemsReaderInterface $bulkItemsReader
     * @param LoggerInterface $log
     */
    public function __construct(
        public CoreInterface               $core,
        protected BatchOperationsInterface $batch,
        protected BulkItemsReaderInterface $bulkItemsReader,
        protected LoggerInterface          $log
    )
    {
    }
}