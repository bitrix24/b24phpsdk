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

namespace Bitrix24\SDK\Services\Calendar\Result;

use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class CalendarSectionsResult
 * Represents the result of a list calendar sections operation.
 */
class CalendarSectionsResult extends AbstractResult
{
    /**
     * Returns array of calendar sections
     */
    public function getSections(): array
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();
        $sections = [];

        foreach ($result as $sectionData) {
            $sections[] = new CalendarSectionItemResult($sectionData);
        }

        return $sections;
    }
}
