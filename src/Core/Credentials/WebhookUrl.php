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

namespace Bitrix24\SDK\Core\Credentials;

use Bitrix24\SDK\Core\ValueObjects\Url;
use Bitrix24\SDK\Core\ValueObjects\ValueObjectResolver;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;

/**
 * Class WebhookUrl
 *
 * @package Bitrix24\SDK\Core\Credentials
 */
class WebhookUrl
{
    protected string $url;

    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\InvalidArgumentException
     */
    public function __construct(string|Url $webhookUrl)
    {
        try {
            $this->url = ValueObjectResolver::resolveUrl($webhookUrl);
        } catch (InvalidArgumentException $exception) {
            throw new InvalidArgumentException('webhook URL is invalid', 0, $exception);
        }
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}