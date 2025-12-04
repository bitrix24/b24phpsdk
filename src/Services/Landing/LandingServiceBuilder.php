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
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Site\Service\Site(
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Get Page service
     */
    public function page(): Page\Service\Page
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Page\Service\Page(
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Get SysPage service
     */
    public function sysPage(): SysPage\Service\SysPage
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new SysPage\Service\SysPage(
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Get Template service
     */
    public function template(): Template\Service\Template
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Template\Service\Template(
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Get Block service
     */
    public function block(): Block\Service\Block
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Block\Service\Block(
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Get Repo service
     */
    public function repo(): Repo\Service\Repo
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Repo\Service\Repo(
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Get Demos service
     */
    public function demos(): Demos\Service\Demos
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Demos\Service\Demos(
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }
}
