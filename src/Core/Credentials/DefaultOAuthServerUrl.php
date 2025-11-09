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

class DefaultOAuthServerUrl
{
    private const BITRIX24_PHP_SDK_DEFAULT_AUTH_SERVER_URL = 'BITRIX24_PHP_SDK_DEFAULT_AUTH_SERVER_URL';

    /**
     * Return default OAUTH server for east region
     * @return non-empty-string
     * @see https://apidocs.bitrix24.ru/settings/oauth/index.html
     */
    public static function east(): string
    {
        return 'https://oauth.bitrix24.tech/';
    }

    /**
     * Return default OAUTH server for west region
     * @see https://apidocs.bitrix24.com/settings/oauth/index.html
     *
     * @return non-empty-string
     */
    public static function west(): string
    {
        return 'https://oauth.bitrix.info/';
    }

    /**
     * Get default OAuth server url
     * - find it in ENV-variable
     * - if not found will return server for west region
     *
     * @return non-empty-string
     */
    public static function default(): string
    {
        // try to find url in ENV-variable
        if (array_key_exists(self::BITRIX24_PHP_SDK_DEFAULT_AUTH_SERVER_URL, $_ENV) && !empty($_ENV[self::BITRIX24_PHP_SDK_DEFAULT_AUTH_SERVER_URL])) {
            $defaultOAuthServerUrl = (string)$_ENV[self::BITRIX24_PHP_SDK_DEFAULT_AUTH_SERVER_URL];
        } else {
            // default fallback
            $defaultOAuthServerUrl = self::west();
        }

        return $defaultOAuthServerUrl;
    }
}