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

namespace Bitrix24\SDK\Services\Documentgenerator\Region\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Documentgenerator\Region\Result\AddedRegionResult;
use Bitrix24\SDK\Services\Documentgenerator\Region\Result\DeletedRegionResult;
use Bitrix24\SDK\Services\Documentgenerator\Region\Result\RegionResult;
use Bitrix24\SDK\Services\Documentgenerator\Region\Result\RegionsResult;
use Bitrix24\SDK\Services\Documentgenerator\Region\Result\UpdatedRegionResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['documentgenerator']))]
class Region extends AbstractService
{
    /**
     * Region constructor
     */
    public function __construct(public Batch $batch, CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Creates a new region
     *
     * @link https://apidocs.bitrix24.com/api-reference/document-generator/region/document-generator-region-add.html
     *
     * @param array{
     *   languageId: string,
     *   name: string,
     *   code: string
     * } $fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'documentgenerator.region.add',
        'https://apidocs.bitrix24.com/api-reference/document-generator/region/document-generator-region-add.html',
        'Creates a new region'
    )]
    public function add(array $fields): AddedRegionResult
    {
        return new AddedRegionResult(
            $this->core->call(
                'documentgenerator.region.add',
                [
                    'fields' => $fields,
                ]
            )
        );
    }

    /**
     * Updates an existing region with new values
     *
     * @link https://apidocs.bitrix24.com/api-reference/document-generator/region/document-generator-region-update.html
     *
     * @param array{
     *   languageId?: string,
     *   name?: string,
     *   code?: string
     * } $fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'documentgenerator.region.update',
        'https://apidocs.bitrix24.com/api-reference/document-generator/region/document-generator-region-update.html',
        'Updates an existing region with new values'
    )]
    public function update(int $id, array $fields): UpdatedRegionResult
    {
        return new UpdatedRegionResult(
            $this->core->call(
                'documentgenerator.region.update',
                [
                    'id' => $id,
                    'fields' => $fields,
                ]
            )
        );
    }

    /**
     * Returns information about the region by its identifier
     *
     * @link https://apidocs.bitrix24.com/api-reference/document-generator/region/document-generator-region-get.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'documentgenerator.region.get',
        'https://apidocs.bitrix24.com/api-reference/document-generator/region/document-generator-region-get.html',
        'Returns information about the region by its identifier'
    )]
    public function get(int $id): RegionResult
    {
        return new RegionResult(
            $this->core->call('documentgenerator.region.get', ['id' => $id])
        );
    }

    /**
     * Returns a list of regions
     *
     * @link https://apidocs.bitrix24.com/api-reference/document-generator/region/document-generator-region-list.html
     *
     * @param int $start Offset for pagination
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'documentgenerator.region.list',
        'https://apidocs.bitrix24.com/api-reference/document-generator/region/document-generator-region-list.html',
        'Returns a list of regions'
    )]
    public function list(int $start = 0): RegionsResult
    {
        return new RegionsResult(
            $this->core->call(
                'documentgenerator.region.list',
                [
                    'start' => $start,
                ]
            )
        );
    }

    /**
     * Deletes a region
     *
     * @link https://apidocs.bitrix24.com/api-reference/document-generator/region/document-generator-region-delete.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'documentgenerator.region.delete',
        'https://apidocs.bitrix24.com/api-reference/document-generator/region/document-generator-region-delete.html',
        'Deletes a region'
    )]
    public function delete(int $id): DeletedRegionResult
    {
        return new DeletedRegionResult(
            $this->core->call(
                'documentgenerator.region.delete',
                ['id' => $id]
            )
        );
    }

    /**
     * Count regions
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function count(): int
    {
        return $this->list()->getCoreResponse()->getResponseData()->getPagination()->getTotal();
    }
}
