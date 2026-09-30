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
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\Log\BlogPost\Result\BlogPostsResult;
use Bitrix24\SDK\Services\Log\BlogPost\Result\BlogPostUpdateResult;
use Bitrix24\SDK\Services\Log\BlogPost\Result\ImportantUsersResult;
use Carbon\CarbonImmutable;
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

    /** Returns a list, including when filtering by a single post ID. */
    #[ApiEndpointMetadata('log.blogpost.get', 'https://apidocs.bitrix24.com/api-reference/log/log-blogpost-get.html', 'Get feed posts')]
    public function get(?int $postId = null, ?array $logRights = null, ?CarbonImmutable $dateFrom = null, ?CarbonImmutable $dateTo = null, ?int $firstId = null, ?int $lastId = null, ?int $limit = null, int $start = 0): BlogPostsResult
    {
        return new BlogPostsResult($this->core->call('log.blogpost.get', array_filter([
            'POST_ID' => $postId, 'LOG_RIGHTS' => $logRights,
            'LOG_DATE_FROM' => $dateFrom?->toIso8601String(), 'LOG_DATE_TO' => $dateTo?->toIso8601String(),
            'FIRST_ID' => $firstId, 'LAST_ID' => $lastId, 'LIMIT' => $limit, 'start' => $start,
        ], static fn ($value): bool => $value !== null)));
    }

    #[ApiEndpointMetadata('log.blogpost.delete', 'https://apidocs.bitrix24.com/api-reference/log/log-blogpost-delete.html', 'Delete a feed post')]
    public function delete(int $postId): DeletedItemResult
    {
        return new DeletedItemResult($this->core->call('log.blogpost.delete', ['POST_ID' => $postId]));
    }

    /**
     * Update a post. Pass its existing title when changing only the message to preserve that title.
     * @param array<string, mixed> $userFields Portal-specific UF_* fields.
     */
    #[ApiEndpointMetadata('log.blogpost.update', 'https://apidocs.bitrix24.com/api-reference/log/log-blogpost-update.html', 'Update a feed post')]
    public function update(int $postId, ?string $postMessage = null, ?string $postTitle = null, ?array $dest = null, ?array $files = null, ?bool $important = null, ?CarbonImmutable $importantDateEnd = null, ?int $userId = null, ?string $siteId = null, array $userFields = []): BlogPostUpdateResult
    {
        $parameters = array_filter([
            'POST_ID' => $postId, 'POST_MESSAGE' => $postMessage, 'POST_TITLE' => $postTitle,
            'DEST' => $dest, 'FILES' => $files, 'IMPORTANT' => $important === null ? null : ($important ? 'Y' : 'N'),
            'IMPORTANT_DATE_END' => $importantDateEnd?->toIso8601String(), 'USER_ID' => $userId, 'SITE_ID' => $siteId,
        ], static fn ($value): bool => $value !== null);
        foreach ($userFields as $name => $value) {
            if (!str_starts_with($name, 'UF_')) {
                throw new \InvalidArgumentException('Custom post fields must start with UF_.');
            }

            $parameters[$name] = $value;
        }

        return new BlogPostUpdateResult($this->core->call('log.blogpost.update', $parameters));
    }

    /** Adds viewing permissions for the explicitly supplied destinations. */
    #[ApiEndpointMetadata('log.blogpost.share', 'https://apidocs.bitrix24.com/api-reference/log/log-blogpost-share.html', 'Share a feed post')]
    public function share(int $postId, array $dest, ?int $userId = null): UpdatedItemResult
    {
        return new UpdatedItemResult($this->core->call('log.blogpost.share', array_filter([
            'POST_ID' => $postId, 'DEST' => $dest, 'USER_ID' => $userId,
        ], static fn ($value): bool => $value !== null)));
    }

    /** Returns at most 500 IDs; an empty list may also indicate no read permission. */
    #[ApiEndpointMetadata('log.blogpost.getusers.important', 'https://apidocs.bitrix24.com/api-reference/log/log-blogpost-getusers-important.html', 'Get readers of an important post')]
    public function getUsersImportant(int $postId): ImportantUsersResult
    {
        return new ImportantUsersResult($this->core->call('log.blogpost.getusers.important', ['POST_ID' => $postId]));
    }
}
