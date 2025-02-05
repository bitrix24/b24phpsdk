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

namespace Bitrix24\SDK\Application\Workflows\Robots\Common;

use Bitrix24\SDK\Services\Workflows\Common\Auth;
use Bitrix24\SDK\Services\Workflows\Common\WorkflowDocumentId;
use Bitrix24\SDK\Services\Workflows\Common\WorkflowDocumentType;
use Bitrix24\SDK\Services\Workflows\Robot\Request\IncomingRobotRequest;

/**
 * DTO for store robot call arguments
 */
readonly class RobotRequest
{
    public function __construct(
        public string $workflowId,
        public string $code,
        public WorkflowDocumentId $workflowDocumentId,
        public WorkflowDocumentType $workflowDocumentType,
        public string $eventToken,
        public array $properties,
        public bool $isUseSubscription,
        public int $timeoutDuration,
        public int $timestamp,
        public Auth $auth
    ) {
    }

    /**
     * Create from incoming robot request
     *
     * @param IncomingRobotRequest $incomingRobotRequest
     * @return self
     */
    public function initFromIncomingRobotRequest(IncomingRobotRequest $incomingRobotRequest): self
    {
        return new self(
            $incomingRobotRequest->workflowId,
            $incomingRobotRequest->code,
            $incomingRobotRequest->workflowDocumentId,
            $incomingRobotRequest->workflowDocumentType,
            $incomingRobotRequest->eventToken,
            $incomingRobotRequest->properties,
            $incomingRobotRequest->isUseSubscription,
            $incomingRobotRequest->timeoutDuration,
            $incomingRobotRequest->timestamp,
            $incomingRobotRequest->auth
        );
    }
}