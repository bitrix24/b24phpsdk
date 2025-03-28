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

use Bitrix24\SDK\Services\AI\Engine\EngineCategory;
use Bitrix24\SDK\Services\AI\Engine\EngineSettings;
use Bitrix24\SDK\Services\CRM\Activity\ActivityContentType;
use Bitrix24\SDK\Services\CRM\Activity\ActivityDirectionType;
use Bitrix24\SDK\Services\CRM\Activity\ActivityNotifyType;
use Bitrix24\SDK\Services\CRM\Activity\ActivityPriority;
use Bitrix24\SDK\Services\CRM\Activity\ActivityStatus;
use Bitrix24\SDK\Services\CRM\Activity\ActivityType;
use Bitrix24\SDK\Services\CRM\Common\Result\AbstractCrmItem;
use Bitrix24\SDK\Services\CRM\Common\Result\DiscountType;
use Bitrix24\SDK\Services\CRM\Common\Result\SystemFields\Types\Email;
use Bitrix24\SDK\Services\CRM\Common\Result\SystemFields\Types\File;
use Bitrix24\SDK\Services\CRM\Common\Result\SystemFields\Types\InstantMessenger;
use Bitrix24\SDK\Services\CRM\Common\Result\SystemFields\Types\Phone;
use Bitrix24\SDK\Services\CRM\Common\Result\SystemFields\Types\Website;
use Bitrix24\SDK\Services\CRM\Deal\Result\DealSemanticStage;
use Carbon\CarbonImmutable;
use Money\Currency;
use Money\Money;
use MoneyPHP\Percentage\Percentage;

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
class EngineItemResult extends AbstractCrmItem
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