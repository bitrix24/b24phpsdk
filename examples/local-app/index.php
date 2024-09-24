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

use Bitrix24\SDK\Core\Credentials\ApplicationProfile;
use Bitrix24\SDK\Services\ServiceBuilderFactory;
use Symfony\Component\HttpFoundation\Request;

require_once 'vendor/autoload.php';
?>
    <pre>
    Application is worked, auth tokens from bitrix24:
    <?= print_r($_REQUEST, true) ?>
</pre>
<?php

$appProfile = ApplicationProfile::initFromArray([
    'BITRIX24_PHP_SDK_APPLICATION_CLIENT_ID' => 'INSERT_HERE_YOUR_DATA',
    'BITRIX24_PHP_SDK_APPLICATION_CLIENT_SECRET' => 'INSERT_HERE_YOUR_DATA',
    'BITRIX24_PHP_SDK_APPLICATION_SCOPE' => 'INSERT_HERE_YOUR_DATA',
]);

$b24Service = ServiceBuilderFactory::createServiceBuilderFromPlacementRequest(Request::createFromGlobals(), $appProfile);

var_dump($b24Service->getMainScope()->main()->getCurrentUserProfile()->getUserProfile());
