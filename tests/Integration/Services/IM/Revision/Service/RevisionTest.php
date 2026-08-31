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

namespace Bitrix24\SDK\Tests\Integration\Services\IM\Revision\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\IM\Revision\Service\Revision;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Revision::class)]
class RevisionTest extends TestCase
{
    private Revision $revisionService;

    #[\Override]
    protected function setUp(): void
    {
        $this->revisionService = Fabric::getServiceBuilder()->getIMScope()->revision();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('im.revision.get returns non-negative revision numbers')]
    public function testGet(): void
    {
        $revision = $this->revisionService->get()->revision();

        $this->assertGreaterThanOrEqual(0, $revision->rest);
        $this->assertGreaterThanOrEqual(0, $revision->mobile);
        $this->assertGreaterThanOrEqual(0, $revision->web);
        $this->assertGreaterThanOrEqual(0, $revision->desktop);
        $this->assertGreaterThanOrEqual(0, $revision->im_revision_mobile);
    }
}
