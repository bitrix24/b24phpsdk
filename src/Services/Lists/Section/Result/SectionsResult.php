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

namespace Bitrix24\SDK\Services\Lists\Section\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class SectionsResult
 *
 * @package Bitrix24\SDK\Services\Lists\Section\Result
 */
class SectionsResult extends AbstractResult
{
    /**
     * @return SectionItemResult[]
     * @throws BaseException
     */
    public function getSections(): array
    {
        $sections = [];
        $result = $this->getCoreResponse()->getResponseData()->getResult();

        // Handle both single section and array of sections
        if (isset($result['ID'])) {
            // Single section
            $sections[] = new SectionItemResult($result);
        } else {
            // Array of sections
            foreach ($result as $item) {
                $sections[] = new SectionItemResult($item);
            }
        }

        return $sections;
    }
}
