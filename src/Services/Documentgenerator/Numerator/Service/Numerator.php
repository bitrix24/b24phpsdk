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

namespace Bitrix24\SDK\Services\Documentgenerator\Numerator\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Documentgenerator\Numerator\Result\AddedNumeratorResult;
use Bitrix24\SDK\Services\Documentgenerator\Numerator\Result\DeletedNumeratorResult;
use Bitrix24\SDK\Services\Documentgenerator\Numerator\Result\NumeratorResult;
use Bitrix24\SDK\Services\Documentgenerator\Numerator\Result\NumeratorsResult;
use Bitrix24\SDK\Services\Documentgenerator\Numerator\Result\UpdatedNumeratorResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['documentgenerator']))]
class Numerator extends AbstractService
{
    /**
     * Numerator constructor
     */
    public function __construct(public Batch $batch, CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Creates a new numerator
     *
     * @link https://apidocs.bitrix24.com/api-reference/document-generator/numerators/document-generator-numerator-add.html
     *
     * @param array{
     *   name: string,
     *   template: string,
     *   settings?: array
     * } $fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'documentgenerator.numerator.add',
        'https://apidocs.bitrix24.com/api-reference/document-generator/numerators/document-generator-numerator-add.html',
        'Creates a new numerator'
    )]
    public function add(array $fields): AddedNumeratorResult
    {
        return new AddedNumeratorResult(
            $this->core->call(
                'documentgenerator.numerator.add',
                [
                    'fields' => $fields,
                ]
            )
        );
    }

    /**
     * Updates an existing numerator with new values
     *
     * @link https://apidocs.bitrix24.com/api-reference/document-generator/numerators/document-generator-numerator-update.html
     *
     * @param array{
     *   name?: string,
     *   template?: string,
     *   settings?: array
     * } $fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'documentgenerator.numerator.update',
        'https://apidocs.bitrix24.com/api-reference/document-generator/numerators/document-generator-numerator-update.html',
        'Updates an existing numerator with new values'
    )]
    public function update(int $id, array $fields): UpdatedNumeratorResult
    {
        return new UpdatedNumeratorResult(
            $this->core->call(
                'documentgenerator.numerator.update',
                [
                    'id' => $id,
                    'fields' => $fields,
                ]
            )
        );
    }

    /**
     * Returns information about the numerator by its identifier
     *
     * @link https://apidocs.bitrix24.com/api-reference/document-generator/numerators/document-generator-numerator-get.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'documentgenerator.numerator.get',
        'https://apidocs.bitrix24.com/api-reference/document-generator/numerators/document-generator-numerator-get.html',
        'Returns information about the numerator by its identifier'
    )]
    public function get(int $id): NumeratorResult
    {
        return new NumeratorResult(
            $this->core->call('documentgenerator.numerator.get', ['id' => $id])
        );
    }

    /**
     * Returns a list of numerators
     *
     * @link https://apidocs.bitrix24.com/api-reference/document-generator/numerators/document-generator-numerator-list.html
     *
     * @param int $start Offset for pagination
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'documentgenerator.numerator.list',
        'https://apidocs.bitrix24.com/api-reference/document-generator/numerators/document-generator-numerator-list.html',
        'Returns a list of numerators'
    )]
    public function list(int $start = 0): NumeratorsResult
    {
        return new NumeratorsResult(
            $this->core->call(
                'documentgenerator.numerator.list',
                [
                    'start' => $start,
                ]
            )
        );
    }

    /**
     * Deletes a numerator
     *
     * @link https://apidocs.bitrix24.com/api-reference/document-generator/numerators/document-generator-numerator-delete.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'documentgenerator.numerator.delete',
        'https://apidocs.bitrix24.com/api-reference/document-generator/numerators/document-generator-numerator-delete.html',
        'Deletes a numerator'
    )]
    public function delete(int $id): DeletedNumeratorResult
    {
        return new DeletedNumeratorResult(
            $this->core->call(
                'documentgenerator.numerator.delete',
                ['id' => $id]
            )
        );
    }

    /**
     * Count numerators
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function count(): int
    {
        return $this->list()->getCoreResponse()->getResponseData()->getPagination()->getTotal();
    }
}
