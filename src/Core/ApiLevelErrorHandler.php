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
use Bitrix24\SDK\Core\Exceptions\ItemNotFoundException;
use Bitrix24\SDK\Core\Exceptions\MethodNotFoundException;
use Bitrix24\SDK\Core\Exceptions\OperationTimeLimitExceededException;
use Bitrix24\SDK\Core\Exceptions\PaymentRequiredException;
use Bitrix24\SDK\Core\Exceptions\QueryLimitExceededException;
use Bitrix24\SDK\Core\Exceptions\UserNotFoundOrIsNotActiveException;
use Bitrix24\SDK\Core\Exceptions\WrongAuthTypeException;
use Bitrix24\SDK\Core\Exceptions\WrongClientException;
use Bitrix24\SDK\Services\Workflows\Exceptions\ActivityOrRobotAlreadyInstalledException;
use Bitrix24\SDK\Services\Workflows\Exceptions\ActivityOrRobotValidationFailureException;
use Bitrix24\SDK\Services\Workflows\Exceptions\WorkflowTaskAlreadyCompletedException;
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
            case 'error_not_found':
                throw new ItemNotFoundException(sprintf('%s - %s', $errorCode, $errorDescription));
            default:
                throw new BaseException(sprintf('%s - %s %s', $errorCode, $errorDescription, $batchErrorPrefix));
        }
    }
}