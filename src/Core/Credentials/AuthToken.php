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

use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Symfony\Component\HttpFoundation;

readonly final class AuthToken
{
    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        /**
         * @phpstan-param non-empty-string $accessToken
         */
        public string  $accessToken,
        /**
         * @phpstan-param non-empty-string|null $refreshToken
         */
        public ?string $refreshToken,
        /**
         * @phpstan-param non-negative-int $expires
         */
        public int     $expires,
        /**
         * @phpstan-param non-negative-int|null $expiresIn
         */
        public ?int    $expiresIn = null)
    {
        if (trim($this->accessToken) === '') {
            throw new InvalidArgumentException('accessToken cannot be empty string');
        }

        if ($this->refreshToken !== null && trim($this->refreshToken) === '') {
            throw new InvalidArgumentException('refreshToken cannot be empty string');
        }
    }

    /**
     * Is this one-off token from event
     *
     * One-off tokens do not have refresh token field
     */
    public function isOneOff(): bool
    {
        return $this->refreshToken === null;
    }

    public function hasExpired(): bool
    {
        return $this->expires <= time();
    }

    /**
     * Initialize an AuthToken object from an array of token payload.
     *
     * @param array $authTokenPayload The array containing the token payload.
     *   - access_token (string): The access token.
     *   - refresh_token (string): The refresh token.
     *   - expires (int): The expiration timestamp of the token.
     *
     * @return self The initialized AuthToken object.
     *
     * @throws InvalidArgumentException If any of the required fields are not found in the authTokenPayload array.
     */
    public static function initFromArray(array $authTokenPayload): self
    {
        if (!array_key_exists('access_token', $authTokenPayload)) {
            throw new InvalidArgumentException('field access_token not fount in authTokenPayload');
        }

        if (!array_key_exists('refresh_token', $authTokenPayload)) {
            throw new InvalidArgumentException('field refresh_token not fount in authTokenPayload');
        }

        if (!array_key_exists('expires', $authTokenPayload)) {
            throw new InvalidArgumentException('field expires not fount in authTokenPayload');
        }

        return new self(
            (string)$authTokenPayload['access_token'],
            (string)$authTokenPayload['refresh_token'],
            (int)$authTokenPayload['expires']
        );
    }

    /**
     * Initializes an object of this class from a Workflow Request object.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request The Workflow Request object.
     *
     * @return self The initialized object of this class.
     */
    public static function initFromWorkflowRequest(HttpFoundation\Request $request): self
    {
        $requestFields = $request->request->all();
        return self::initFromArray($requestFields['auth']);
    }

    /**
     * Initializes the object from an event request.
     *
     * Parses the provided HTTP request and extracts the required fields to construct a new instance of the current class.
     * The required fields are 'access_token', 'expires', and 'expires_in'.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request The HTTP request object containing the required fields.
     *
     * @return self Returns a new instance of the current class with the extracted data.
     */
    public static function initFromEventRequest(HttpFoundation\Request $request): self
    {
        $requestFields = $request->request->all();
        return new self(
            $requestFields['auth']['access_token'],
            null,
            (int)$requestFields['auth']['expires'],
            (int)$requestFields['auth']['expires_in'],
        );
    }

    /**
     * Initializes an instance of the class from a placement request.
     *
     * @param HttpFoundation\Request $request The placement request object.
     * @return self The initialized instance of the class.
     * @throws InvalidArgumentException If the required fields are not found in the request.
     */
    public static function initFromPlacementRequest(HttpFoundation\Request $request): self
    {
        $requestFields = $request->request->all();
        if (!array_key_exists('AUTH_ID', $requestFields)) {
            throw new InvalidArgumentException('field AUTH_ID not fount in request');
        }

        if (!array_key_exists('REFRESH_ID', $requestFields)) {
            throw new InvalidArgumentException('field REFRESH_ID not fount in request');
        }

        if (!array_key_exists('AUTH_EXPIRES', $requestFields)) {
            throw new InvalidArgumentException('field AUTH_EXPIRES not fount in request');
        }

        return new self(
            (string)$request->request->get('AUTH_ID'),
            (string)$request->request->get('REFRESH_ID'),
            $request->request->getInt('AUTH_EXPIRES'),
        );
    }
}