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

namespace Bitrix24\SDK\Services\Placement\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Services\Placement\Common\PlacementHandlerMetadata;
use Bitrix24\SDK\Services\Placement\PlacementRegistrationStatus;

final readonly class PlacementRegistrationItemResult
{
    public function __construct(
        public PlacementHandlerMetadata $declaration,
        public PlacementRegistrationStatus $status,
        public ?BaseException $error = null,
        public ?BaseException $rollbackError = null,
        public ?bool $previousConfigurationRestored = null,
    ) {
    }
}
