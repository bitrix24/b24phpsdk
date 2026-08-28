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

namespace Bitrix24\SDK\Services\Catalog\UserfieldDocument\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Catalog\UserfieldDocument\Result\UserfieldDocumentResult;
use Bitrix24\SDK\Services\Catalog\UserfieldDocument\Result\UserfieldDocumentsResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['catalog']))]
class UserfieldDocument extends AbstractService
{
    public function __construct(public Batch $batch, CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Returns a paginated list of userfield values for warehouse accounting documents.
     * The «documentType» key is required in both $select and $filter.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/userfield-document/catalog-userfield-document-list.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.userfield.document.list',
        'https://apidocs.bitrix24.com/api-reference/catalog/userfield-document/catalog-userfield-document-list.html',
        'Returns a paginated list of userfield values for warehouse accounting documents'
    )]
    public function list(array $select, array $filter, array $order = [], int $start = 0): UserfieldDocumentsResult
    {
        return new UserfieldDocumentsResult(
            $this->core->call(
                'catalog.userfield.document.list',
                ['select' => $select, 'filter' => $filter, 'order' => $order, 'start' => $start]
            )
        );
    }

    /**
     * Updates userfield values of a warehouse accounting document.
     * $fields must contain «documentType» plus the fieldN values to update.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/userfield-document/catalog-userfield-document-update.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.userfield.document.update',
        'https://apidocs.bitrix24.com/api-reference/catalog/userfield-document/catalog-userfield-document-update.html',
        'Updates userfield values of a warehouse accounting document'
    )]
    public function update(int $documentId, array $fields): UserfieldDocumentResult
    {
        return new UserfieldDocumentResult(
            $this->core->call('catalog.userfield.document.update', ['documentId' => $documentId, 'fields' => $fields])
        );
    }
}
