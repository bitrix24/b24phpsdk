<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Services\Task;

use Bitrix24\SDK\Services\Task\Batch;
use Bitrix24\SDK\Tests\Unit\Services\BatchPaginationTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Batch::class)]
class BatchPaginationTest extends BatchPaginationTestCase
{
    protected const string BATCH_CLASS = Batch::class;
    protected const string API_METHOD = 'tasks.task.list';
    protected const string ENVELOPE = 'tasks';
    protected const string REQUEST_ID = 'ID';
}
