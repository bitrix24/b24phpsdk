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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Duplicates\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\CRM\Contact\Service\Contact;
use Bitrix24\SDK\Services\CRM\Duplicates\Service\Duplicate;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\CRM\Duplicates\Service\Duplicate::class)]
class DuplicateTest extends TestCase
{
    protected Contact $contactService;

    protected Duplicate $duplicate;

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDuplicatesByEmailNotFound(): void
    {
        $duplicateResult = $this->duplicate->findByEmail([sprintf('%s@gmail.com', time())]);
        $this->assertFalse($duplicateResult->hasDuplicateContacts());
        $this->assertFalse($duplicateResult->hasOneContact());
        $this->assertCount(0, $duplicateResult->getContactsId());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDuplicatesByEmailOneItemFound(): void
    {
        $email = sprintf('%s@gmail.com', time());
        $this->contactService->add([
            'NAME' => 'Test',
            'LAST_NAME' => 'Test',
            'EMAIL' => [
                [
                    'VALUE' => $email,
                    'TYPE' => 'WORK'
                ]
            ]
        ])->getId();

        $duplicateResult = $this->duplicate->findByEmail([$email]);
        $this->assertFalse($duplicateResult->hasDuplicateContacts());
        $this->assertTrue($duplicateResult->hasOneContact());
        $this->assertCount(1, $duplicateResult->getContactsId());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDuplicatesByPhoneNotFound(): void
    {
        $duplicateResult = $this->duplicate->findByPhone([sprintf('+1%s', time())]);
        $this->assertFalse($duplicateResult->hasDuplicateContacts());
        $this->assertFalse($duplicateResult->hasOneContact());
        $this->assertCount(0, $duplicateResult->getContactsId());
    }


    protected function setUp(): void
    {
        $this->contactService = Fabric::getServiceBuilder()->getCRMScope()->contact();
        $this->duplicate = Fabric::getServiceBuilder()->getCRMScope()->duplicate();

    }
}