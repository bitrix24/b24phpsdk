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

namespace Bitrix24\SDK\Services\CRM\Documentgenerator\Numerator\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\AddedItemResult;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Core\Result\FieldsResult;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\CRM\Documentgenerator\Numerator\Result\NumeratorResult;
use Bitrix24\SDK\Services\CRM\Documentgenerator\Numerator\Result\NumeratorsResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['crm']))]
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
     * Adds a new numerator
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/document-generator/numerator/crm-document-generator-numerator-add.html
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
        'crm.documentgenerator.numerator.add',
        'https://apidocs.bitrix24.com/api-reference/crm/document-generator/numerator/crm-document-generator-numerator-add.html',
        'Adds a new numerator'
    )]
    public function add(array $fields): AddedItemResult
    {
        return new AddedItemResult(
            $this->core->call(
                'crm.documentgenerator.numerator.add',
                [
                    'fields' => $fields
                ]
            )
        );
    }

    /**
     * Removes a numerator
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/document-generator/numerator/crm-document-generator-numerator-delete.html
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.documentgenerator.numerator.delete',
        'https://apidocs.bitrix24.com/api-reference/crm/document-generator/numerator/crm-document-generator-numerator-delete.html',
        'Removes a numerator'
    )]
    public function delete(int $id): DeletedItemResult
    {
        $params = [
            'id' => $id,
        ];

        return new DeletedItemResult(
            $this->core->call(
                'crm.documentgenerator.numerator.delete',
                $params
            )
        );
    }

    /**
     * Returns information about the numerator by its identifier
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/document-generator/numerator/crm-document-generator-numerator-get.html
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.documentgenerator.numerator.get',
        'https://apidocs.bitrix24.com/api-reference/crm/document-generator/numerator/crm-document-generator-numerator-get.html',
        'Returns information about the numerator by its identifier'
    )]
    public function get(int $id): NumeratorResult
    {
        return new NumeratorResult($this->core->call('crm.documentgenerator.numerator.get', ['id' => $id]));
    }

    /**
     * Returns a list of numerators
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/document-generator/numerator/crm-document-generator-numerator-list.html
     *
     * @param int $start - offset for pagination
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.documentgenerator.numerator.list',
        'https://apidocs.bitrix24.com/api-reference/crm/document-generator/numerator/crm-document-generator-numerator-list.html',
        'Returns a list of numerators'
    )]
    public function list(int $start = 0): NumeratorsResult
    {
        return new NumeratorsResult(
            $this->core->call(
                'crm.documentgenerator.numerator.list',
                [
                    'start' => $start
                ]
            )
        );
    }

    /**
     * Updates an existing numbering with new values
     *
     * The method crm.documentgenerator.numerator.update updates an existing numbering with new values
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/document-generator/numerator/crm-document-generator-numerator-update.html
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
        'crm.documentgenerator.numerator.update',
        'https://apidocs.bitrix24.com/api-reference/crm/document-generator/numerator/crm-document-generator-numerator-update.html',
        'Updates an existing numbering with new values'
    )]
    public function update(int $id, array $fields): UpdatedItemResult
    {
        $params = [
            'id' => $id,
            'fields' => $fields
        ];

        return new UpdatedItemResult(
            $this->core->call(
                'crm.documentgenerator.numerator.update',
                $params
            )
        );
    }
}
