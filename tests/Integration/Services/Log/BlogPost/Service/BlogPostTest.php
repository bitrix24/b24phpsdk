<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Log\BlogPost\Service;

use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\TestCase;
use Carbon\CarbonImmutable;

class BlogPostTest extends TestCase
{
    public function testPrivatePostLifecycle(): void
    {
        $builder = Factory::getServiceBuilder();
        $userId = (int)$builder->core->call('user.current')->getResponseData()->getResult()['ID'];
        $service = $builder->getLogScope()->blogPost();
        $comments = $builder->getLogScope()->blogComment();
        $postId = $service->add('SDK private fixture', 'SDK coverage #643', dest: ['U'.$userId], important: true)->getId();
        try {
            self::assertGreaterThan(0, $postId);
            $items = $service->get($postId)->getBlogPosts();
            self::assertCount(1, $items);
            self::assertSame($postId, $items[0]->ID);
            self::assertSame($postId, $service->update($postId, 'Updated private fixture', 'Updated private title', important: true, importantDateEnd: CarbonImmutable::now()->addDay())->getId());
            self::assertSame('Updated private title', $service->get($postId)->getBlogPosts()[0]->TITLE);
            self::assertIsArray($service->getUsersImportant($postId)->getUserIds());
            // Only the current user already has access: never grant access to anyone else.
            self::assertTrue($service->share($postId, ['U'.$userId])->isSuccess());
            $commentId = $comments->add($postId, 'Private SDK test comment')->getId();
            self::assertGreaterThan(0, $commentId);
            self::assertTrue($comments->delete($commentId)->isSuccess());
        } finally {
            self::assertTrue($service->delete($postId)->isSuccess());
        }

        self::assertSame([], $service->get($postId)->getBlogPosts());
    }
}
