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

namespace Bitrix24\SDK\Attributes\Services;

use Bitrix24\SDK\Core\Contracts\ApiVersion;

readonly class SupportedInSdkApiMethod
{
    public function __construct(
        public string $sdkScope,
        public string $name,
        public ?string $documentationUrl,
        public ?string $description,
        public bool $isDeprecated,
        public ?string $deprecationMessage,
        public string $sdkMethodName,
        public string $sdkMethodFileName,
        public int $sdkMethodFileStartLine,
        public int $sdkMethodFileEndLine,
        public string $sdkClassName,
        public ApiVersion $apiVersion,
        public ?string $sdkReturnTypeClass,
        public ?string $sdkReturnTypeFileName,
        public ?string $sdkReturnTypeDeclaration,
    ) {
    }
}
