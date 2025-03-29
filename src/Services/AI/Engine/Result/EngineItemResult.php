<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\AI\Engine\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;
use Bitrix24\SDK\Services\AI\Engine\EngineCategory;
use Bitrix24\SDK\Services\AI\Engine\EngineSettings;
use Carbon\CarbonImmutable;

/**
 * @property-read int $id
 * @property-read non-empty-string $app_code
 * @property-read non-empty-string $name
 * @property-read non-empty-string $code
 * @property-read EngineCategory $category
 * @property-read non-empty-string $completionsUrl
 * @property-read EngineSettings $settings
 * @property-read CarbonImmutable $dateCreate
 */
class EngineItemResult extends AbstractItem
{
    /**
     * @param int|string $offset
     *
     * @return bool|CarbonImmutable|int|mixed|null
     */
    public function __get($offset)
    {
        switch ($offset) {
            case 'id':
                if ($this->data[$offset] !== '' && $this->data[$offset] !== null) {
                    return (int)$this->data[$offset];
                }

                return null;
            case 'category':
                return EngineCategory::from($this->data[$offset]);
            case 'settings':
                return EngineSettings::fromArray($this->data[$offset]);
            case 'dateCreate':
                return CarbonImmutable::createFromTimestamp($this->data[$offset]);
            default:
                return $this->data[$offset] ?? null;
        }
    }

}