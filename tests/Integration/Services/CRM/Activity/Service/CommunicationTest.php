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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Activity\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\CRM\Activity\ActivityContentType;
use Bitrix24\SDK\Services\CRM\Activity\ActivityDirectionType;
use Bitrix24\SDK\Services\CRM\Activity\Result\ActivityItemResult;
use Bitrix24\SDK\Services\CRM\Activity\Result\ActivitiesResult;
use Bitrix24\SDK\Services\CRM\Activity\Service\Communication;
use Bitrix24\SDK\Services\CRM\Activity\ActivityType;
use Bitrix24\SDK\Services\CRM\Contact\Service\Contact;
use Bitrix24\SDK\Services\CRM\Deal\Result\DealItemResult;
use Bitrix24\SDK\Services\CRM\Deal\Result\DealProductRowItemResult;
use Bitrix24\SDK\Tests\Builders\DemoDataGenerator;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Typhoon\Reflection\TyphoonReflector;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Core;

#[CoversClass(Communication::class)]
#[CoversMethod(Communication::class, 'fields')]
class CommunicationTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Communication $communicationService;

    public function setUp(): void
    {
        $this->communicationService = Fabric::getServiceBuilder()->getCRMScope()->communication();
    }

    public function testAllSystemFieldsAnnotated(): void
    {
        $propListFromApi = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($this->communicationService->fields()->getFieldsDescription()));
        $this->assertBitrix24AllResultItemFieldsAnnotated($propListFromApi, ActivityItemResult::class);
    }

    public function testAllSystemFieldsHasValidTypeAnnotation():void
    {
        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $this->communicationService->fields()->getFieldsDescription(),
            ActivityItemResult::class);
    }

    /**
     * @covers Communication::fields
     * @throws BaseException
     * @throws TransportException
     */
    public function testFields(): void
    {
        self::assertIsArray($this->communicationService->fields()->getFieldsDescription());
    }
}