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

namespace Bitrix24\SDK\Services;

use Bitrix24\SDK\Core\Contracts\ItemBuilderInterface;

abstract class AbstractItemBuilder implements ItemBuilderInterface
{
    protected array $fields = [];

    public function build(): array
    {
        return array_unique($this->fields);
    }

    public function withUserField(string $userField, mixed $value): self
    {
        $this->fields[$userField] = $value;

        return $this;
    }
}