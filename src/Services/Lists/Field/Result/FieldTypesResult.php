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

namespace Bitrix24\SDK\Services\Lists\Field\Result;

use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class FieldTypesResult
 *
 * @package Bitrix24\SDK\Services\Lists\Field\Result
 */
class FieldTypesResult extends AbstractResult
{
    /**
     * Get available field types
     *
     * @return array<string, string>
     */
    public function types(): array
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();

        return is_array($result) ? $result : [];
    }
}
