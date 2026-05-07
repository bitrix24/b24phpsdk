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

namespace Bitrix24\SDK\Attributes;

use Attribute;

/**
 * Links a *ItemResult class to its OpenAPI v3 entity key and related builder classes.
 *
 * Usage:
 *
 *   #[OpenApiEntity(
 *       entityKey:     'bitrix.tasks.taskdto',
 *       selectBuilder: TaskItemSelectBuilder::class,
 *       itemBuilder:   TaskItemBuilder::class,
 *   )]
 *   class TaskItemResult extends AbstractItem { ... }
 *
 * - entityKey:     key from components.schemas in docs/open-api/openapi.json
 * - selectBuilder: class that builds the select[] array for get/list calls (nullable until created)
 * - itemBuilder:   class that builds the fields[] array for add/update calls (nullable until created)
 */
#[Attribute(Attribute::TARGET_CLASS)]
readonly class OpenApiEntity
{
    public function __construct(
        public string  $entityKey,
        public ?string $selectBuilder = null,
        public ?string $itemBuilder = null,
    ) {
    }
}
