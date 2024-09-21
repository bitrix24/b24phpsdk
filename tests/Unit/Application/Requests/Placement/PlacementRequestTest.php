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

namespace Bitrix24\SDK\Tests\Unit\Application\Requests\Placement;

use Bitrix24\SDK\Application\Requests\Placement\PlacementRequest;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(PlacementRequest::class)]
class PlacementRequestTest extends TestCase
{
    #[Test]
    #[DataProvider('requestsFabric')]
    public function testIsCanProcess(Request $request, bool $isCanProcess): void
    {
        $this->assertEquals($isCanProcess, PlacementRequest::isCanProcess($request));
    }

    public static function requestsFabric(): Generator
    {
        $rawRequest = 'AUTH_ID=4991e86600715db20058f18a00000001302a076f538759f623791a7dece3ebd357ae95&AUTH_EXPIRES=3600&REFRESH_ID=3910106700715db20058f18a00000001302a07aec4e4fe2f9bcbe401caff9773d9e489&member_id=010b6886ebc205e43ae65000ee00addb&status=L&PLACEMENT=DEFAULT&PLACEMENT_OPTIONS=%7B%22any%22%3A%2228%5C%2F%22%7D';
        $queryString = parse_url('/install.php?DOMAIN=bitrix24-php-sdk-playground.bitrix24.com&PROTOCOL=1&LANG=en&APP_SID=926d17365a87fbb35bace60dcc8f08cf', PHP_URL_QUERY);
        parse_str($queryString, $query);
        parse_str($rawRequest, $requestContent);
        $request = new Request(
            $query,                    // GET parameters
            $requestContent,           // POST parameters
            [],                        // Additional attributes, if any
            [],                        // Cookies, if any
            [],                        // Files, if any
            [],                        // Server parameters, if any
            $rawRequest     // Raw content
        );
        $request->setMethod('POST');

        yield 'install placement' => [
            $request,
            true
        ];
    }
}