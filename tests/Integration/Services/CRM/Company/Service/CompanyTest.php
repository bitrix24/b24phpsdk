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
use Bitrix24\SDK\Services\CRM\Common\Result\SystemFields\Types\EmailValueType;
use Bitrix24\SDK\Services\CRM\Common\Result\SystemFields\Types\PhoneValueType;
use Bitrix24\SDK\Services\CRM\Company\Result\CompanyItemResult;
use Bitrix24\SDK\Services\CRM\Company\Service\Company;
use Bitrix24\SDK\Services\CRM\Deal\Service\Deal;
use Bitrix24\SDK\Services\CRM\Lead\Result\LeadItemResult;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Bitrix24\SDK\Core;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Services\CRM\Deal\Result\DealItemResult;
use Faker;
use Symfony\Component\Uid\Uuid;

#[CoversClass(Company::class)]
#[CoversMethod(Company::class, 'fields')]
#[CoversMethod(Company::class, 'add')]
#[CoversMethod(Company::class, 'get')]
#[CoversMethod(Company::class, 'delete')]
#[CoversMethod(Company::class, 'list')]
#[CoversMethod(Company::class, 'update')]
#[CoversMethod(Company::class, 'countByFilter')]
class CompanyTest extends TestCase
{
    use CustomBitrix24Assertions;

    private ServiceBuilder $sb;

    private Faker\Generator $faker;

    private array $createdCompanies = [];

    protected function setUp(): void
    {
        $this->sb = Factory::getServiceBuilder();
        $this->faker = Faker\Factory::create();
    }

    protected function tearDown(): void
    {
    }

    #[TestDox('method crm.company.fields')]
    public function testAllSystemFieldsAnnotated(): void
    {
        $propListFromApi = (new Core\Fields\FieldsFilter())->filterSystemFields(
            array_keys($this->sb->getCRMScope()->company()->fields()->getFieldsDescription())
        );
        $this->assertBitrix24AllResultItemFieldsAnnotated($propListFromApi, CompanyItemResult::class);
    }

    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $allFields = $this->sb->getCRMScope()->company()->fields()->getFieldsDescription();
        $systemFieldsCodes = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($allFields));
        $systemFields = array_filter($allFields, static fn($code): bool => in_array($code, $systemFieldsCodes, true), ARRAY_FILTER_USE_KEY);

        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $systemFields,
            CompanyItemResult::class
        );
    }

    #[TestDox('method crm.company.fields returns the description of company fields, including custom fields')]
    public function testFields(): void
    {
        self::assertIsArray($this->sb->getCRMScope()->company()->fields()->getFieldsDescription());
    }

    #[TestDox('method crm.company.add')]
    public function testAdd(): void
    {
        $companyTitle = sprintf('Acme Inc - %s', time());
        $email = $this->faker->email();
        $phone = $this->faker->e164PhoneNumber();

        $companyId = $this->sb->getCRMScope()->company()->add(
            [
                'TITLE' => $companyTitle,
                'COMMENTS' => sprintf('test company from b24-php-sdk integration tests %s', time()),
                'UTM_SOURCE' => 'b24-php-sdk',
                'EMAIL' => [
                    [
                        'VALUE' => $email,
                        'VALUE_TYPE' => EmailValueType::work->name,
                    ]
                ],
                'PHONE' => [
                    [
                        'VALUE' => $phone,
                        'VALUE_TYPE' => PhoneValueType::work->name,
                    ]
                ],
            ]
        )->getId();
        $this->createdCompanies[] = $companyId;

        $this->assertGreaterThan(1, $companyId);
        $companyItemResult = $this->sb->getCRMScope()->company()->get($companyId)->company();

        $this->assertEquals($companyTitle, $companyItemResult->TITLE);
        $this->assertEquals($email, $companyItemResult->EMAIL[0]->VALUE);
        $this->assertEquals($phone, $companyItemResult->PHONE[0]->VALUE);
    }

    #[TestDox('method crm.company.get')]
    public function testGet(): void
    {
        $companyTitle = sprintf('Acme Inc - %s', time());
        $companyId = $this->sb->getCRMScope()->company()->add(
            [
                'TITLE' => $companyTitle,
                'COMMENTS' => sprintf('test company from b24-php-sdk integration tests %s', time()),
            ]
        )->getId();
        $this->createdCompanies[] = $companyId;

        $this->assertGreaterThan(1, $companyId);
        $companyItemResult = $this->sb->getCRMScope()->company()->get($companyId)->company();
        $this->assertEquals($companyTitle, $companyItemResult->TITLE);
    }

    #[TestDox('method crm.company.delete')]
    public function testDelete(): void
    {
        $companyTitle = sprintf('Acme Inc - %s', time());
        $companyId = $this->sb->getCRMScope()->company()->add(
            [
                'TITLE' => $companyTitle,
                'COMMENTS' => sprintf('test company from b24-php-sdk integration tests %s', time()),
            ]
        )->getId();

        $this->assertTrue($this->sb->getCRMScope()->company()->delete($companyId)->isSuccess());
    }


    #[TestDox('method crm.company.list')]
    public function testList(): void
    {
        $companyTitle = sprintf('Acme Inc - %s', time());
        $companyId = $this->sb->getCRMScope()->company()->add(
            [
                'TITLE' => $companyTitle,
                'COMMENTS' => sprintf('test company from b24-php-sdk integration tests %s', time()),
            ]
        )->getId();
        $this->createdCompanies[] = $companyId;

        $companiesResult = $this->sb->getCRMScope()->company()->list();
        $this->assertGreaterThan(1, count($companiesResult->getCompanies()));
    }

    #[TestDox('method crm.company.update')]
    public function testUpdate(): void
    {
        $companyTitle = sprintf('Acme Inc - %s', time());
        $companyId = $this->sb->getCRMScope()->company()->add(
            [
                'TITLE' => $companyTitle,
                'COMMENTS' => sprintf('test company from b24-php-sdk integration tests %s', time()),
            ]
        )->getId();
        $this->createdCompanies[] = $companyId;

        $newTitle = 'new title';
        $this->assertTrue($this->sb->getCRMScope()->company()->update($companyId, ['TITLE' => $newTitle])->isSuccess());
        $this->assertEquals($newTitle, $this->sb->getCRMScope()->company()->get($companyId)->company()->TITLE);
    }

    public function testCountByFilter(): void
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

        self::assertEquals(count($companies), $cnt);

        $this->assertEquals(
            count($companies),
            $this->sb->getCRMScope()->company()->countByFilter(['UTM_SOURCE' => $utmSource])
        );
    }
}