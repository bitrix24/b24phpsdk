<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Infrastructure\Console\Commands\Generator;

use InvalidArgumentException;

class BranchIssueIdResolver
{
    public function resolve(string $branchName): string
    {
        if (preg_match('~^(?:feature|bugfix)/(?P<issueId>\d+)-~', $branchName, $matches) === 1) {
            return $matches['issueId'];
        }

        throw new InvalidArgumentException(sprintf(
            'Unable to extract issue id from branch "%s". Expected feature/<id>-... or bugfix/<id>-...',
            $branchName
        ));
    }
}
