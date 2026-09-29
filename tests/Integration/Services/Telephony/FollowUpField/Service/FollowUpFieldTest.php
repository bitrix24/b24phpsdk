<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Telephony\FollowUpField\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Telephony\FollowUpField\Result\FollowUpFieldItemResult;
use Bitrix24\SDK\Services\Telephony\FollowUpField\Service\FollowUpField;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(FollowUpField::class)]
class FollowUpFieldTest extends TestCase
{
    private FollowUpField $followUpFieldService;

    #[\Override]
    protected function setUp(): void
    {
        $this->followUpFieldService = Factory::getServiceBuilder()->getTelephonyScope()->followUpField();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('call.followup.field.list returns Follow-up field metadata')]
    public function testList(): void
    {
        $fields = $this->followUpFieldService->list()->getFields();
        $this->assertNotEmpty($fields);

        $names = array_map(static fn (FollowUpFieldItemResult $field) => $field->name, $fields);
        $this->assertContains('callId', $names);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('call.followup.field.get returns a single Follow-up field descriptor')]
    public function testGet(): void
    {
        $field = $this->followUpFieldService->get('callId')->field();
        $this->assertSame('callId', $field->name);
    }
}
