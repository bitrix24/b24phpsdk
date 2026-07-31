<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Mail\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;

/**
 * @property-read bool   $success
 * @property-read int    $movedCount
 * @property-read string $action
 */
class MoveToFolderItemResult extends AbstractAnnotatedItem
{
}
