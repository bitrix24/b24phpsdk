<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Log\BlogPost\Result;

use Bitrix24\SDK\Services\Log\BlogPost\Result\BlogPostItemResult;
use Bitrix24\SDK\Services\Log\BlogPost\Service\BlogPost;
use Bitrix24\SDK\Services\ServiceBuilderFactory;
use Psr\Log\NullLogger;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/** The API has no fields endpoint; validate the raw live result and getter casting instead. */
#[CoversClass(BlogPostItemResult::class)]
class BlogPostItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;
    private BlogPost $service;
    private int $postId;
    protected function setUp(): void
    {
        $builder = ServiceBuilderFactory::createServiceBuilderFromWebhook(
            $_ENV['BITRIX24_PHP_SDK_PLAYGROUND_WEBHOOK'] ?? $_ENV['BITRIX24_WEBHOOK'],
            logger: new NullLogger()
        );
        $userId = (int)$builder->core->call('user.current')->getResponseData()->getResult()['ID'];
        $this->service = $builder->getLogScope()->blogPost();
        $this->postId = $this->service->add('Private annotation fixture', 'SDK annotations #643', dest: ['U'.$userId])->getId();
    }
    protected function tearDown(): void
    {
        $this->service->delete($this->postId);
    }
    public function testAllSystemFieldsAnnotated(): void
    {
        $raw = $this->service->get($this->postId)->getCoreResponse()->getResponseData()->getResult()[0];
        // UF_* fields depend on portal configuration; their names are not a fixed system contract.
        $systemFields = array_filter(array_keys($raw), static fn (string $name): bool => !str_starts_with($name, 'UF_'));
        // This shared assertion compares exact sets. Add the documented optional fields,
        // without removing any unknown system field that should make the test fail.
        $optionalFields = ['FILES', 'UF_BLOG_POST_DOC', 'UF_BLOG_POST_URL_PRV', 'UF_GRATITUDE',
            'UF_BLOG_POST_FILE', 'UF_BLOG_POST_IMPRTNT', 'UF_IMPRTANT_DATE_END', 'UF_BLOG_POST_VOTE', 'UF_MAIL_MESSAGE'];
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_values(array_unique([...$systemFields, ...$optionalFields])), BlogPostItemResult::class);
    }
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $result = $this->service->get($this->postId);
        $item = $result->getBlogPosts()[0];
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($item, BlogPostItemResult::class);
        $raw = $result->getCoreResponse()->getResponseData()->getResult()[0];
        foreach (['HAS_SOCNET_ALL', 'HAS_TAGS', 'HAS_IMAGES', 'HAS_PROPS', 'HAS_COMMENT_IMAGES'] as $field) {
            self::assertArrayHasKey($field, $raw);
            self::assertContains($raw[$field], ['Y', 'N', true, false, null]);
            self::assertSame(match ($raw[$field]) {
                'Y', true => true,
                'N', false => false,
                null => null,
                default => throw new \LogicException('Unexpected flag representation'),
            }, $item->$field, $field);
        }
    }
}
