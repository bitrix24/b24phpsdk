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

final readonly class PlacementLangItem
{
    public function __construct(
        public string $title,
        public ?string $description = null,
        public ?string $groupName = null,
    ) {
    }

    /**
     * @return array{TITLE: string, DESCRIPTION?: string, GROUP_NAME?: string}
     */
    public function toArray(): array
    {
        $result = [
            'TITLE' => $this->title,
        ];

        if ($this->description !== null) {
            $result['DESCRIPTION'] = $this->description;
        }

        if ($this->groupName !== null) {
            $result['GROUP_NAME'] = $this->groupName;
        }

        return $result;
    }
}
