<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Bitrix24\SDK\Core\Credentials;

use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;

class Endpoints
{
    private readonly string $clientUrl;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        /**
         * @phpstan-param non-empty-string $clientUrl
         */
        string $clientUrl,
        /**
         * @phpstan-param non-empty-string|null $authServerUrl
         * @todo in v2 make it required
         */
        private readonly ?string $authServerUrl = null
    ) {
        // Normalize client URL - add https:// protocol if not present
        $this->clientUrl = $this->normalizeUrl($clientUrl);

        $this->validateUrl('clientUrl', $this->clientUrl);

        $this->validateUrl('BITRIX24_PHP_SDK_DEFAULT_AUTH_SERVER_URL', $authServerUrl);
    }

    /**
     * Normalize URL by adding https:// protocol if not present
     */
    private function normalizeUrl(string $url): string
    {
        $parseResult = parse_url($url);
        if (!array_key_exists('scheme', $parseResult)) {
            return 'https://' . $url;
        }

        return $url;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function changeClientUrl(string $clientUrl): self
    {
        return new self($clientUrl, $this->authServerUrl);
    }

    public function getClientUrl(): string
    {
        return $this->clientUrl;
    }

    public function getAuthServerUrl(): string
    {
        return $this->authServerUrl;
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function initFromArray(array $auth): self
    {
        if (!array_key_exists('client_endpoint', $auth)) {
            throw new InvalidArgumentException('field client_endpoint not found in array');
        }

        if (!array_key_exists('server_endpoint', $auth)) {
            throw new InvalidArgumentException('field server_endpoint not found in array');
        }

        return new self(
            (string)$auth['client_endpoint'],
            (string)$auth['server_endpoint'],
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    private function validateUrl(string $variableName, mixed $urlValue): void
    {
        if (filter_var($urlValue, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException(sprintf('%s endpoint URL «%s» is invalid', $variableName, $urlValue));
        }
    }
}
