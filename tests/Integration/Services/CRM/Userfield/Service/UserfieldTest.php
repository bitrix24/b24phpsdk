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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Userfield\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\CRM\Userfield\Result\AbstractUserfieldItemResult;
use Bitrix24\SDK\Services\CRM\Userfield\Service\Userfield;
use Bitrix24\SDK\Core;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Userfield::class)]
#[CoversMethod(Userfield::class,'fields')]
#[CoversMethod(Userfield::class,'enumerationFields')]
#[CoversMethod(Userfield::class,'settingsFields')]
#[CoversMethod(Userfield::class,'types')]
class UserfieldTest extends TestCase
{
    use CustomBitrix24Assertions;
    private ServiceBuilder $sb;

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('crm.userfield.fields')]
    public function testFields(): void
    {
        self::assertIsArray($this->sb->getCRMScope()->userfield()->fields()->getFieldsDescription());
    }

    #[TestDox('All system fields are phpdoc annotated')]
    public function testAllSystemFieldsAnnotated(): void
    {
        $propListFromApi = (new Core\Fields\FieldsFilter())->filterSystemFields(
            array_keys($this->sb->getCRMScope()->userfield()->fields()->getFieldsDescription())
        );
        $this->assertBitrix24AllResultItemFieldsAnnotated($propListFromApi, AbstractUserfieldItemResult::class);
    }

    #[TestDox('All system fields are phpdoc annotated with valid types')]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $allFields = $this->sb->getCRMScope()->userfield()->fields()->getFieldsDescription();
        $systemFieldsCodes = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($allFields));
        $systemFields = array_filter($allFields, static function ($code) use ($systemFieldsCodes) {
            return in_array($code, $systemFieldsCodes, true);
        }, ARRAY_FILTER_USE_KEY);

        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $systemFields,
            AbstractUserfieldItemResult::class
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testEnumerationFields(): void
    {
        self::assertIsArray($this->sb->getCRMScope()->userfield()->enumerationFields()->getFieldsDescription());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testSettingsFields(): void
    {
        foreach ($this->sb->getCRMScope()->userfield()->types()->getTypes() as $typeItem) {
            self::assertIsArray($this->sb->getCRMScope()->userfield()->settingsFields($typeItem->ID)->getFieldsDescription());
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testTypes(): void
    {
        $ufTypes = $this->sb->getCRMScope()->userfield()->types();
        $this->assertGreaterThan(10, $ufTypes->getTypes());
    }

    public function setUp(): void
    {
        $this->sb = Fabric::getServiceBuilder();
    }
}