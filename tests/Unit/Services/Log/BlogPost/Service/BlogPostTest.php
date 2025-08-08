<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Services\Log\BlogPost\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\Log\BlogPost\Result\BlogPostAddResult;
use Bitrix24\SDK\Services\Log\BlogPost\Service\BlogPost;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(BlogPost::class)]
class BlogPostTest extends TestCase
{
    #[TestDox('Test BlogPost service can be instantiated')]
    public function testCanBeInstantiated(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $nullLogger = new NullLogger();
        
        $blogPost = new BlogPost($core, $nullLogger);
        
        $this->assertInstanceOf(BlogPost::class, $blogPost);
    }

    #[TestDox('Test BlogPost::add method builds correct parameters')]
    public function testAddBuildsCorrectParameters(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $response = $this->createMock(Response::class);
        $nullLogger = new NullLogger();
        
        $core->expects($this->once())
            ->method('call')
            ->with(
                'log.blogpost.add',
                [
                    'POST_MESSAGE' => 'Test message',
                    'POST_TITLE' => 'Test title',
                    'IMPORTANT' => 'Y',
                    'IMPORTANT_DATE_END' => '2025-08-15T00:00:00+00:00',
                    'DEST' => ['UA']
                ]
            )
            ->willReturn($response);
        
        $blogPost = new BlogPost($core, $nullLogger);
        $blogPostAddResult = $blogPost->add(
            postMessage: 'Test message',
            postTitle: 'Test title',
            important: true,
            importantDateEnd: '2025-08-15T00:00:00+00:00',
            dest: ['UA']
        );
        
        $this->assertInstanceOf(BlogPostAddResult::class, $blogPostAddResult);
    }
}
