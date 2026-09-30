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

namespace Bitrix24\SDK\Services\Telephony\FollowUp\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;
use Bitrix24\SDK\Services\Telephony\FollowUp\Service\FollowUpListCursor;

class FollowUpsResult extends AbstractResult
{
    /**
     * @return FollowUpItemResult[]
     * @throws BaseException
     */
    public function getFollowUps(): array
    {
        $items = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult()['items'] as $item) {
            $items[] = new FollowUpItemResult($item);
        }

        return $items;
    }

    /**
     * @throws BaseException
     */
    public function hasMore(): bool
    {
        return (bool)($this->getCoreResponse()->getResponseData()->getResult()['hasMore'] ?? false);
    }

    /**
     * @throws BaseException
     */
    public function getNextCursor(): ?FollowUpListCursor
    {
        $cursor = $this->getCoreResponse()->getResponseData()->getResult()['afterCursor'] ?? null;

        return $cursor === null ? null : FollowUpListCursor::fromArray($cursor);
    }
}
