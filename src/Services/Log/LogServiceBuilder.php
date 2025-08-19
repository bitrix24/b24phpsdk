<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Log;

use Bitrix24\SDK\Attributes\ApiServiceBuilderMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Services\AbstractServiceBuilder;
use Bitrix24\SDK\Services\Log\BlogPost;

#[ApiServiceBuilderMetadata(new Scope(['log']))]
class LogServiceBuilder extends AbstractServiceBuilder
{
    public function blogPost(): BlogPost\Service\BlogPost
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new BlogPost\Service\BlogPost(
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }
}
