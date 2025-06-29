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

namespace Bitrix24\SDK\Application\Contracts\ApplicationInstallations\Entity;

use Bitrix24\SDK\Application\ApplicationStatus;
use Bitrix24\SDK\Application\PortalLicenseFamily;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\LogicException;
use Carbon\CarbonImmutable;
use Symfony\Component\Uid\Uuid;

interface ApplicationInstallationInterface
{
    /**
     * @return Uuid unique application installation id
     */
    public function getId(): Uuid;

    /**
     * @return CarbonImmutable date and time application installation create
     */
    public function getCreatedAt(): CarbonImmutable;

    /**
     * @return CarbonImmutable date and time application installation last change
     */
    public function getUpdatedAt(): CarbonImmutable;

    /**
     * Get Bitrix24 Account id related with this installation
     *
     * Return bitrix24 account with tokens related with user who installed application on portal
     *
     * @return Uuid bitrix24 account id
     */
    public function getBitrix24AccountId(): Uuid;

    /**
     * Get Contact Person id
     *
     * Return contact person id who install application on portal, optional
     *
     * @return Uuid|null get contact person id
     */
    public function getContactPersonId(): ?Uuid;

    /**
     * Link contact person
     *
     * Link client contact person if a client says they have new responsible for the application
     */
    public function linkContactPerson(Uuid $uuid): void;

    /**
     * Unlink contact person
     *
     * Unlink a client contact person if the client says the contact person has changed
     */
    public function unlinkContactPerson(): void;

    /**
     * Get Bitrix24 Partner contact person id, optional
     *
     * Return bitrix24 partner contact person id - if application supported wih another partner
     */
    public function getBitrix24PartnerContactPersonId(): ?Uuid;

    /**
     * Link Bitrix24 partner contact person
     *
     * Link Bitrix24 partner contact person if partner say he has new responsible for the application
     */
    public function linkBitrix24PartnerContactPerson(Uuid $uuid): void;

    /**
     * Unlink Bitrix24 partner contact person
     *
     * Unlink Bitrix24 partner contacts the person if the partner says they remove this employee
     */
    public function unlinkBitrix24PartnerContactPerson(): void;

    /**
     * @return Uuid|null get Bitrix24 Partner id related with this installation, optional
     */
    public function getBitrix24PartnerId(): ?Uuid;

    /**
     * Link Bitrix24 partner
     *
     * Link Bitrix24 partner who supports this portal
     */
    public function linkBitrix24Partner(Uuid $uuid): void;

    /**
     * Unlink Bitrix24 partner
     *
     * Unlink Bitrix24 partner who stops supporting this portal
     */
    public function unlinkBitrix24Partner(): void;

    /**
     * Get external id for application installation
     *
     * Return external id for application installation related entity in crm or erp - lead or deal id
     */
    public function getExternalId(): ?string;

    /**
     * Get external id for application installation
     *
     * Set external id for application installation related entity in crm or erp - lead or deal id
     * @param non-empty-string|null $externalId
     * @throws InvalidArgumentException
     */
    public function setExternalId(?string $externalId): void;

    /**
     * Get application installation status
     *
     * new - started the installation procedure, but have not yet finalized, there is no “installation completed”
     * active - installation finished, active portal, there is a connection to B24
     * deleted - application has been removed from the portal
     * blocked - lost connection with the portal or the developer forcibly deactivated the account
     */
    public function getStatus(): ApplicationInstallationStatus;

    /**
     * Finish application installation
     *
     * Application installed on portal and finish installation flow. Method must set the status from «new» to «active»
     * If You installed application without UI, You already have an application token in OnApplicationInstall event
     * If You installed application with UI, You can finish the installation flow without a token and receive it in a separate request with OnApplicationInstall event
     *
     * @param non-empty-string|null $applicationToken
     * @throws InvalidArgumentException
     */
    public function applicationInstalled(?string $applicationToken = null): void;

    /**
     * Application uninstalled
     *
     * Application can be uninstalled by:
     * - admin on portal active → deleted statuses
     * - if installation will not complete new → blocked → deleted by background task
     * @param string|null $applicationToken Application uninstalled from portal, set status «deleted»
     * @throws InvalidArgumentException
     */
    public function applicationUninstalled(?string $applicationToken = null): void;

    /**
     * Check is application token valid
     *
     * @param non-empty-string $applicationToken
     * @link https://training.bitrix24.com/rest_help/general/events/event_safe.php
     */
    public function isApplicationTokenValid(string $applicationToken): bool;

    /**
     * Set application token from OnApplicationInstall event
     *
     * If the application is installed without UI, You already have an application token in OnApplicationInstall event
     * If the application is installed with UI, You can finish the installation flow without a token and receive it in a separate request with OnApplicationInstall event
     *
     * @param non-empty-string $applicationToken
     * @throws InvalidArgumentException
     */
    public function setApplicationToken(string $applicationToken): void;

    /**
     * Change status to active for blocked application installation accounts
     *
     * You can activate accounts only blocked state
     *
     * @param non-empty-string|null $comment
     * @throws LogicException
     */
    public function markAsActive(?string $comment): void;

    /**
     * Change status to blocked for application installation accounts in state new or active
     *
     *  You can block installation account if you need temporally  stop installation work
     *
     * @param non-empty-string|null $comment
     * @throws LogicException
     */
    public function markAsBlocked(?string $comment): void;

    /**
     * Get application status
     *
     * Return current application status stored in persistence storage.
     * This method do not call bitrix24 rest api to get actual data
     * @link https://training.bitrix24.com/rest_help/general/app_info.php
     */
    public function getApplicationStatus(): ApplicationStatus;

    /**
     * Change application status
     *
     * You can check application status in periodical background task and store it in persistence storage for BI analytics
     * @link https://training.bitrix24.com/rest_help/general/app_info.php
     */
    public function changeApplicationStatus(ApplicationStatus $applicationStatus): void;

    /**
     * Get bitrix24 tariff plan designation without specified region.
     *
     * Return current bitrix24 tariff plan designation without specified region stored in persistence storage.
     * This method do not call bitrix24 rest api to get actual data
     * @link https://training.bitrix24.com/rest_help/general/app_info.php
     */
    public function getPortalLicenseFamily(): PortalLicenseFamily;

    /**
     * Change plan designation without specified region.
     *
     * You can check portal license family in periodical background task and store it in persistence storage for BI analytics
     * @link https://training.bitrix24.com/rest_help/general/app_info.php
     */
    public function changePortalLicenseFamily(PortalLicenseFamily $portalLicenseFamily): void;

    /**
     * Get bitrix24 portal users count
     *
     * Return bitrix24 portal users count stored in persistence storage
     * This method do not call bitrix24 rest api to get actual data
     */
    public function getPortalUsersCount(): ?int;

    /**
     * Change bitrix24 portal users count
     *
     *  You can check portal users count background task and store it in persistence storage for BI analytics
     */
    public function changePortalUsersCount(int $usersCount): void;

    /**
     * Get comment
     */
    public function getComment(): ?string;
}
