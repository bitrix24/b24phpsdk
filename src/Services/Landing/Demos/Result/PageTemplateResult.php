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

namespace Bitrix24\SDK\Services\Landing\Demos\Result;

use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class PageTemplateResult
 * Result for landing.demos.getPageList method
 */
class PageTemplateResult extends AbstractResult
{
    /**
     * Get page templates collection
     * @return PageTemplateItemResult[]
     */
    public function getPageTemplates(): array
    {
        $pageTemplates = $this->getCoreResponse()->getResponseData()->getResult();

        if (!is_array($pageTemplates)) {
            return [];
        }

        $items = [];
        foreach ($pageTemplates as $pageTemplate) {
            $items[] = new PageTemplateItemResult($pageTemplate);
        }

        return $items;
    }

    /**
     * Alias for getPageTemplates() to match naming convention
     * @return PageTemplateItemResult[]
     */
    public function getResult(): array
    {
        return $this->getPageTemplates();
    }
}
