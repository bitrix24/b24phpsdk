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

use Bitrix24\SDK\Core\Exceptions\AuthForbiddenException;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\InvalidGrantException;
use Bitrix24\SDK\Core\Exceptions\ItemNotFoundException;
use Bitrix24\SDK\Core\Exceptions\MethodNotFoundException;
use Bitrix24\SDK\Core\Exceptions\OperationTimeLimitExceededException;
use Bitrix24\SDK\Core\Exceptions\PaymentRequiredException;
use Bitrix24\SDK\Core\Exceptions\PortalDomainNotFoundException;
use Bitrix24\SDK\Core\Exceptions\QueryLimitExceededException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Exceptions\UserNotFoundOrIsNotActiveException;
use Bitrix24\SDK\Core\Exceptions\WrongAuthTypeException;
use Bitrix24\SDK\Core\Exceptions\WrongClientException;
use Bitrix24\SDK\Services\Workflows\Exceptions\ActivityOrRobotAlreadyInstalledException;
use Bitrix24\SDK\Services\Workflows\Exceptions\ActivityOrRobotValidationFailureException;
use Bitrix24\SDK\Services\Workflows\Exceptions\WorkflowTaskAlreadyCompletedException;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Log\LoggerInterface;

/**
 * Handle api-level errors and throw related exception
 */
class ApiLevelErrorHandler
{
    protected const ERROR_KEY = 'error';

    protected const RESULT_KEY = 'result';

    protected const RESULT_ERROR_KEY = 'result_error';

    protected const ERROR_DESCRIPTION_KEY = 'error_description';

    /**
     * ApiLevelErrorHandler constructor.
     */
    public function __construct(protected LoggerInterface $logger)
    {
    }

    /**
     * @param array<string, mixed> $responseBody
     *
     * @throws QueryLimitExceededException
     * @throws BaseException
     */
    public function handle(array $responseBody): void
    {
        // single query error response
        if (array_key_exists(self::ERROR_KEY, $responseBody) && array_key_exists(self::ERROR_DESCRIPTION_KEY, $responseBody)) {
            $this->handleError($responseBody);
        }

        // error in refresh token request
        if (array_key_exists(self::ERROR_KEY, $responseBody) && !array_key_exists(self::RESULT_KEY, $responseBody)) {
            $this->handleError($responseBody);
        }

        // error in batch response
        if (!array_key_exists(self::RESULT_KEY, $responseBody) || (!is_array($responseBody[self::RESULT_KEY]))) {
            return;
        }

        if (array_key_exists(self::RESULT_ERROR_KEY, $responseBody[self::RESULT_KEY])) {
            foreach ($responseBody[self::RESULT_KEY][self::RESULT_ERROR_KEY] as $cmdId => $errorData) {
                $this->handleError($errorData, $cmdId);
            }
        }
    }

