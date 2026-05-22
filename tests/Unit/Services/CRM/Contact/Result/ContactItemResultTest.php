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

namespace Bitrix24\SDK\Tests\Unit\Services\CRM\Contact\Result;

use Bitrix24\SDK\Services\CRM\Common\Result\SystemFields\Types\Email;
use Bitrix24\SDK\Services\CRM\Common\Result\SystemFields\Types\InstantMessenger;
use Bitrix24\SDK\Services\CRM\Common\Result\SystemFields\Types\Phone;
use Bitrix24\SDK\Services\CRM\Common\Result\SystemFields\Types\Website;
use Bitrix24\SDK\Services\CRM\Contact\Result\ContactItemResult;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ContactItemResult::class)]
final class ContactItemResultTest extends TestCase
{
    use CustomBitrix24Assertions;

    #[Test]
    public function testMagicGetterCastsAllAnnotatedFieldsAccordingToPhpDoc(): void
    {
        $contactItemResult = new ContactItemResult([
            'ADDRESS_LOC_ADDR_ID' => '17',
            'ADDRESS'             => null,
            'ADDRESS_2'           => null,
            'ADDRESS_CITY'        => 'Bishkek',
            'ADDRESS_COUNTRY'     => 'KG',
            'ADDRESS_COUNTRY_CODE' => 'KG',
            'ADDRESS_POSTAL_CODE' => '720000',
            'ADDRESS_PROVINCE'    => null,
            'ADDRESS_REGION'      => null,
            'ASSIGNED_BY_ID'      => '1',
            'BIRTHDATE'           => '',
            'COMMENTS'            => null,
            'COMPANY_ID'          => null,
            'COMPANY_IDS'         => [10, 11],
            'CREATED_BY_ID'       => '1',
            'DATE_CREATE'         => '2026-04-29T12:34:56+00:00',
            'DATE_MODIFY'         => '2026-04-29T12:35:56+00:00',
            'FACE_ID'             => '42',
            'EXPORT'              => 'Y',
            'EMAIL'               => [['ID' => '7', 'VALUE' => 'user@example.com', 'VALUE_TYPE' => 'WORK']],
            'ID'                  => '100',
            'HAS_EMAIL'           => 'Y',
            'HAS_IMOL'            => 'N',
            'HAS_PHONE'           => 'Y',
            'HONORIFIC'           => null,
            'IM'                  => [['ID' => '8', 'VALUE' => 'user-im', 'VALUE_TYPE' => 'WORK']],
            'LEAD_ID'             => null,
            'LAST_ACTIVITY_TIME'  => '2026-04-29T12:36:56+00:00',
            'LAST_ACTIVITY_BY'    => '1',
            'LAST_NAME'           => 'Contact',
            'LINK'                => null,
            'MODIFY_BY_ID'        => '1',
            'NAME'                => 'Test',
            'ORIGIN_ID'           => null,
            'ORIGINATOR_ID'       => null,
            'ORIGIN_VERSION'      => null,
            'OPENED'              => 'Y',
            'PHONE'               => [['ID' => '9', 'VALUE' => '+996555000000', 'VALUE_TYPE' => 'WORK']],
            'POST'                => null,
            'PHOTO'               => null,
            'SECOND_NAME'         => null,
            'SOURCE_DESCRIPTION'  => null,
            'SOURCE_ID'           => null,
            'TYPE_ID'             => null,
            'UTM_CAMPAIGN'        => null,
            'UTM_CONTENT'         => null,
            'UTM_MEDIUM'          => null,
            'UTM_SOURCE'          => null,
            'UTM_TERM'            => null,
            'WEB'                 => [['ID' => '10', 'VALUE' => 'https://example.com', 'VALUE_TYPE' => 'WORK']],
        ]);

        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($contactItemResult, ContactItemResult::class);
        self::assertInstanceOf(CarbonImmutable::class, $contactItemResult->DATE_CREATE);
        self::assertSame(42, $contactItemResult->FACE_ID);
        self::assertInstanceOf(Email::class, $contactItemResult->EMAIL[0]);
        self::assertInstanceOf(InstantMessenger::class, $contactItemResult->IM[0]);
        self::assertInstanceOf(Phone::class, $contactItemResult->PHONE[0]);
        self::assertInstanceOf(Website::class, $contactItemResult->WEB[0]);
    }
}
