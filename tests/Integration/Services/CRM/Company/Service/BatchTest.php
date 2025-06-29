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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Company\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\CRM\Company\Service\Batch;
use Bitrix24\SDK\Services\CRM\Company\Service\Company;
use Bitrix24\SDK\Services\CRM\Deal\Service\Deal;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

#[CoversClass(Batch::class)]
#[CoversMethod(Batch::class, 'list')]
#[CoversMethod(Batch::class, 'add')]
#[CoversMethod(Batch::class, 'delete')]
#[CoversMethod(Batch::class, 'update')]
class BatchTest extends TestCase
{
    private ServiceBuilder $sb;

    private array $createdCompanies = [];

    protected function setUp(): void
    {
        $this->sb = Fabric::getServiceBuilder();
    }

    protected function tearDown(): void
    {
    }

    public function testBatchList(): void
    {
        $newCompaniesCount = 60;
        $utmSource = Uuid::v7()->toRfc4122();
        $companies = [];
        for ($i = 1; $i <= $newCompaniesCount; $i++) {
            $companies[] = ['TITLE' => 'TITLE-' . sprintf('Acme Inc - %s', time()), 'UTM_SOURCE' => $utmSource];
        }

        $cnt = 0;
        foreach ($this->sb->getCRMScope()->company()->batch->add($companies) as $item) {
            $this->createdCompanies[] = $item->getId();
            $cnt++;
        }

        $addedCompanies = [];
        foreach (
            $this->sb->getCRMScope()->company()->batch->list([], ['UTM_SOURCE' => $utmSource], ['ID', 'TITLE']) as $item
        ) {
            $addedCompanies[] = $item->ID;
        }

        $this->assertEquals($newCompaniesCount, count($addedCompanies));
    }

    public function testBatchAdd(): void
    {
        $newCompaniesCount = 60;
        $utmSource = Uuid::v7()->toRfc4122();
        $companies = [];
        for ($i = 1; $i <= $newCompaniesCount; $i++) {
            $companies[] = ['TITLE' => 'TITLE-' . sprintf('Acme Inc - %s', time()), 'UTM_SOURCE' => $utmSource];
        }

        $newCompanies = [];
        foreach ($this->sb->getCRMScope()->company()->batch->add($companies) as $item) {
            $this->createdCompanies[] = $item->getId();
            $newCompanies[] = $item->getId();
        }

        foreach (
            $this->sb->getCRMScope()->company()->batch->list([], ['UTM_SOURCE' => $utmSource], ['ID', 'TITLE']) as $item
        ) {
            $addedCompanies[] = $item->ID;
        }

        $this->assertEquals($newCompanies, $addedCompanies);
    }

    public function testBatchDelete(): void
    {
        $newCompaniesCount = 60;
        $utmSource = Uuid::v7()->toRfc4122();
        $companies = [];
        for ($i = 1; $i <= $newCompaniesCount; $i++) {
            $companies[] = ['TITLE' => 'TITLE-' . sprintf('Acme Inc - %s', time()), 'UTM_SOURCE' => $utmSource];
        }

        $newCompanies = [];
        foreach ($this->sb->getCRMScope()->company()->batch->add($companies) as $item) {
            $newCompanies[] = $item->getId();
        }

        $deletedCnt = 0;
        foreach ($this->sb->getCRMScope()->company()->batch->delete($newCompanies) as $result) {
            $this->assertTrue($result->isSuccess());
            $deletedCnt++;
        }

        $this->assertEquals($newCompaniesCount, $deletedCnt);
        $this->assertEquals(0, $this->sb->getCRMScope()->company()->countByFilter(['UTM_SOURCE' => $utmSource]));
    }

    public function testBatchUpdate(): void
    {
        $newCompaniesCount = 60;
        $utmSource = Uuid::v7()->toRfc4122();
        $companies = [];
        for ($i = 1; $i <= $newCompaniesCount; $i++) {
            $companies[] = ['TITLE' => 'TITLE-' . sprintf('Acme Inc - %s', time()), 'UTM_SOURCE' => $utmSource];
        }

        $newCompanies = [];
        foreach ($this->sb->getCRMScope()->company()->batch->add($companies) as $item) {
            $this->createdCompanies[] = $item->getId();
            $newCompanies[] = $item->getId();
        }


        $toUpdate = [];
        $result = [];
        foreach ($this->sb->getCRMScope()->company()->batch->list([], ['ID' => $newCompanies], ['ID', 'TITLE', 'COMMENTS']) as $item) {
            $opportunity = random_int(100, 10000);
            $toUpdate[$item->ID] = [
                'fields' => [
                    'COMMENTS' => $opportunity,
                ],
                'params' => [],
            ];
            $result[$item->ID] = $opportunity;
        }

        // update
        foreach ($this->sb->getCRMScope()->company()->batch->update($toUpdate) as $updateResult) {
            $this->assertTrue($updateResult->isSuccess());
        }

        // list
        $updateResult = [];
        foreach ($this->sb->getCRMScope()->company()->batch->list([], ['ID' => $newCompanies], ['ID', 'TITLE', 'COMMENTS']) as $item) {
            $updateResult[$item->ID] = $item->COMMENTS;
        }

        $this->assertEquals($result, $updateResult);
    }
}