    /**
     * @throws MethodNotFoundException
     * @throws QueryLimitExceededException
     * @throws BaseException
     */
    private function handleError(array $responseBody, ?string $batchCommandId = null): void
    {
        $errorCode = strtolower(trim((string)$responseBody[self::ERROR_KEY]));
        $errorDescription = array_key_exists(self::ERROR_DESCRIPTION_KEY, $responseBody) ? strtolower(trim((string)$responseBody[self::ERROR_DESCRIPTION_KEY])) : null;

        $this->logger->debug(
            'handle.errorInformation',
            [
                'errorCode' => $errorCode,
                'errorDescription' => $errorDescription,
            ]
        );

        $batchErrorPrefix = '';
        if ($batchCommandId !== null) {
            $batchErrorPrefix = sprintf(' batch command id: %s', $batchCommandId);
        }

        // todo send issues to bitrix24
        // fix errors without error_code responses
        if ($errorCode === '' && strtolower((string) $errorDescription) === strtolower('You can delete ONLY templates created by current application')) {
            $errorCode = 'bizproc_workflow_template_access_denied';
        }

        if ($errorCode === '' && strtolower((string) $errorDescription) === strtolower('No fields to update.')) {
            $errorCode = 'bad_request_no_fields_to_update';
        }

        if ($errorCode === '' && strtolower((string) $errorDescription) === strtolower('User is not found or is not active')) {
            $errorCode = 'user_not_found_or_is_not_active';
        }

        // crm.requisite.get
        if ($errorCode === '' && str_contains(strtolower((string)$errorDescription),'not found')) {
            $errorCode = 'error_not_found';
        }

        // todo check errors
        // EXPIRED_TOKEN
        // ERROR_OAUTH
        // ERROR_METHOD_NOT_FOUND
        // INVALID_TOKEN
        // INVALID_GRANT
        // NO_AUTH_FOUND
        // INSUFFICIENT_SCOPE

        switch ($errorCode) {
            case 'error_task_completed':
                throw new WorkflowTaskAlreadyCompletedException(sprintf('%s - %s', $errorCode, $errorDescription));
            case 'bad_request_no_fields_to_update':
                throw new InvalidArgumentException(sprintf('%s - %s', $errorCode, $errorDescription));
            case 'access_denied':
            case 'bizproc_workflow_template_access_denied':
                throw new AuthForbiddenException(sprintf('%s - %s', $errorCode, $errorDescription));
            case 'query_limit_exceeded':
                throw new QueryLimitExceededException(sprintf('query limit exceeded - too many requests %s', $batchErrorPrefix));
            case 'error_method_not_found':
                throw new MethodNotFoundException(sprintf('api method not found %s %s', $errorDescription, $batchErrorPrefix));
            case 'operation_time_limit':
                throw new OperationTimeLimitExceededException(sprintf('operation time limit exceeded %s %s', $errorDescription, $batchErrorPrefix));
            case 'error_activity_already_installed':
                throw new ActivityOrRobotAlreadyInstalledException(sprintf('%s - %s', $errorCode, $errorDescription));
            case 'error_activity_validation_failure':
                throw new ActivityOrRobotValidationFailureException(sprintf('%s - %s', $errorCode, $errorDescription));
            case 'user_not_found_or_is_not_active':
                throw new UserNotFoundOrIsNotActiveException(sprintf('%s - %s', $errorCode, $errorDescription));
            case 'wrong_auth_type':
                throw new WrongAuthTypeException(sprintf('%s - %s', $errorCode, $errorDescription));
            case 'payment_required':
                throw new PaymentRequiredException(sprintf('%s - %s', $errorCode, $errorDescription));
            case 'wrong_client':
                throw new WrongClientException(sprintf('%s - %s', $errorCode, $errorDescription));
            case 'not_found':
            case 'error_not_found':
                throw new ItemNotFoundException(sprintf('%s - %s', $errorCode, $errorDescription));
            default:
                throw new BaseException(sprintf('%s - %s %s', $errorCode, $errorDescription, $batchErrorPrefix));
        }
    }

