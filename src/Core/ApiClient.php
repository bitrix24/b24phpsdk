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

namespace Bitrix24\SDK\Core;

use Bitrix24\SDK\Core\Contracts\ApiClientInterface;
use Bitrix24\SDK\Core\Credentials\ApplicationProfile;
use Bitrix24\SDK\Core\Credentials\AuthToken;
use Bitrix24\SDK\Core\Credentials\Credentials;
use Bitrix24\SDK\Core\Credentials\WebhookUrl;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Response\DTO\RenewedAuthToken;
use Bitrix24\SDK\Infrastructure\HttpClient\RequestId\RequestIdGeneratorInterface;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class ApiClient implements ApiClientInterface
{
    /**
     * @const string
     */
    protected const BITRIX24_OAUTH_SERVER_URL = 'https://oauth.bitrix.info';

    /**
     * @const string
     */
    protected const SDK_VERSION = '1.3.0';

    protected const SDK_USER_AGENT = 'b24-php-sdk';

    /**
     * ApiClient constructor.
     */
    public function __construct(
        protected Credentials $credentials,
        protected HttpClientInterface $client,
        protected RequestIdGeneratorInterface $requestIdGenerator,
        protected ApiLevelErrorHandler $apiLevelErrorHandler,
        protected LoggerInterface $logger
    ) {
        $this->logger->debug(
            'ApiClient.init',
            [
                'httpClientType' => $this->client::class,
            ]
        );
    }

    /**
     * @return array<string,string>
     */
    protected function getDefaultHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'Accept-Charset' => 'utf-8',
            'User-Agent' => sprintf('%s-v-%s-php-%s', self::SDK_USER_AGENT, self::SDK_VERSION, PHP_VERSION),
            'X-BITRIX24-PHP-SDK-PHP-VERSION' => PHP_VERSION,
            'X-BITRIX24-PHP-SDK-VERSION' => self::SDK_VERSION,
        ];
    }

    public function getCredentials(): Credentials
    {
        return $this->credentials;
    }

    /**
     * @throws InvalidArgumentException
     * @throws TransportExceptionInterface
     * @throws TransportException
     */
    public function getNewAuthToken(): RenewedAuthToken
    {
        $requestId = $this->requestIdGenerator->getRequestId();
        $this->logger->debug('getNewAuthToken.start', [
            'requestId' => $requestId
        ]);
        if (!$this->getCredentials()->getApplicationProfile() instanceof ApplicationProfile) {
            throw new InvalidArgumentException('application profile not set');
        }

        if (!$this->getCredentials()->getAuthToken() instanceof AuthToken) {
            throw new InvalidArgumentException('access token in credentials not set');
        }

        $method = 'GET';
        $url = sprintf(
            '%s/oauth/token/?%s',
            $this::BITRIX24_OAUTH_SERVER_URL,
            http_build_query(
                [
                    'grant_type' => 'refresh_token',
                    'client_id' => $this->getCredentials()->getApplicationProfile()->clientId,
                    'client_secret' => $this->getCredentials()->getApplicationProfile()->clientSecret,
                    'refresh_token' => $this->getCredentials()->getAuthToken()->refreshToken,
                    $this->requestIdGenerator->getQueryStringParameterName() => $requestId
                ]
            )
        );

        $requestOptions = [
            'headers' => array_merge(
                $this->getDefaultHeaders(),
                [
                    $this->requestIdGenerator->getHeaderFieldName() => $requestId
                ]
            ),
        ];
        $response = $this->client->request($method, $url, $requestOptions);
        $responseData = $response->toArray(false);
        $this->logger->debug('getNewAuthToken.response', [
            'httpStatus' => $response->getStatusCode(),
            'responseData' => $responseData,
            'requestId' => $requestId
        ]);
        if ($response->getStatusCode() === StatusCodeInterface::STATUS_OK) {
            $this->apiLevelErrorHandler->handle($responseData);

            $newAuthToken = RenewedAuthToken::initFromArray($responseData);

            $this->logger->debug('getNewAuthToken.finish', [
                'requestId' => $requestId
            ]);
            return $newAuthToken;
        }

        if ($response->getStatusCode() === StatusCodeInterface::STATUS_BAD_REQUEST) {
            $this->logger->warning('getNewAuthToken.badRequest', [
                'url' => $url
            ]);
            throw new TransportException(sprintf('getting new access token failure: %s', $responseData['error']));
        }

        throw new TransportException(
            'getting new access token failure with unknown http-status code %s',
            $response->getStatusCode()
        );
    }

    /**
     * @param array<mixed> $parameters
     *
     * @throws TransportExceptionInterface
     * @throws InvalidArgumentException
     */
    public function getResponse(string $apiMethod, array $parameters = []): ResponseInterface
    {
        $requestId = $this->requestIdGenerator->getRequestId();
        $this->logger->info(
            'getResponse.start',
            [
                'apiMethod' => $apiMethod,
                'domainUrl' => $this->credentials->getDomainUrl(),
                'parameters' => $parameters,
                'requestId' => $requestId
            ]
        );
        $apiMethod = strtolower($apiMethod);
        $method = 'POST';
        if ($this->getCredentials()->getWebhookUrl() instanceof WebhookUrl) {
            $url = sprintf('%s/%s/', $this->getCredentials()->getWebhookUrl()->getUrl(), $apiMethod);
        } else {
            // all api calls work with current portal and credentials related with this portal,
            // portal url stored in credentials, but if we work with on-premise installation we can't trust tokens from portal placement or portal event
            // we must make sure that the token is alive that the token corresponds to the portal,
            // "from which" came a request, and that the token corresponds to our application.
            // that's why we call app.info on OAUTH server
            if (($apiMethod === 'app.info') && array_key_exists(
                    'IS_NEED_OAUTH_SECURE_CHECK',
                    $parameters
                ) && $parameters['IS_NEED_OAUTH_SECURE_CHECK']) {
                // call method on vendor OAUTH server
                $url = sprintf('%s/rest/%s', self::BITRIX24_OAUTH_SERVER_URL, $apiMethod);
            } else {
                // work with portal
                $url = sprintf('%s/rest/%s', $this->getCredentials()->getDomainUrl(), $apiMethod);
            }

            if (!$this->getCredentials()->getAuthToken() instanceof AuthToken) {
                throw new InvalidArgumentException('access token in credentials not found ');
            }

            $parameters['auth'] = $this->getCredentials()->getAuthToken()->accessToken;
        }

        // todo must be fixed by vendor in API v2
        // duplicate request id in query string for current version of bitrix24 api
        // vendor don't use request id from headers =(

        // todo must be fixed by vendor in API v2
        // part of endpoints required strict order of arguments
        $strictApiMethods = [
            'task.commentitem.add',
            'task.commentitem.getlist',
            'task.commentitem.update',
            'task.commentitem.getlist',
            'task.commentitem.delete',
            'task.commentitem.isactionallowed',
            'task.elapseditem.add',
            'task.elapseditem.update',
            'task.elapseditem.get',
            'task.elapseditem.getlist',
            'task.elapseditem.delete',
            'task.elapseditem.isactionallowed',
            'task.elapseditem.getmanifest',
            'tasks.task.add',
        ];
        if (!in_array($apiMethod, $strictApiMethods, true)) {
            $url .= '?' . $this->requestIdGenerator->getQueryStringParameterName() . '=' . $requestId;
        }

        $requestOptions = [
            'json' => $parameters,
            'headers' => array_merge(
                $this->getDefaultHeaders(),
                [
                    $this->requestIdGenerator->getHeaderFieldName() => $requestId
                ]
            ),
            // disable redirects, try to catch portal change domain name event
            'max_redirects' => 0,
        ];
        $this->logger->debug('getResponse.requestPayload', [
            'method' => $method,
            'url' => $url,
            'requestOptions' => $requestOptions
        ]);
        $response = $this->client->request($method, $url, $requestOptions);

        $this->logger->info(
            'getResponse.end',
            [
                'apiMethod' => $apiMethod,
                'responseInfo' => $response->getInfo(),
                'requestId' => $requestId
            ]
        );

        return $response;
    }
}
