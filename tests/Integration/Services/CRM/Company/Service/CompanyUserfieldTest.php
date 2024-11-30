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


use Bitrix24\SDK\Services\CRM\Company\Service\CompanyUserfield;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Tests\Builders\Services\CRM\Userfield\SystemUserfieldBuilder;
use Bitrix24\SDK\Tests\Integration\Fabric;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Bitrix24\SDK\Core;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;

#[CoversClass(CompanyUserfield::class)]
#[CoversMethod(CompanyUserfield::class, 'add')]
#[CoversMethod(CompanyUserfield::class, 'get')]
#[CoversMethod(CompanyUserfield::class, 'list')]
#[CoversMethod(CompanyUserfield::class, 'delete')]
#[CoversMethod(CompanyUserfield::class, 'update')]
class CompanyUserfieldTest extends TestCase
{
    use CustomBitrix24Assertions;

    private ServiceBuilder $sb;

    private array $createdCompanies = [];
    private array $createdUserfields = [];

    public function setUp(): void
    {
        $this->sb = Fabric::getServiceBuilder();
    }

    public function tearDown(): void
    {
        foreach ($this->sb->getCRMScope()->company()->batch->delete($this->createdCompanies) as $result) {
        }

        foreach ($this->createdUserfields as $fieldId) {
            $this->sb->getCRMScope()->companyUserfield()->delete($fieldId);
        }
    }

    public static function systemUserfieldsDemoDataDataProvider(): Generator
    {
        yield 'user type id string' => [(new SystemUserfieldBuilder())->build()];
    }

    #[TestDox('crm.company.userfield.add')]
    #[DataProvider('systemUserfieldsDemoDataDataProvider')]
    public function testCompanyUserfieldAdd(array $uf): void
    {
        $fieldId = $this->sb->getCRMScope()->companyUserfield()->add($uf)->getId();
        $this->createdUserfields[] = $fieldId;
        $addedField = $this->sb->getCRMScope()->companyUserfield()->get($fieldId)->userfieldItem();

        $this->assertTrue(str_contains($addedField->FIELD_NAME, $uf['FIELD_NAME']));
        $this->assertEquals($uf['USER_TYPE_ID'], $addedField->USER_TYPE_ID);
        $this->assertEquals($uf['XML_ID'], $addedField->XML_ID);
    }

    #[TestDox('crm.company.userfield.get')]
    #[DataProvider('systemUserfieldsDemoDataDataProvider')]
    public function testCompanyUserfieldGet(array $uf): void
    {
        $fieldId = $this->sb->getCRMScope()->companyUserfield()->add($uf)->getId();
        $addedField = $this->sb->getCRMScope()->companyUserfield()->get($fieldId)->userfieldItem();

        $this->assertTrue(str_contains($addedField->FIELD_NAME, $uf['FIELD_NAME']));
        $this->assertEquals($uf['USER_TYPE_ID'], $addedField->USER_TYPE_ID);
        $this->assertEquals($uf['XML_ID'], $addedField->XML_ID);
    }

    #[TestDox('crm.company.userfield.list')]
    public function testCompanyUserfieldList(): void
    {
        $newFields[] = (new SystemUserfieldBuilder())->build();
        $newFields[] = (new SystemUserfieldBuilder('integer'))->build();

        foreach ($newFields as $field) {
            $addedResult = $this->sb->getCRMScope()->companyUserfield()->add($field);
            $this->createdUserfields[] = $addedResult->getId();
        }

        $fields = $this->sb->getCRMScope()->companyUserfield()->list();
        $this->assertGreaterThanOrEqual(2, $fields->getUserfields());
    }

    #[TestDox('crm.company.userfield.delete')]
    #[DataProvider('systemUserfieldsDemoDataDataProvider')]
    public function testCompanyUserfieldDelete(array $uf): void
    {
        $fieldId = $this->sb->getCRMScope()->companyUserfield()->add($uf)->getId();
        $addedField = $this->sb->getCRMScope()->companyUserfield()->get($fieldId)->userfieldItem();
        $this->assertTrue(str_contains($addedField->FIELD_NAME, $uf['FIELD_NAME']));

        $this->assertTrue($this->sb->getCRMScope()->companyUserfield()->delete($fieldId)->isSuccess());

        $this->expectException(Core\Exceptions\ItemNotFoundException::class);
        $this->sb->getCRMScope()->companyUserfield()->delete($fieldId);
    }

    #[TestDox('crm.company.userfield.add')]
    #[DataProvider('systemUserfieldsDemoDataDataProvider')]
    public function testCompanyUserfieldUpdate(array $uf): void
    {
        $fieldId = $this->sb->getCRMScope()->companyUserfield()->add($uf)->getId();
        $this->createdUserfields[] = $fieldId;
        $addedField = $this->sb->getCRMScope()->companyUserfield()->get($fieldId)->userfieldItem();

        $this->assertTrue(str_contains($addedField->FIELD_NAME, $uf['FIELD_NAME']));
        $this->assertEquals($uf['USER_TYPE_ID'], $addedField->USER_TYPE_ID);
        $this->assertEquals($uf['XML_ID'], $addedField->XML_ID);

        $newXmlId = 'new' . $addedField->XML_ID;

        $this->assertTrue(
            $this->sb->getCRMScope()->companyUserfield()->update(
                $fieldId,
                [
                    'XML_ID' => $newXmlId,
                ]
            )->isSuccess()
        );

        $this->assertEquals(
            $newXmlId,
            $this->sb->getCRMScope()->companyUserfield()->get($fieldId)->userfieldItem()->XML_ID
        );
    }
}