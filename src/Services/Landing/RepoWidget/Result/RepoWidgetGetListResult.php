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

namespace Bitrix24\SDK\Services\Landing\RepoWidget\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Represents the response from landing.repowidget.getlist.
 *
 * @link https://apidocs.bitrix24.com/api-reference/vibe/landing-repowidget-get-list.html
 */
class RepoWidgetGetListResult extends AbstractResult
{
    /**
     * Returns the list of Vibe widget items.
     *
     * @return RepoWidgetItemResult[]
     * @throws BaseException
     */
    public function getRepoWidgetItems(): array
    {
        $items = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult() as $item) {
            if (is_array($item)) {
                $items[] = new RepoWidgetItemResult($item);
            }
        }

        return $items;
    }
}

