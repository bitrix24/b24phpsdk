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

use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Credentials\Credentials;
use Bitrix24\SDK\Core\Credentials\WebhookUrl;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Infrastructure\HttpClient\RequestId\RequestIdGeneratorInterface;
use Psr\Log\LoggerInterface;

/**
 * Class EndpointUrlFormatter
 *
 * Responsible for formatting URLs via some vendor rules
 *
 * Rules:
 * For the current api version: 1
 * - Some api-methods are case-sensitive and must be in lower case
 * - Some api-methods are sensitive for parameters order and must not contain additional parameters - request id in a query string
 *
 * For the api version: 3
 * - Added api prefix in URL
 *
 */
readonly class EndpointUrlFormatter
{
    public function __construct(
        private RequestIdGeneratorInterface $requestIdGenerator,
        private LoggerInterface $logger
    ) {
    }

    /**
     * Formats the API request URL based on the provided credentials, method, parameters, and request ID.
     *
     * @param Credentials $credentials The credentials object containing authentication and endpoint information.
     * @param non-empty-string $apiMethod The API method to be called, which may require case sensitivity adjustment.
     * @param array $parameters The parameters to be sent with the API request.
     * @param non-empty-string $requestId A unique identifier for the request, added as a query string in certain cases.
     * @return non-empty-string The formatted API URL for the request.
     * @throws InvalidArgumentException
     */
    public function format(
        ApiVersion $apiVersion,
        Credentials $credentials,
        string $apiMethod,
        array $parameters,
        string $requestId
    ): string {
        $this->logger->debug('EndpointUrlFormatter.format.start', [
            'apiMethod' => $apiMethod,
            'version' => $apiVersion->value,
        ]);

        //todo remove after vendor fix
        $caseSensitiveMethods = [
            'tasks.flow.Flow.create',
            'tasks.flow.Flow.update',
            'tasks.flow.Flow.delete',
            'tasks.flow.Flow.get',
            'tasks.flow.Flow.isExists',
            'tasks.flow.Flow.activate',
            'tasks.flow.Flow.pin',
        ];
        if (!in_array($apiMethod, $caseSensitiveMethods, true)) {
            $apiMethod = strtolower($apiMethod);
        }

        if ($credentials->getWebhookUrl() instanceof WebhookUrl) {
            if ($apiVersion->isV3()) {
                $urlParts = parse_url($credentials->getWebhookUrl()->getUrl());
                $urlParts['path'] = str_replace('/rest', '/rest/api', $urlParts['path']);
                $finalPath = str_replace('//', '/', sprintf('%s/%s', $urlParts['path'], $apiMethod));
                $url = sprintf('%s://%s%s/', $urlParts['scheme'], $urlParts['host'], $finalPath);
            } else {
                $url = sprintf('%s/%s/', $credentials->getWebhookUrl()->getUrl(), $apiMethod);
            }
        } elseif (($apiMethod === 'app.info') && array_key_exists(
            'IS_NEED_OAUTH_SECURE_CHECK',
            $parameters
        ) && $parameters['IS_NEED_OAUTH_SECURE_CHECK']) {
            // all api calls work with current portal and credentials related with this portal,
            // portal url stored in credentials, but if we work with on-premise installation we can't trust tokens from portal placement or portal event
            // we must make sure that the token is alive that the token corresponds to the portal,
            // "from which" came a request, and that the token corresponds to our application.
            // that's why we call app.info on OAUTH server
            // call method on vendor OAUTH server
            $url = sprintf('%s/rest/%s', $credentials->getEndpoints()->getAuthServerUrl(), $apiMethod);
        } elseif ($apiVersion->isV3()) {
            // work with portal
            $url = sprintf('%s/rest/api/%s', $credentials->getDomainUrl(), $apiMethod);
        } else {
            $url = sprintf('%s/rest/%s', $credentials->getDomainUrl(), $apiMethod);
        }

        // todo must be fixed by vendor in API v3
        // duplicate request id in query string for current version of bitrix24 api
        // vendor don't use request id from headers =(
        // part of endpoints required strict order of arguments
        $strictApiMethods = [
            'task.checklistitem.add',
            'task.checklistitem.update',
            'task.checklistitem.getlist',
            'task.checklistitem.get',
            'task.checklistitem.delete',
            'task.checklistitem.moveafteritem',
            'task.checklistitem.complete',
            'task.checklistitem.renew',
            'task.checklistitem.isactionallowed',
            'task.commentitem.add',
            'task.commentitem.get',
            'task.commentitem.getlist',
            'task.commentitem.update',
            'task.commentitem.delete',
            'task.commentitem.isactionallowed',
            'task.elapseditem.add',
            'task.elapseditem.update',
            'task.elapseditem.get',
            'task.elapseditem.getlist',
            'task.elapseditem.delete',
            'task.elapseditem.isactionallowed',
            'task.elapseditem.getmanifest',
            'task.item.userfield.add',
            'task.item.userfield.delete',
            'task.item.userfield.list',
            'task.item.userfield.get',
            'task.item.userfield.update',
            'tasks.task.add',
        ];
        if (!in_array($apiMethod, $strictApiMethods, true)) {
            $url .= '?' . $this->requestIdGenerator->getQueryStringParameterName() . '=' . $requestId;
        }

        $this->logger->debug('EndpointUrlFormatter.format.finish', [
            'url' => $url
        ]);
        return $url;
    }
}
