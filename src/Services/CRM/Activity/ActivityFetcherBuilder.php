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

namespace Bitrix24\SDK\Services\CRM\Activity;

use Bitrix24\SDK\Services\AbstractServiceBuilder;
use Bitrix24\SDK\Services\CRM\Contact;
use Bitrix24\SDK\Services\CRM\Deal;
use Bitrix24\SDK\Services\CRM\Product;
use Bitrix24\SDK\Services\CRM\Settings;
use Bitrix24\SDK\Services\CRM\Activity;

class ActivityFetcherBuilder extends AbstractServiceBuilder
{
    public function emailFetcher(): Activity\ReadModel\EmailFetcher
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Activity\ReadModel\EmailFetcher($this->bulkItemsReader);
        }

        return $this->serviceCache[__METHOD__];
    }

    public function openLineFetcher(): Activity\ReadModel\OpenLineFetcher
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Activity\ReadModel\OpenLineFetcher($this->bulkItemsReader);
        }

        return $this->serviceCache[__METHOD__];
    }

    public function voximplantFetcher(): Activity\ReadModel\VoximplantFetcher
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Activity\ReadModel\VoximplantFetcher($this->bulkItemsReader);
        }

        return $this->serviceCache[__METHOD__];
    }

    public function webFormFetcher(): Activity\ReadModel\WebFormFetcher
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Activity\ReadModel\WebFormFetcher($this->bulkItemsReader);
        }

        return $this->serviceCache[__METHOD__];
    }
}
