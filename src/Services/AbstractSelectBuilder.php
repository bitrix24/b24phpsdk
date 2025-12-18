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

use Bitrix24\SDK\Core\Contracts\SelectBuilderInterface;

abstract class AbstractSelectBuilder implements SelectBuilderInterface
{
    protected array $select = [];

    public function buildSelect(): array
    {
        return array_unique($this->select);
    }

    public function withUserFields(array $userFields): self
    {
        $this->select = array_merge($this->select, $userFields);
        return $this;
    }
}