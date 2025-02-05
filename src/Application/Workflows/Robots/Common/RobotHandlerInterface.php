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

use Bitrix24\SDK\Services\Workflows\Robot\Request\IncomingRobotRequest;

interface RobotHandlerInterface
{
    /**
     * Process robot request
     *
     * @param RobotRequest $robotRequest
     * @return RobotResponse
     */
    public function handle(RobotRequest $robotRequest): RobotResponse;

    /**
     * Get robot metadata
     *
     * @return RobotMetadata
     */
    public function getMetadata(): RobotMetadata;
}