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

namespace Bitrix24\SDK\Services\IM\Placements;

use Bitrix24\SDK\Services\Placement\ExtranetAvailability;
use Bitrix24\SDK\Services\Placement\PlacementOptionsInterface;
use Bitrix24\SDK\Services\Placement\Role;

abstract class AbstractPlacementOptions implements PlacementOptionsInterface
{
    /** @var array<string, scalar> */
    protected array $fields = [];

    public function context(ChatContext ...$chatContext): static
    {
        $this->fields['context'] = implode(';', array_map(
            static fn (ChatContext $chatContext): string => $chatContext->value,
            $chatContext,
        ));

        return $this;
    }

    public function role(Role $role): static
    {
        $this->fields['role'] = $role->value;

        return $this;
    }

    public function extranet(ExtranetAvailability $extranetAvailability): static
    {
        $this->fields['extranet'] = $extranetAvailability->value;

        return $this;
    }

    #[\Override]
    public function build(): array
    {
        return $this->fields;
    }
}
