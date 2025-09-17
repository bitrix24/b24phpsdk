<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Log\BlogPost\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Log\BlogPost\Result\BlogPostAddResult;

#[ApiServiceMetadata(new Scope(['log']))]
class BlogPost extends AbstractService
{
    /**
     * Add new blog post to Live Feed
     *
     * @param string $postMessage Text message content
     * @param string|null $postTitle Message title (optional)
     * @param int|null $userId Author ID (optional, defaults to current user, other values available only to admin in box version)
     * @param array|null $dest List of recipients who will receive the right to view the message (optional, defaults to ['UA'])
     * @param array|null $sperm List of recipients who will receive the right to view the message (deprecated, same as DEST)
     * @param array|null $files Files array described according to rules
     * @param bool $important Whether message should be published as "important" (default false)
     * @param string|null $importantDateEnd Date/time value until which the message will be considered important
     * 
     * @throws BaseException
     * @throws TransportException
     * @link https://apidocs.bitrix24.com/api-reference/log/log-blogpost-add.html
     */
    #[ApiEndpointMetadata(
        'log.blogpost.add',
        'https://apidocs.bitrix24.com/api-reference/log/log-blogpost-add.html',
        'Add new blog post to Live Feed'
    )]
    public function add(
        string $postMessage,
        ?string $postTitle = null,
        ?int $userId = null,
        ?array $dest = null,
        ?array $sperm = null,
        ?array $files = null,
        bool $important = false,
        ?string $importantDateEnd = null
    ): BlogPostAddResult {
        $params = [
            'POST_MESSAGE' => $postMessage,
        ];

        if ($postTitle !== null) {
            $params['POST_TITLE'] = $postTitle;
        }

        if ($userId !== null) {
            $params['USER_ID'] = $userId;
        }

        if ($dest !== null) {
            $params['DEST'] = $dest;
        }

        if ($sperm !== null) {
            $params['SPERM'] = $sperm;
        }

        if ($files !== null) {
            $params['FILES'] = $files;
        }

        if ($important) {
            $params['IMPORTANT'] = 'Y';
        }

        if ($importantDateEnd !== null) {
            $params['IMPORTANT_DATE_END'] = $importantDateEnd;
        }

        return new BlogPostAddResult(
            $this->core->call('log.blogpost.add', $params)
        );
    }
}
