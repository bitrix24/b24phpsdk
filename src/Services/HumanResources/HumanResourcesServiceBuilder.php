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

namespace Bitrix24\SDK\Services\HumanResources;

use Bitrix24\SDK\Attributes\ApiServiceBuilderMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Services\AbstractServiceBuilder;

#[ApiServiceBuilderMetadata(new Scope(['humanresources']))]
class HumanResourcesServiceBuilder extends AbstractServiceBuilder
{
    public function employee(): Service\Employee
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Service\Employee($this->core, $this->log);
        }

        return $this->serviceCache[__METHOD__];
    }

    public function employeeField(): EmployeeField\Service\EmployeeField
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new EmployeeField\Service\EmployeeField($this->core, $this->log);
        }

        return $this->serviceCache[__METHOD__];
    }

    public function node(): Service\Node
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Service\Node($this->core, $this->log);
        }

        return $this->serviceCache[__METHOD__];
    }

    public function nodeField(): NodeField\Service\NodeField
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new NodeField\Service\NodeField($this->core, $this->log);
        }

        return $this->serviceCache[__METHOD__];
    }

    public function nodeCommunication(): Service\NodeCommunication
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Service\NodeCommunication($this->core, $this->log);
        }

        return $this->serviceCache[__METHOD__];
    }

    public function nodeMember(): Service\NodeMember
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Service\NodeMember($this->core, $this->log);
        }

        return $this->serviceCache[__METHOD__];
    }

    public function nodeMemberField(): NodeMemberField\Service\NodeMemberField
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new NodeMemberField\Service\NodeMemberField($this->core, $this->log);
        }

        return $this->serviceCache[__METHOD__];
    }
}
