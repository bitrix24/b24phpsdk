<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Services\Sale\BasketItem;

use Bitrix24\SDK\Services\Sale\BasketItem\Batch;
use Bitrix24\SDK\Tests\Unit\Services\BatchPaginationTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Batch::class)]
class BatchPaginationTest extends BatchPaginationTestCase
{
    protected const string BATCH_CLASS = Batch::class;
    protected const string API_METHOD = 'sale.basketitem.list';
    protected const string ENVELOPE = 'basketItems';
}
