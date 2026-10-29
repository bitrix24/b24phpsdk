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

namespace Bitrix24\SDK\Tests\Unit\Core\Response\DTO;

use Bitrix24\SDK\Core\Response\DTO\UnsuccessfulResponseError;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(UnsuccessfulResponseError::class)]
class UnsuccessfulResponseErrorTest extends TestCase
{
    #[Test]
    public function testFromArrayKeepsUnknownErrorKeysAsDetails(): void
    {
        $error = UnsuccessfulResponseError::fromArray([
            'code' => 'CRM_FORM_VERSION_MISMATCH',
            'message' => 'outdated',
            'validation' => [['field' => 'name', 'message' => 'required']],
            'currentUpdatedTime' => '2026-10-29T14:16:55+03:00',
        ]);

        $this->assertSame('CRM_FORM_VERSION_MISMATCH', $error->code);
        $this->assertSame('outdated', $error->message);
        $this->assertCount(1, $error->validation);
        $this->assertSame(['currentUpdatedTime' => '2026-10-29T14:16:55+03:00'], $error->details);
    }

    #[Test]
    public function testFromArrayWithoutExtraKeysHasEmptyDetails(): void
    {
        $this->assertSame([], UnsuccessfulResponseError::fromArray(['code' => 'X', 'message' => 'y'])->details);
    }
}
