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

namespace Bitrix24\SDK\Services\Catalog\V3\Section\Service;

use Bitrix24\SDK\Filters\AbstractFilterBuilder;
use Bitrix24\SDK\Filters\Types\BoolFieldConditionBuilder;
use Bitrix24\SDK\Filters\Types\IntFieldConditionBuilder;
use Bitrix24\SDK\Filters\Types\StringFieldConditionBuilder;

/**
 * Typed filter for `catalog.section.list`; the required `iblockId` condition is added by Section::list().
 */
class SectionFilter extends AbstractFilterBuilder
{
    public function id(): IntFieldConditionBuilder
    {
        return new IntFieldConditionBuilder('id', $this);
    }

    public function iblockSectionId(): IntFieldConditionBuilder
    {
        return new IntFieldConditionBuilder('iblockSectionId', $this);
    }

    public function sort(): IntFieldConditionBuilder
    {
        return new IntFieldConditionBuilder('sort', $this);
    }

    public function name(): StringFieldConditionBuilder
    {
        return new StringFieldConditionBuilder('name', $this);
    }

    public function code(): StringFieldConditionBuilder
    {
        return new StringFieldConditionBuilder('code', $this);
    }

    public function xmlId(): StringFieldConditionBuilder
    {
        return new StringFieldConditionBuilder('xmlId', $this);
    }

    /**
     * Unlike other REST v3 catalog entities, sections filter `active` by the Y/N strings this builder emits.
     */
    public function active(): BoolFieldConditionBuilder
    {
        return new BoolFieldConditionBuilder('active', $this);
    }
}
