<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Bitrix24\SDK\Services\Log\BlogComment\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Result\AddedItemResult;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Services\AbstractService;

#[ApiServiceMetadata(new Scope(['log']))]
class BlogComment extends AbstractService
{
    #[ApiEndpointMetadata('log.blogcomment.add', 'https://apidocs.bitrix24.com/api-reference/log/blogcomment/log-blogcomment-add.html', 'Add a feed post comment')]
    public function add(int $postId, string $text, ?array $files = null, ?int $userId = null): AddedItemResult
    {
        return new AddedItemResult($this->core->call('log.blogcomment.add', array_filter([
            'POST_ID' => $postId, 'TEXT' => $text, 'FILES' => $files, 'USER_ID' => $userId,
        ], static fn ($value): bool => $value !== null)));
    }
    #[ApiEndpointMetadata('log.blogcomment.delete', 'https://apidocs.bitrix24.com/api-reference/log/blogcomment/log-blogcomment-delete.html', 'Delete a feed post comment')]
    public function delete(int $commentId, ?int $userId = null): DeletedItemResult
    {
        return new DeletedItemResult($this->core->call('log.blogcomment.delete', array_filter([
            'COMMENT_ID' => $commentId, 'USER_ID' => $userId,
        ], static fn ($value): bool => $value !== null)));
    }
}
