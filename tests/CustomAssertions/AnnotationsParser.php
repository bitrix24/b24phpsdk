<?php

declare(strict_types=1);

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Bitrix24\SDK\Tests\CustomAssertions;

use Typhoon\Reflection\TyphoonReflector;

class AnnotationsParser
{
    public function parse(string $resultItemClassName): array {
        // parse keys from phpdoc annotation
        $props = TyphoonReflector::build()->reflectClass($resultItemClassName)->properties();
        $propsFromAnnotations = [];
        foreach ($props as $meta) {
            if ($meta->isAnnotated() && !$meta->isNative()) {
                $propsFromAnnotations[] = $meta->id->name;
            }
        }
        sort($propsFromAnnotations);

        return $propsFromAnnotations;
    }
}