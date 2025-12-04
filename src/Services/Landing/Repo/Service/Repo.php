<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Sally Fancen <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Landing\Repo\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Core\Result\AddedItemResult;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Services\Landing\Repo\Result\RepoGetListResult;
use Bitrix24\SDK\Services\Landing\Repo\Result\RepoCheckContentResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['landing']))]
class Repo extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Retrieves a list of blocks from the current application.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/user-blocks/landing-repo-get-list.html
     *
     * @param array $select Fields to select
     * @param array $filter Filter conditions
     * @param array $order Sort order
     * @param array $group Group fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.repo.getList',
        'https://apidocs.bitrix24.com/api-reference/landing/user-blocks/landing-repo-get-list.html',
        'Method retrieves a list of blocks from the current application.'
    )]
    public function getList(
        array $select = [],
        array $filter = [],
        array $order = [],
        array $group = []
    ): RepoGetListResult {
        $params = [];

        if ($select !== []) {
            $params['select'] = $select;
        }

        if ($filter !== []) {
            $params['filter'] = $filter;
        }

        if ($order !== []) {
            $params['order'] = $order;
        }

        if ($group !== []) {
            $params['group'] = $group;
        }

        $callParams = [];
        if ($params !== []) {
            $callParams['params'] = $params;
        }

        return new RepoGetListResult(
            $this->core->call('landing.repo.getList', $callParams)
        );
    }

    /**
     * Adds a block to the repository.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/user-blocks/landing-repo-register.html
     *
     * @param string $code Unique code for your block, which will be used to remove the block if necessary
     * @param array $fields An array of fields describing your block
     * @param array $manifest An array of the manifest describing the block
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.repo.register',
        'https://apidocs.bitrix24.com/api-reference/landing/user-blocks/landing-repo-register.html',
        'Method adds a block to the repository. Returns an error or the ID of the added block.'
    )]
    public function register(string $code, array $fields, array $manifest): AddedItemResult
    {
        return new AddedItemResult(
            $this->core->call('landing.repo.register', [
                'code' => $code,
                'fields' => $fields,
                'manifest' => $manifest
            ])
        );
    }

    /**
     * Deletes a block from the repository.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/user-blocks/landing-repo-unregister.html
     *
     * @param string $code Unique code of the block to be deleted
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.repo.unregister',
        'https://apidocs.bitrix24.com/api-reference/landing/user-blocks/landing-repo-unregister.html',
        'Method deletes a block. Returns true upon deletion or false if the block has already been deleted or did not exist.'
    )]
    public function unregister(string $code): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call('landing.repo.unregister', ['code' => $code])
        );
    }

    /**
     * Checks the content for dangerous substrings.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/user-blocks/landing-repo-check-content.html
     *
     * @param string $content Content to be tested
     * @param string $splitter Optional parameter for separating dangerous substrings. Defaults to #SANITIZE#
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.repo.checkContent',
        'https://apidocs.bitrix24.com/api-reference/landing/user-blocks/landing-repo-check-content.html',
        'Method checks the content for dangerous substrings. Used for content control during block registration.'
    )]
    public function checkContent(string $content, string $splitter = '#SANITIZE#'): RepoCheckContentResult
    {
        return new RepoCheckContentResult(
            $this->core->call('landing.repo.checkContent', [
                'content' => $content,
                'splitter' => $splitter
            ])
        );
    }
}
