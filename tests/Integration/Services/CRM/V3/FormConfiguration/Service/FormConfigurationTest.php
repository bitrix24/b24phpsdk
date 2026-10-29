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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\V3\FormConfiguration\Service;

use Bitrix24\SDK\Services\CRM\V3\FormConfiguration\Service\FormConfiguration;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(FormConfiguration::class)]
class FormConfigurationTest extends TestCase
{
    #[Test]
    public function testGetReturnsFormBuilderDirectories(): void
    {
        $configuration = Factory::getServiceBuilder()->getCRMScope()->v3()->formConfiguration()->get()->configuration();

        $schemeIds = array_map(static fn ($scheme): ?int => $scheme->id, $configuration->entitySchemes ?? []);
        $this->assertContains(1, $schemeIds, 'the lead scheme is always available');
        $this->assertNotEmpty($configuration->duplicateModes);
        $this->assertNotEmpty($configuration->languages);
        $this->assertNotEmpty($configuration->fieldTypes);
        $this->assertNotNull($configuration->permissions?->canRead);
        $this->assertGreaterThanOrEqual(
            (int)$configuration->limits?->activeForms?->current,
            (int)$configuration->limits?->activeForms?->maximum
        );
        $this->assertNotSame('', $configuration->region);
    }
}
