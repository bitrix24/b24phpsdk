<?php

declare(strict_types=1);

use Lendable\ComposerLicenseChecker\LicenseConfigurationBuilder;

return (new LicenseConfigurationBuilder())
    ->addLicenses(
        // And other licenses you wish to allow.
        'MIT',
        'Apache-2.0',
        'BSD-3-Clause',
    )
    ->build();