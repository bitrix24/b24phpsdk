<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Services\Log\BlogPost\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Core\Response\DTO\ResponseData;
use Bitrix24\SDK\Services\Log\BlogPost\Service\BlogPost;
use Bitrix24\SDK\Services\Log\BlogComment\Service\BlogComment;
use Bitrix24\SDK\Services\Log\LogServiceBuilder;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class BlogPostCoverageTest extends TestCase
{
    private function core(string $method, array $parameters, array $result): CoreInterface
    {
        $data = $this->createStub(ResponseData::class);
        $data->method('getResult')->willReturn($result);
        $response = $this->createStub(Response::class);
        $response->method('getResponseData')->willReturn($data);
        $core = $this->createMock(CoreInterface::class);
        $core->expects($this->once())->method('call')->with($method, $parameters)->willReturn($response);
        return $core;
    }

    public function testGetMapsFiltersAndCastsList(): void
    {
        $date = new CarbonImmutable('2026-01-01T00:00:00+00:00');
        $blogPost = new BlogPost($this->core('log.blogpost.get', [
            'POST_ID' => 7, 'LOG_RIGHTS' => ['U1'], 'LOG_DATE_FROM' => $date->toIso8601String(),
            'LOG_DATE_TO' => $date->toIso8601String(), 'FIRST_ID' => 2, 'LAST_ID' => 10, 'LIMIT' => 20, 'start' => 50,
        ], [['ID' => '7','DATE_PUBLISH' => '2026-01-01T00:00:00+00:00','NUM_COMMENTS' => '0']]), new NullLogger());
        $items = $blogPost->get(7, ['U1'], $date, $date, 2, 10, 20, 50)->getBlogPosts();
        self::assertSame(7, $items[0]->ID);
        self::assertSame(0, $items[0]->NUM_COMMENTS);
        self::assertInstanceOf(CarbonImmutable::class, $items[0]->DATE_PUBLISH);
    }

    public function testMissingOptionalPostValuesRemainNull(): void
    {
        $blogPostItemResult = new \Bitrix24\SDK\Services\Log\BlogPost\Result\BlogPostItemResult(['DATE_PUBLISH' => null, 'HAS_PROPS' => null]);
        self::assertNull($blogPostItemResult->DATE_PUBLISH);
        self::assertNull($blogPostItemResult->HAS_PROPS);
        self::assertNull($blogPostItemResult->FILES);
        $reflectionClass = new \ReflectionClass($blogPostItemResult);
        self::assertStringContainsString('@property-read CarbonImmutable|null $DATE_PUBLISH', (string)$reflectionClass->getDocComment());
    }

    public function testGetOmitsOptionalFiltersAndAcceptsEmptyResult(): void
    {
        $blogPost = new BlogPost($this->core('log.blogpost.get', ['start' => 0], []), new NullLogger());
        self::assertSame([], $blogPost->get()->getBlogPosts());
    }

    public function testDelete(): void
    {
        $blogPost = new BlogPost($this->core('log.blogpost.delete', ['POST_ID' => 7], [true]), new NullLogger());
        self::assertTrue($blogPost->delete(7)->isSuccess());
    }

    public function testUpdateReturnsIdentifierAndKeepsFalseAndEmptyValues(): void
    {
        $date = new CarbonImmutable('2026-01-01T00:00:00+00:00');
        $blogPost = new BlogPost($this->core('log.blogpost.update', [
            'POST_ID' => 7, 'POST_MESSAGE' => '', 'POST_TITLE' => 'Title', 'DEST' => ['U1'], 'FILES' => [],
            'IMPORTANT' => 'N', 'IMPORTANT_DATE_END' => $date->toIso8601String(), 'USER_ID' => 1, 'SITE_ID' => 's1', 'UF_CUSTOM' => 'x',
        ], [7]), new NullLogger());
        $result = $blogPost->update(7, '', 'Title', ['U1'], [], false, $date, 1, 's1', ['UF_CUSTOM' => 'x']);
        self::assertSame(7, $result->getId());
        self::assertTrue($result->isSuccess());
    }

    public function testUpdateOmitsOptionalValues(): void
    {
        $blogPost = new BlogPost($this->core('log.blogpost.update', ['POST_ID' => 7,'POST_TITLE' => 'Title'], [7]), new NullLogger());
        self::assertSame(7, $blogPost->update(7, postTitle:'Title')->getId());
    }

    public function testShare(): void
    {
        $blogPost = new BlogPost($this->core('log.blogpost.share', ['POST_ID' => 7,'DEST' => ['U1'],'USER_ID' => 1], [true]), new NullLogger());
        self::assertTrue($blogPost->share(7, ['U1'], 1)->isSuccess());
    }

    public function testImportantUsers(): void
    {
        $blogPost = new BlogPost($this->core('log.blogpost.getusers.important', ['POST_ID' => 7], ['1',2]), new NullLogger());
        self::assertSame([1,2], $blogPost->getUsersImportant(7)->getUserIds());
    }

    public function testAddComment(): void
    {
        $blogComment = new BlogComment($this->core('log.blogcomment.add', ['POST_ID' => 7,'TEXT' => 'hello','FILES' => [],'USER_ID' => 1], [10]), new NullLogger());
        self::assertSame(10, $blogComment->add(7, 'hello', [], 1)->getId());
    }

    public function testDeleteComment(): void
    {
        $blogComment = new BlogComment($this->core('log.blogcomment.delete', ['COMMENT_ID' => 10,'USER_ID' => 1], [true]), new NullLogger());
        self::assertTrue($blogComment->delete(10, 1)->isSuccess());
    }

    public function testBuilderCachesCommentService(): void
    {
        $logServiceBuilder = new LogServiceBuilder($this->createStub(CoreInterface::class), $this->createStub(\Bitrix24\SDK\Core\Contracts\BatchOperationsInterface::class), $this->createStub(\Bitrix24\SDK\Core\Contracts\BulkItemsReaderInterface::class), new NullLogger());
        self::assertSame($logServiceBuilder->blogComment(), $logServiceBuilder->blogComment());
    }

    public function testAddResultExposesIdentifier(): void
    {
        $blogPost = new BlogPost($this->core('log.blogpost.add', ['POST_MESSAGE' => 'private','DEST' => ['U1']], ['7']), new NullLogger());
        $result = $blogPost->add('private', dest:['U1']);
        self::assertInstanceOf(\Bitrix24\SDK\Core\Result\AddedItemResult::class, $result);
        self::assertInstanceOf(\Bitrix24\SDK\Core\Contracts\AddedItemIdResultInterface::class, $result);
        self::assertSame(7, $result->getId());
        self::assertTrue($result->isSuccess());
    }
}
