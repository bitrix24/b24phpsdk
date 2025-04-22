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

namespace Bitrix24\SDK\Application\Workflows\Robots\Events;

use Bitrix24\SDK\Application\Workflows\Robots\Common\RobotMetadata;
use Symfony\Contracts\EventDispatcher\Event;

class RobotAdded extends Event
{
    public function __construct(protected RobotMetadata $robotMetadata)
    {
    }

    public function getRobotMetadata(): RobotMetadata
    {
        return $this->robotMetadata;
    }
}