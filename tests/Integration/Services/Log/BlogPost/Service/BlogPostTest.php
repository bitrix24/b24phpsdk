<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Log\BlogPost\Service;

use Bitrix24\SDK\Services\Log\BlogPost\Service\BlogPost;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(BlogPost::class)]
class BlogPostTest extends TestCase
{
    protected ServiceBuilder $serviceBuilder;

    #[TestDox('Test BlogPost::add method with basic parameters')]
    public function testAddBasic(): void
    {
        $result = $this->serviceBuilder->getLogScope()->blogPost()->add(
            'Test blog post message',
            'Test Title'
        );

        $this->assertTrue($result->isSuccess(), 'Blog post should be added successfully');
    }

    #[TestDox('Test BlogPost::add method with all parameters')]
    public function testAddWithAllParameters(): void
    {
        $result = $this->serviceBuilder->getLogScope()->blogPost()->add(
            postMessage: 'Test blog post with all parameters',
            postTitle: 'Complete Test',
            userId: null, // Will use current user
            dest: ['UA'], // All users
            sperm: null,
            files: null,
            important: true,
            importantDateEnd: date('c', strtotime('+7 days'))
        );

        $this->assertTrue($result->isSuccess(), 'Blog post with all parameters should be added successfully');
    }

    #[TestDox('Test BlogPost::add method with custom destination')]
    public function testAddWithCustomDestination(): void
    {
        $result = $this->serviceBuilder->getLogScope()->blogPost()->add(
            postMessage: 'Test message for specific users',
            postTitle: 'Targeted Message',
            dest: ['UA'] // All authorized users
        );

        $this->assertTrue($result->isSuccess(), 'Blog post with custom destination should be added successfully');
    }

    protected function setUp(): void
    {
        $this->serviceBuilder = Factory::getServiceBuilder();
    }
}
