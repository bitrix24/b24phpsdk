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

namespace Bitrix24\SDK\Services\Sign\B2e\Document\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Sign\B2e\Document\Result\DocumentResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['sign.b2e']))]
class Document extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Sends a document for company-side signing (КЭДО).
     *
     * @link https://apidocs.bitrix24.com/api-reference/sign/sign-b2e-document-send.html
     *
     * @param array{
     *     company?: array<string, mixed>,
     *     members?: array<int, array{userId: int, role: string}>,
     *     responsible?: array{userId: int},
     *     companyProviderUid?: string,
     *     files?: array<int, array{fileName: string, fileType: string, fileContent: string}>,
     *     regionDocumentType?: string,
     *     externalSettings?: array<string, mixed>,
     *     language?: string
     * } $fields Document fields
     * @param string|null $language Language for status localisation in response (default: en)
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sign.b2e.document.send',
        'https://apidocs.bitrix24.com/api-reference/sign/sign-b2e-document-send.html',
        'Sends a document for company-side signing (КЭДО). Requires application context.'
    )]
    public function send(array $fields, ?string $language = null): DocumentResult
    {
        $params = ['fields' => $fields];
        if ($language !== null) {
            $params['language'] = $language;
        }

        return new DocumentResult($this->core->call('sign.b2e.document.send', $params));
    }

    /**
     * Returns information about a document and its signing members.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sign/sign-b2e-document-get.html
     *
     * @param string $uid Unique document identifier
     * @param string|null $language Language for status localisation in response (default: en)
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sign.b2e.document.get',
        'https://apidocs.bitrix24.com/api-reference/sign/sign-b2e-document-get.html',
        'Returns information about a document and its signing members.'
    )]
    public function get(string $uid, ?string $language = null): DocumentResult
    {
        $params = ['uid' => $uid];
        if ($language !== null) {
            $params['language'] = $language;
        }

        return new DocumentResult($this->core->call('sign.b2e.document.get', $params));
    }
}

