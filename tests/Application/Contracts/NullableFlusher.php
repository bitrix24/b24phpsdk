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

namespace Bitrix24\SDK\Tests\Application\Contracts;
class NullableFlusher implements TestRepositoryFlusherInterface
{
    /**
     * Flush changes to storage
     *
     * @see https://www.doctrine-project.org/projects/doctrine-orm/en/3.3/reference/working-with-objects.html
     */
    public function flush(): void
    {
    }
}