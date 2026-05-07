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

namespace Bitrix24\SDK\Tests\Unit\OpenApi\Domain;

use Bitrix24\SDK\OpenApi\Domain\OaToSdkMethodNormalizationPolicy;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class OaToSdkMethodNormalizationPolicyTest extends TestCase
{
    #[Test]
    public function itNormalizesOpenApiPathNamesAndAliases(): void
    {
        $oaToSdkMethodNormalizationPolicy = new OaToSdkMethodNormalizationPolicy();

        $this->assertSame('main.eventlog.list', $oaToSdkMethodNormalizationPolicy->normalizeOaMethodName('/main.eventlog.list'));
        $this->assertSame('documentation', $oaToSdkMethodNormalizationPolicy->normalizeOaMethodName('/rest.documentation.openapi'));
    }

    #[Test]
    public function itDerivesScopesAndSupportsScopeAliases(): void
    {
        $oaToSdkMethodNormalizationPolicy = new OaToSdkMethodNormalizationPolicy();

        $this->assertSame('main', $oaToSdkMethodNormalizationPolicy->deriveScope('main.eventlog.list'));
        $this->assertSame('–', $oaToSdkMethodNormalizationPolicy->deriveScope('documentation'));
        $this->assertTrue($oaToSdkMethodNormalizationPolicy->isScopeCompatible('tasks.task.get', 'task'));
        $this->assertTrue($oaToSdkMethodNormalizationPolicy->isScopeCompatible('documentation', ''));
        $this->assertFalse($oaToSdkMethodNormalizationPolicy->isScopeCompatible('main.eventlog.list', 'task'));
    }

    #[Test]
    public function itBuildsDocumentationUrlsForScopedAndScopeLessMethods(): void
    {
        $oaToSdkMethodNormalizationPolicy = new OaToSdkMethodNormalizationPolicy();

        $this->assertSame(
            'https://apidocs.bitrix24.com/api-reference/rest-v3/tasks/tasks-task-add.html',
            $oaToSdkMethodNormalizationPolicy->buildDocumentationUrl('tasks.task.add')
        );
        $this->assertSame(
            'https://apidocs.bitrix24.com/api-reference/rest-v3/documentation.html',
            $oaToSdkMethodNormalizationPolicy->buildDocumentationUrl('documentation')
        );
    }
}