    /**
     * Handle OAuth token refresh errors and throw appropriate exceptions
     *
     * @param int $httpStatusCode HTTP status code from OAuth server
     * @param array<string, mixed> $responseBody Response body with error details
     * @param string $requestUrl Original request URL for logging
     *
     *
     * @throws InvalidGrantException When refresh token is invalid or expired
     * @throws WrongClientException When client credentials are invalid
     * @throws PortalDomainNotFoundException When portal domain is not found
     * @throws TransportException For server errors and other issues
     */
    public function handleOAuthError(int $httpStatusCode, array $responseBody, string $requestUrl): never
    {
        $errorCode = strtolower(trim((string)($responseBody[self::ERROR_KEY] ?? '')));
        $errorDescription = strtolower(trim((string)($responseBody[self::ERROR_DESCRIPTION_KEY] ?? '')));

        $this->logger->debug(
            'handleOAuthError.errorInformation',
            [
                'httpStatusCode' => $httpStatusCode,
                'errorCode' => $errorCode,
                'errorDescription' => $errorDescription,
                'requestUrl' => $requestUrl,
            ]
        );

        switch ($httpStatusCode) {
            case StatusCodeInterface::STATUS_BAD_REQUEST:
                $this->logger->warning('handleOAuthError.badRequest', [
                    'url' => $requestUrl,
                    'error' => $responseBody[self::ERROR_KEY] ?? 'unknown',
                    'error_description' => $responseBody[self::ERROR_DESCRIPTION_KEY] ?? 'no description'
                ]);

                // Handle specific OAuth error codes
                switch ($errorCode) {
                    case 'invalid_grant':
                    case 'bad_verification_code':
                        throw new InvalidGrantException(
                            sprintf(
                                'OAuth refresh token is invalid or expired (error: %s, description: %s). User re-authorization required.',
                                $responseBody[self::ERROR_KEY] ?? 'unknown',
                                $responseBody[self::ERROR_DESCRIPTION_KEY] ?? 'no description'
                            )
                        );

                    default:
                        // Check if error is related to portal domain
                        if (str_contains($errorDescription, 'portal') ||
                            str_contains($errorDescription, 'domain') ||
                            str_contains($errorDescription, 'not found')) {
                            throw new PortalDomainNotFoundException(
                                sprintf(
                                    'Bitrix24 portal domain not found or inaccessible (error: %s, description: %s)',
                                    $responseBody[self::ERROR_KEY] ?? 'unknown',
                                    $responseBody[self::ERROR_DESCRIPTION_KEY] ?? 'no description'
                                )
                            );
                        }

                        // Generic bad request error
                        throw new TransportException(
                            sprintf(
                                'Getting new access token failure (error: %s, description: %s)',
                                $responseBody[self::ERROR_KEY] ?? 'unknown',
                                $responseBody[self::ERROR_DESCRIPTION_KEY] ?? 'no description'
                            )
                        );
                }

            case StatusCodeInterface::STATUS_UNAUTHORIZED:
                $this->logger->warning('handleOAuthError.unauthorized', [
                    'url' => $requestUrl,
                    'error' => $responseBody[self::ERROR_KEY] ?? 'unknown'
                ]);

                if ($errorCode === 'invalid_client') {
                    throw new WrongClientException(
                        sprintf(
                            'OAuth client credentials are invalid (client_id or client_secret is wrong). Error: %s, description: %s',
                            $responseBody[self::ERROR_KEY] ?? 'unknown',
                            $responseBody[self::ERROR_DESCRIPTION_KEY] ?? 'no description'
                        )
                    );
                }

                throw new TransportException(
                    sprintf(
                        'Getting new access token failure: unauthorized (error: %s, description: %s)',
                        $responseBody[self::ERROR_KEY] ?? 'unknown',
                        $responseBody[self::ERROR_DESCRIPTION_KEY] ?? 'no description'
                    )
                );

            case StatusCodeInterface::STATUS_NOT_FOUND:
                $this->logger->warning('handleOAuthError.notFound', [
                    'url' => $requestUrl
                ]);

                throw new PortalDomainNotFoundException(
                    'Bitrix24 portal domain not found (HTTP 404). Portal may not exist or has been deleted.'
                );

            case StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR:
            case StatusCodeInterface::STATUS_BAD_GATEWAY:
            case StatusCodeInterface::STATUS_SERVICE_UNAVAILABLE:
            case StatusCodeInterface::STATUS_GATEWAY_TIMEOUT:
                $this->logger->error('handleOAuthError.serverError', [
                    'url' => $requestUrl,
                    'httpStatus' => $httpStatusCode,
                    'responseData' => $responseBody
                ]);

                throw new TransportException(
                    sprintf(
                        'OAuth server error (HTTP %s). Please retry later.',
                        $httpStatusCode
                    )
                );

            default:
                $this->logger->error('handleOAuthError.unknownHttpStatus', [
                    'url' => $requestUrl,
                    'httpStatus' => $httpStatusCode,
                    'responseData' => $responseBody
                ]);

                throw new TransportException(
                    sprintf(
                        'Getting new access token failure with unknown HTTP status code %s',
                        $httpStatusCode
                    )
                );
        }
    }
}