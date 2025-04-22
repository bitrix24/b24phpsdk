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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Contact\Service;


use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\CRM\Common\CompanyConnection;
use Bitrix24\SDK\Services\CRM\Contact\Result\ContactCompanyConnectionItemResult;
use Bitrix24\SDK\Services\CRM\Contact\Service\ContactCompany;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Tests\Builders\Services\CRM\CompanyBuilder;
use Bitrix24\SDK\Tests\Builders\Services\CRM\ContactBuilder;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use Bitrix24\SDK\Core;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;

#[CoversClass(ContactCompany::class)]
#[CoversMethod(ContactCompany::class, 'fields')]
#[CoversMethod(ContactCompany::class, 'setItems')]
#[CoversMethod(ContactCompany::class, 'get')]
#[CoversMethod(ContactCompany::class, 'add')]
#[CoversMethod(ContactCompany::class, 'delete')]
#[CoversMethod(ContactCompany::class, 'deleteItems')]
class ContactCompanyTest extends TestCase
{
    use CustomBitrix24Assertions;

    private ServiceBuilder $sb;

    private array $createdCompanies = [];

    private array $createdContacts = [];

    protected function setUp(): void
    {
        $this->sb = Fabric::getServiceBuilder();
    }

    protected function tearDown(): void
    {
    }


    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testFields(): void
    {
        self::assertIsArray($this->sb->getCRMScope()->contactCompany()->fields()->getFieldsDescription());
    }

    public function testAllSystemFieldsAnnotated(): void
    {
        $propListFromApi = (new Core\Fields\FieldsFilter())->filterSystemFields(
            array_keys($this->sb->getCRMScope()->contactCompany()->fields()->getFieldsDescription())
        );
        $this->assertBitrix24AllResultItemFieldsAnnotated($propListFromApi, ContactCompanyConnectionItemResult::class);
    }

    /**
     * @throws TransportException
     * @throws BaseException
     */
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $allFields = $this->sb->getCRMScope()->contactCompany()->fields()->getFieldsDescription();
        $systemFieldsCodes = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($allFields));
        $systemFields = array_filter($allFields, static fn($code): bool => in_array($code, $systemFieldsCodes, true), ARRAY_FILTER_USE_KEY);

        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $systemFields,
            ContactCompanyConnectionItemResult::class
        );
    }

    /**
     * @throws TransportException
     * @throws InvalidArgumentException
     * @throws BaseException
     */
    public function testSet(): void
    {
        // prepare data
        $contactId = $this->sb->getCRMScope()->contact()->add((new ContactBuilder())->build())->getId();
        $this->createdContacts[] = $contactId;

        $connectedCompanies = [];
        $newCompanyId = [];
        for ($i = 0; $i < 3; $i++) {
            $companyId = $this->sb->getCRMScope()->company()->add((new CompanyBuilder())->build())->getId();
            $this->createdCompanies[] = $companyId;
            $newCompanyId[] = $companyId;
            $connectedCompanies[] = new CompanyConnection($companyId);
        }

        // bind
        $this->sb->getCRMScope()->contactCompany()->setItems($contactId, $connectedCompanies);

        // read and check
        $companies = $this->sb->getCRMScope()->contactCompany()->get($contactId)->getCompanyConnections();
        foreach ($companies as $company) {
            $this->assertContains($company->COMPANY_ID, $newCompanyId);
        }
    }

    public function testSetWithEmptyConnections(): void
    {
        $this->expectException(Core\Exceptions\InvalidArgumentException::class);
        $this->sb->getCRMScope()->contactCompany()->setItems(1, []);
    }

    public function testSetWithWrongType(): void
    {
        $this->expectException(Core\Exceptions\InvalidArgumentException::class);
        /** @phpstan-ignore */
        $this->sb->getCRMScope()->contactCompany()->setItems(1, [new \DateTime()]);
    }

    public function testAdd(): void
    {
        $contactId = $this->sb->getCRMScope()->contact()->add((new ContactBuilder())->build())->getId();
        $this->createdContacts[] = $contactId;

        $newCompanyId = [];
        for ($i = 0; $i < 3; $i++) {
            $companyId = $this->sb->getCRMScope()->company()->add((new CompanyBuilder())->build())->getId();
            $this->createdCompanies[] = $companyId;
            $newCompanyId[] = $companyId;

            // check
            $this->assertTrue(
                $this->sb->getCRMScope()->contactCompany()->add(
                    $contactId,
                    new CompanyConnection($companyId)
                )->isSuccess()
            );
        }

        // read and check
        $companies = $this->sb->getCRMScope()->contactCompany()->get($contactId)->getCompanyConnections();
        foreach ($companies as $company) {
            $this->assertContains($company->COMPANY_ID, $newCompanyId);
        }
    }

    public function testDelete(): void
    {
        $contactId = $this->sb->getCRMScope()->contact()->add((new ContactBuilder())->build())->getId();
        $this->createdContacts[] = $contactId;

        $newCompanyId = [];
        for ($i = 0; $i < 3; $i++) {
            $companyId = $this->sb->getCRMScope()->company()->add((new CompanyBuilder())->build())->getId();
            $this->createdCompanies[] = $companyId;
            $newCompanyId[] = $companyId;

            // check
            $this->assertTrue(
                $this->sb->getCRMScope()->contactCompany()->add(
                    $contactId,
                    new CompanyConnection($companyId)
                )->isSuccess()
            );
        }

        $this->assertTrue($this->sb->getCRMScope()->contactCompany()->delete($contactId, array_pop($newCompanyId))->isSuccess());
        $this->assertCount(count($newCompanyId), $this->sb->getCRMScope()->contactCompany()->get($contactId)->getCompanyConnections());
    }

    public function testDeleteItems(): void
    {
        $contactId = $this->sb->getCRMScope()->contact()->add((new ContactBuilder())->build())->getId();
        $this->createdContacts[] = $contactId;

        $newCompanyId = [];
        for ($i = 0; $i < 3; $i++) {
            $companyId = $this->sb->getCRMScope()->company()->add((new CompanyBuilder())->build())->getId();
            $this->createdCompanies[] = $companyId;
            $newCompanyId[] = $companyId;
            $this->assertTrue(
                $this->sb->getCRMScope()->contactCompany()->add(
                    $contactId,
                    new CompanyConnection($companyId)
                )->isSuccess()
            );
        }

        $this->assertTrue($this->sb->getCRMScope()->contactCompany()->deleteItems($contactId)->isSuccess());
        $this->assertCount(0, $this->sb->getCRMScope()->contactCompany()->get($companyId)->getCompanyConnections());
    }
}