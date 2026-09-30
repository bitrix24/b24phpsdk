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

namespace Bitrix24\SDK\Tests\Integration\Services\Telephony\Voximplant\Callback\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Services\Telephony\Voximplant\Callback\Service\Callback;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Callback::class)]
class CallbackTest extends TestCase
{
    public function testStartRejectsInvalidParametersWithoutPlacingACall(): void
    {
        // Never provide a real line or destination: this verifies only server validation.
        $this->expectException(BaseException::class);
        $this->expectExceptionMessageMatches('/could not find line/i');

        Factory::getServiceBuilder()->getTelephonyScope()->getVoximplantServiceBuilder()->callback()->start(
            'b24phpsdk-nonexistent-callback-line',
            '',
            ''
        );
    }
}
