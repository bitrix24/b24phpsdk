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

namespace Bitrix24\SDK\Core\Contracts;

/**
 * Interface for build data structure for add or update entity methods
 */
interface ItemBuilderInterface
{
    public function build(): array;

    public function withUserField(string $userField, mixed $value): self;
}