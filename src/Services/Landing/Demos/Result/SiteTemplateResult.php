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
 * Class SiteTemplateResult
 * Result for landing.demos.getSiteList method
 */
class SiteTemplateResult extends AbstractResult
{
    /**
     * Get site templates collection
     * @return SiteTemplateItemResult[]
     */
    public function getSiteTemplates(): array
    {
        $siteTemplates = $this->getCoreResponse()->getResponseData()->getResult();

        if (!is_array($siteTemplates)) {
            return [];
        }

        $items = [];
        foreach ($siteTemplates as $siteTemplate) {
            $items[] = new SiteTemplateItemResult($siteTemplate);
        }

        return $items;
    }

    /**
     * Alias for getSiteTemplates() to match naming convention
     * @return SiteTemplateItemResult[]
     */
    public function getResult(): array
    {
        return $this->getSiteTemplates();
    }
}
