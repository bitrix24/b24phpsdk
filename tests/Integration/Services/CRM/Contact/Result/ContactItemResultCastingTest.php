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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Contact\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\CRM\Common\Result\SystemFields\Types\Email;
use Bitrix24\SDK\Services\CRM\Common\Result\SystemFields\Types\EmailValueType;
use Bitrix24\SDK\Services\CRM\Common\Result\SystemFields\Types\InstantMessenger;
use Bitrix24\SDK\Services\CRM\Common\Result\SystemFields\Types\InstantMessengerValueType;
use Bitrix24\SDK\Services\CRM\Common\Result\SystemFields\Types\Phone;
use Bitrix24\SDK\Services\CRM\Common\Result\SystemFields\Types\PhoneValueType;
use Bitrix24\SDK\Services\CRM\Common\Result\SystemFields\Types\Website;
use Bitrix24\SDK\Services\CRM\Common\Result\SystemFields\Types\WebsiteValueType;
use Bitrix24\SDK\Services\CRM\Contact\Result\ContactItemResult;
use Bitrix24\SDK\Services\CRM\Contact\Service\Contact;
use Bitrix24\SDK\Tests\Integration\Factory;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ContactItemResult::class)]
final class ContactItemResultCastingTest extends TestCase
{
    private Contact $contactService;

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    public function testContactGetPayloadMapsToAnnotatedRuntimeTypes(): void
    {
        $email = sprintf('contact-item-result-%s@example.com', uniqid('', true));
        $phone = '+996555000000';
        $im = 'contact-item-result-im';
        $website = 'https://example.com/contact-item-result';

        $contactId = $this->contactService->add([
            'NAME' => 'ContactItemResult',
            'LAST_NAME' => 'Casting',
            'EMAIL' => [
                [
                    'VALUE' => $email,
                    'VALUE_TYPE' => EmailValueType::work->name,
                ],
            ],
            'PHONE' => [
                [
                    'VALUE' => $phone,
                    'VALUE_TYPE' => PhoneValueType::work->name,
                ],
            ],
            'IM' => [
                [
                    'VALUE' => $im,
                    'VALUE_TYPE' => InstantMessengerValueType::telegram->name,
                ],
            ],
            'WEB' => [
                [
                    'VALUE' => $website,
                    'VALUE_TYPE' => WebsiteValueType::work->name,
                ],
            ],
        ])->getId();

        try {
            $contact = $this->contactService->get($contactId)->contact();
            $payload = iterator_to_array($contact);

            self::assertArrayHasKey('ID', $payload);
            self::assertArrayHasKey('DATE_CREATE', $payload);
            self::assertArrayHasKey('EMAIL', $payload);
            self::assertIsString($payload['ID']);
            self::assertIsString($payload['DATE_CREATE']);
            self::assertIsArray($payload['EMAIL']);

            self::assertSame($contactId, $contact->ID);
            self::assertInstanceOf(CarbonImmutable::class, $contact->DATE_CREATE);
            self::assertInstanceOf(CarbonImmutable::class, $contact->DATE_MODIFY);
            self::assertInstanceOf(CarbonImmutable::class, $contact->LAST_ACTIVITY_TIME);
            self::assertIsBool($contact->HAS_EMAIL);
            self::assertIsBool($contact->HAS_PHONE);
            self::assertIsBool($contact->HAS_IMOL);
            self::assertInstanceOf(Email::class, $contact->EMAIL[0]);
            self::assertSame($email, $contact->EMAIL[0]->VALUE);
            self::assertInstanceOf(Phone::class, $contact->PHONE[0]);
            self::assertSame($phone, $contact->PHONE[0]->VALUE);
            self::assertInstanceOf(InstantMessenger::class, $contact->IM[0]);
            self::assertSame($im, $contact->IM[0]->VALUE);
            self::assertInstanceOf(Website::class, $contact->WEB[0]);
            self::assertSame($website, $contact->WEB[0]->VALUE);
        } finally {
            $this->contactService->delete($contactId);
        }
    }

    protected function setUp(): void
    {
        $this->contactService = Factory::getServiceBuilder()->getCRMScope()->contact();
    }
}
