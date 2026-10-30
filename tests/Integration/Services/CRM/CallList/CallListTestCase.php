<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\CallList;

use Bitrix24\SDK\Services\CRM\CallList\Service\CallList;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\TestCase;

/**
 * Call lists have no public delete endpoint. Delete only the contacts/companies
 * created here, and report retained list IDs for test-portal maintenance.
 */
abstract class CallListTestCase extends TestCase
{
    /** @var list<int> */
    protected static array $contactIds = [];

    /** @var list<int> */
    protected static array $companyIds = [];

    /** @var list<int> */
    protected static array $retainedListIds = [];

    protected static int $listId;

    protected CallList $callList;

    #[\Override]
    public static function setUpBeforeClass(): void
    {
        self::$contactIds = [];
        self::$companyIds = [];
        self::$retainedListIds = [];
        $crm = Factory::getServiceBuilder()->getCRMScope();
        try {
            for ($index = 0; $index < 2; ++$index) {
                self::$contactIds[] = $crm->contact()->add([
                    'NAME' => 'SDK #208 ' . bin2hex(random_bytes(6)),
                    'PHONE' => [['VALUE' => '+1555000208' . $index, 'VALUE_TYPE' => 'WORK']],
                ])->getId();
            }

            self::$listId = $crm->callList()->add('CONTACT', self::$contactIds)->getId();
            self::$retainedListIds[] = self::$listId;
        } catch (\Throwable $exception) {
            self::tearDownAfterClass();
            throw $exception;
        }
    }

    #[\Override]
    protected function setUp(): void
    {
        $this->callList = Factory::getServiceBuilder()->getCRMScope()->callList();
    }

    #[\Override]
    public static function tearDownAfterClass(): void
    {
        $crm = Factory::getServiceBuilder()->getCRMScope();
        $errors = [];
        foreach (self::$contactIds as $id) {
            try {
                self::assertTrue($crm->contact()->delete($id)->isSuccess());
            } catch (\Throwable $exception) {
                $errors[] = $exception;
            }
        }

        foreach (self::$companyIds as $id) {
            try {
                self::assertTrue($crm->company()->delete($id)->isSuccess());
            } catch (\Throwable $exception) {
                $errors[] = $exception;
            }
        }

        if (self::$retainedListIds !== []) {
            fwrite(STDERR, PHP_EOL . 'SDK #208 retained call-list IDs (no delete endpoint): ' . implode(', ', self::$retainedListIds) . PHP_EOL);
        }

        self::$contactIds = [];
        self::$companyIds = [];
        self::$retainedListIds = [];
        if ($errors !== []) {
            throw $errors[0];
        }
    }
}
