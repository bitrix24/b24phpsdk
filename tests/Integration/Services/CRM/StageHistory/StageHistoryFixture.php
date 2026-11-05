<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\StageHistory;

use Bitrix24\SDK\Services\CRM\CRMServiceBuilder;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\TestCase;
use Throwable;

abstract class StageHistoryFixture extends TestCase
{
    protected static CRMServiceBuilder $crm;
    /** @var array<int, int> */
    protected static array $owners = [];
    private static ?int $smartTypeId = null;

    #[\Override]
    public static function setUpBeforeClass(): void
    {
        self::$crm = Factory::getServiceBuilder()->getCRMScope();
        $title = 'HISTORY-TEST_' . bin2hex(random_bytes(8));
        try {
            self::$owners[1] = self::$crm->lead()->add(['TITLE' => $title])->getId();
            self::$owners[2] = self::$crm->deal()->add(['TITLE' => $title])->getId();
            $type = self::$crm->type()->add($title, null, ['isStagesEnabled' => 'Y']);
            self::$smartTypeId = $type->getId();
            $entityTypeId = (int)$type->type()->entityTypeId;
            self::$owners[$entityTypeId] = (int)self::$crm->item()->add($entityTypeId, ['title' => $title])->item()->id;
        } catch (Throwable $exception) {
            self::tearDownAfterClass();
            throw $exception;
        }
    }

    #[\Override]
    public static function tearDownAfterClass(): void
    {
        try {
            foreach (self::$owners as $entityTypeId => $ownerId) {
                match ($entityTypeId) {
                    1 => self::$crm->lead()->delete($ownerId),
                    2 => self::$crm->deal()->delete($ownerId),
                    default => self::$crm->item()->delete($entityTypeId, $ownerId),
                };
            }
        } finally {
            self::$owners = [];
            if (self::$smartTypeId !== null) {
                self::$crm->type()->delete(self::$smartTypeId);
                self::$smartTypeId = null;
            }
        }
    }
}
