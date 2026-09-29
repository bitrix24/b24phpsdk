<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Sally Fancen <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Landing;

use Bitrix24\SDK\Attributes\ApiServiceBuilderMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Services\AbstractServiceBuilder;

/**
 * Class LandingServiceBuilder
 *
 * @package Bitrix24\SDK\Services\Landing
 */
#[ApiServiceBuilderMetadata(new Scope(['landing']))]
class LandingServiceBuilder extends AbstractServiceBuilder
{
    /**
     * Get Site service
     */
    public function site(): Site\Service\Site
    {
        $this->serviceCache[__METHOD__] ??= new Site\Service\Site(
            new Site\Service\Batch($this->createBatch(), $this->log),
            $this->core,
            $this->log
        );

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Get Page service
     */
    public function page(): Page\Service\Page
    {
        $this->serviceCache[__METHOD__] ??= new Page\Service\Page(
            new Page\Service\Batch($this->createBatch(), $this->log),
            $this->core,
            $this->log
        );

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Get SysPage service
     */
    public function sysPage(): SysPage\Service\SysPage
    {
        $this->serviceCache[__METHOD__] ??= new SysPage\Service\SysPage(
            new SysPage\Service\Batch($this->createBatch(), $this->log),
            $this->core,
            $this->log
        );

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Get Template service
     */
    public function template(): Template\Service\Template
    {
        $this->serviceCache[__METHOD__] ??= new Template\Service\Template(
            new Template\Service\Batch($this->createBatch(), $this->log),
            $this->core,
            $this->log
        );

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Get Block service
     */
    public function block(): Block\Service\Block
    {
        $this->serviceCache[__METHOD__] ??= new Block\Service\Block(
            new Block\Service\Batch($this->createBatch(), $this->log),
            $this->core,
            $this->log
        );

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Get Repo service
     */
    public function repo(): Repo\Service\Repo
    {
        $this->serviceCache[__METHOD__] ??= new Repo\Service\Repo(
            new Repo\Service\Batch($this->createBatch(), $this->log),
            $this->core,
            $this->log
        );

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Get Demos service
     */
    public function demos(): Demos\Service\Demos
    {
        $this->serviceCache[__METHOD__] ??= new Demos\Service\Demos(
            new Demos\Service\Batch($this->createBatch(), $this->log),
            $this->core,
            $this->log
        );

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Get Role service
     */
    public function role(): Role\Service\Role
    {
        $this->serviceCache[__METHOD__] ??= new Role\Service\Role(
            new Role\Service\Batch($this->createBatch(), $this->log),
            $this->core,
            $this->log
        );

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Get RepoWidget service
     */
    public function repoWidget(): RepoWidget\Service\RepoWidget
    {
        $this->serviceCache[__METHOD__] ??= new RepoWidget\Service\RepoWidget(
            new RepoWidget\Service\Batch($this->createBatch(), $this->log),
            $this->core,
            $this->log
        );

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Creates a dedicated landing batch instance, batch commands state must not be shared between services
     */
    private function createBatch(): Batch
    {
        return new Batch($this->core, $this->log);
    }
}
