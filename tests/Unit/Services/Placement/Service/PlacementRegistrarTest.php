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

namespace Bitrix24\SDK\Tests\Unit\Services\Placement\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\Placement\Common\PlacementHandlerMetadata;
use Bitrix24\SDK\Services\Placement\PlacementConflictPolicy;
use Bitrix24\SDK\Services\Placement\PlacementRegistrationStatus;
use Bitrix24\SDK\Services\Placement\Service\Placement;
use Bitrix24\SDK\Services\Placement\Service\PlacementRegistrar;
use Bitrix24\SDK\Tests\Unit\Services\Placement\ResponseFixture;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class PlacementRegistrarTest extends TestCase
{
    use ResponseFixture;

    private function declaration(string $title = 'Tab', array $options = [], ?int $userId = null, string $url = 'https://example.com/widget'): PlacementHandlerMetadata
    {
        return new PlacementHandlerMetadata('crm_contact_detail_tab', $url, ['en' => ['TITLE' => $title]], $options, $userId);
    }

    private function row(string $title = 'Tab', array $options = [], int $userId = 0, string $url = 'https://example.com/widget'): array
    {
        return ['id' => '1', 'placement' => 'CRM_CONTACT_DETAIL_TAB', 'handler' => $url, 'userId' => $userId, 'options' => $options, 'langAll' => ['en' => ['TITLE' => $title]], 'title' => $title, 'description' => ''];
    }

    private function bindPayload(PlacementHandlerMetadata $declaration): array
    {
        return ['PLACEMENT' => $declaration->placementCode, 'HANDLER' => $declaration->handlerUrl->getUrl(), 'LANG_ALL' => ['en' => ['TITLE' => $declaration->lang['en']['TITLE'], 'DESCRIPTION' => '', 'GROUP_NAME' => '']], 'OPTIONS' => $declaration->options, 'USER_ID' => $declaration->b24UserId];
    }

    /** @param list<array{string, array, Response|\Throwable}> $steps */
    private function registrar(array $steps): PlacementRegistrar
    {
        $core = $this->createMock(CoreInterface::class);
        $core->expects(self::exactly(count($steps)))->method('call')->willReturnCallback(function (string $method, array $parameters = []) use (&$steps): Response {
            [$expectedMethod, $expectedParameters, $response] = array_shift($steps);
            self::assertSame($expectedMethod, $method);
            self::assertSame($expectedParameters, $parameters);
            if ($response instanceof \Throwable) {
                throw $response;
            }

            return $response;
        });
        return new PlacementRegistrar(new Placement($core, new NullLogger()));
    }

    public function testEmptyDeclarationsDoNotCallApi(): void
    {
        self::assertSame([], $this->registrar([])->register([])->getItems());
    }

    public function testAllTypesValidatedBeforeNetwork(): void
    {
        $registrar = $this->registrar([]);
        $this->expectException(InvalidArgumentException::class);
        /** @var mixed $invalid */
        $invalid = 'invalid';
        $registrar->register([$this->declaration(), $invalid]);
    }

    public function testDuplicateIdentityRejectedBeforeNetwork(): void
    {
        $registrar = $this->registrar([]);
        $this->expectException(InvalidArgumentException::class);
        $registrar->register([$this->declaration(), $this->declaration('Different')]);
    }

    public function testCreatesMissingDeclarationAndRepeatedInstallerSkips(): void
    {
        $declaration = $this->declaration();
        $registrar = $this->registrar([
            ['placement.get', [], $this->response([])],
            ['placement.bind', $this->bindPayload($declaration), $this->response(true)],
            ['placement.get', [], $this->response([$this->row()])],
        ]);
        self::assertSame(PlacementRegistrationStatus::created, $registrar->register([$declaration])->getItems()[0]->status);
        self::assertSame(PlacementRegistrationStatus::skipped, $registrar->register([$declaration])->getItems()[0]->status);
    }

    public function testMapOrderAndLanguageCaseAndOptionalEmptyFieldsAreEquivalent(): void
    {
        $declaration = new PlacementHandlerMetadata('code', 'https://example.com/', ['EN' => ['TITLE' => 'Tab']], ['nested' => ['a' => 1, 'b' => [2, 3]], 'extra' => true]);
        $row = ['placement' => 'code', 'handler' => 'https://example.com/', 'userId' => '0', 'options' => ['extra' => true, 'nested' => ['b' => [2, 3], 'a' => 1]], 'langAll' => ['en' => ['GROUP_NAME' => '', 'title' => 'Tab', 'DESCRIPTION' => '']]];
        self::assertSame(PlacementRegistrationStatus::skipped, $this->registrar([['placement.get', [], $this->response([$row])]])->register([$declaration])->getItems()[0]->status);
    }

    public static function conflictingConfigurations(): array
    {
        return [['Changed', []], ['Tab', ['x' => 1]], ['Tab', ['x' => '1']], ['Tab', ['list' => [2, 1]]]];
    }

    #[DataProvider('conflictingConfigurations')]
    public function testConflictsFailWithoutMutations(string $title, array $options): void
    {
        $result = $this->registrar([['placement.get', [], $this->response([$this->row()])]])->register([$this->declaration($title, $options)]);
        self::assertFalse($result->isSuccess());
        self::assertSame(PlacementRegistrationStatus::failed, $result->getItems()[0]->status);
        self::assertInstanceOf(BaseException::class, $result->getItems()[0]->error);
    }

    public function testOptionsScalarTypesAndListOrderAreSignificant(): void
    {
        foreach ([[['value' => 1], ['value' => '1']], [['value' => [1, 2]], ['value' => [2, 1]]]] as [$remote, $requested]) {
            $result = $this->registrar([['placement.get', [], $this->response([$this->row(options: $remote)])]])->register([$this->declaration(options: $requested)]);
            self::assertFalse($result->isSuccess());
        }
    }

    public function testDifferentUrlsAndUsersRemainIndependentAndGetCalledOnce(): void
    {
        $declarations = [$this->declaration(), $this->declaration(userId: 8), $this->declaration(url: 'https://example.com/Widget')];
        $steps = [['placement.get', [], $this->response([$this->row(userId: 9), $this->row(url: 'https://example.com/unmentioned')])]];
        foreach ($declarations as $declaration) {
            $steps[] = ['placement.bind', $this->bindPayload($declaration), $this->response(true)];
        }

        $result = $this->registrar($steps)->register($declarations);
        self::assertTrue($result->isSuccess());
        self::assertSame($declarations, array_map(static fn ($item) => $item->declaration, $result->getItems()));
    }

    public static function unobservableRows(): array
    {
        return [['options'], ['langAll'], ['legacy'], ['invalid-options'], ['invalid-lang'], ['invalid-user']];
    }

    #[DataProvider('unobservableRows')]
    public function testUnobservableConfigurationFailsConservatively(string $variant): void
    {
        $row = $this->row();
        if ($variant === 'legacy') {
            $row['langAll'] = [];
        } elseif ($variant === 'invalid-options') {
            $row['options'] = 'invalid';
        } elseif ($variant === 'invalid-lang') {
            $row['langAll'] = ['en' => 'invalid'];
        } elseif ($variant === 'invalid-user') {
            $row['userId'] = 'unknown';
        } else {
            unset($row[$variant]);
        }

        $result = $this->registrar([['placement.get', [], $this->response([$row])]])->register([$this->declaration()], PlacementConflictPolicy::replace);
        self::assertSame(PlacementRegistrationStatus::failed, $result->getItems()[0]->status);
    }

    public function testDuplicateRemoteIdentityNeverDeletes(): void
    {
        $result = $this->registrar([['placement.get', [], $this->response([$this->row(), $this->row()])]])->register([$this->declaration('Changed')], PlacementConflictPolicy::replace);
        self::assertFalse($result->isSuccess());
    }

    public static function replacementUsers(): array
    {
        return [[null, 0], [8, 8]];
    }

    #[DataProvider('replacementUsers')]
    public function testReplaceUnbindsExactIdentityBeforeBinding(?int $userId, int $unbindUserId): void
    {
        $declaration = $this->declaration('Changed', ['x' => 1], $userId);
        $result = $this->registrar([
            ['placement.get', [], $this->response([$this->row(userId: $unbindUserId)])],
            ['placement.unbind', ['PLACEMENT' => $declaration->placementCode, 'HANDLER' => $declaration->handlerUrl->getUrl(), 'USER_ID' => $unbindUserId], $this->response(['count' => 1])],
            ['placement.bind', $this->bindPayload($declaration), $this->response(true)],
        ])->register([$declaration], conflictPolicy: PlacementConflictPolicy::replace);
        self::assertTrue($result->isSuccess());
        self::assertSame(PlacementRegistrationStatus::updated, $result->getItems()[0]->status);
    }

    public static function wrongUnbindCounts(): array
    {
        return [[0], [2]];
    }

    #[DataProvider('wrongUnbindCounts')]
    public function testWrongUnbindCountDoesNotBind(int $count): void
    {
        $result = $this->registrar([
            ['placement.get', [], $this->response([$this->row()])],
            ['placement.unbind', ['PLACEMENT' => 'CRM_CONTACT_DETAIL_TAB', 'HANDLER' => 'https://example.com/widget', 'USER_ID' => 0], $this->response(['count' => $count])],
        ])->register([$this->declaration('Changed')], PlacementConflictPolicy::replace);
        self::assertFalse($result->isSuccess());
        self::assertNull($result->getItems()[0]->previousConfigurationRestored);
    }

    public function testInitialUnbindFailureDoesNotBind(): void
    {
        $exception = new TransportException('network error');
        $result = $this->registrar([
            ['placement.get', [], $this->response([$this->row()])],
            ['placement.unbind', ['PLACEMENT' => 'CRM_CONTACT_DETAIL_TAB', 'HANDLER' => 'https://example.com/widget', 'USER_ID' => 0], $exception],
        ])->register([$this->declaration('Changed')], PlacementConflictPolicy::replace);
        self::assertSame($exception, $result->getItems()[0]->error);
    }

    public function testFalseBindAndLazyApiErrorAndTransportFailureContinueIndependentItems(): void
    {
        $declarations = [$this->declaration(), $this->declaration(userId: 8), $this->declaration(userId: 9), $this->declaration(userId: 10)];
        $responses = [$this->response(false), $this->responseBody(['error' => 'ACCESS_DENIED', 'error_description' => 'denied'], 'placement.bind'), new TransportException('network error'), $this->response(true)];
        $steps = [['placement.get', [], $this->response([])]];
        foreach ($declarations as $index => $declaration) {
            $steps[] = ['placement.bind', $this->bindPayload($declaration), $responses[$index]];
        }

        $result = $this->registrar($steps)->register($declarations);
        self::assertSame([PlacementRegistrationStatus::failed, PlacementRegistrationStatus::failed, PlacementRegistrationStatus::failed, PlacementRegistrationStatus::created], array_map(static fn ($item) => $item->status, $result->getItems()));
        self::assertStringContainsString('denied', $result->getItems()[1]->error->getMessage());
        self::assertSame($responses[2], $result->getItems()[2]->error);
    }

    public function testInitialGetFailurePropagatesBeforeWrites(): void
    {
        $registrar = $this->registrar([['placement.get', [], $this->responseBody(['error' => 'ACCESS_DENIED', 'error_description' => 'read denied'], 'placement.get')]]);
        $this->expectException(BaseException::class);
        $this->expectExceptionMessage('read denied');
        $registrar->register([$this->declaration()]);
    }

    public static function rollbackOutcomes(): array
    {
        return [[true], [false]];
    }

    #[DataProvider('rollbackOutcomes')]
    public function testFailedReplacementRestoresSavedConfigurationAndReportsOutcome(bool $restore): void
    {
        $original = $this->row('Original', ['unknown' => ['key' => 1]], 8);
        $declaration = $this->declaration('Changed', userId: 8);
        $error = new BaseException('new bind failed');
        $result = $this->registrar([
            ['placement.get', [], $this->response([$original])],
            ['placement.unbind', ['PLACEMENT' => $declaration->placementCode, 'HANDLER' => $declaration->handlerUrl->getUrl(), 'USER_ID' => 8], $this->response(['count' => 1])],
            ['placement.bind', $this->bindPayload($declaration), $error],
            ['placement.bind', ['PLACEMENT' => $original['placement'], 'HANDLER' => $original['handler'], 'LANG_ALL' => $original['langAll'], 'OPTIONS' => $original['options'], 'USER_ID' => 8], $this->response($restore)],
        ])->register([$declaration], PlacementConflictPolicy::replace);
        $item = $result->getItems()[0];
        self::assertFalse($result->isSuccess());
        self::assertSame($error, $item->error);
        self::assertSame($restore, $item->previousConfigurationRestored);
        if ($restore) {
            self::assertNull($item->rollbackError);
        } else {
            self::assertInstanceOf(BaseException::class, $item->rollbackError);
        }
    }

    public function testMissingUserIdentityCannotBeReplaced(): void
    {
        $row = $this->row();
        unset($row['userId']);
        $result = $this->registrar([['placement.get', [], $this->response([$row])]])->register([$this->declaration('Changed')], PlacementConflictPolicy::replace);
        self::assertFalse($result->isSuccess());
    }

    public function testMalformedRawRowsFailWithoutMutations(): void
    {
        $result = $this->registrar([['placement.get', [], $this->response([['handler' => 'https://example.com/widget']])]])->register([$this->declaration()], PlacementConflictPolicy::replace);
        self::assertSame(PlacementRegistrationStatus::failed, $result->getItems()[0]->status);
    }

    public function testInitialTransportFailurePropagates(): void
    {
        $error = new TransportException('read transport failed');
        $registrar = $this->registrar([['placement.get', [], $error]]);
        $this->expectExceptionObject($error);
        $registrar->register([$this->declaration()]);
    }

    public function testProgrammingExceptionsAreNotSwallowed(): void
    {
        $declaration = $this->declaration();
        $error = new \LogicException('programming defect');
        $registrar = $this->registrar([
            ['placement.get', [], $this->response([])],
            ['placement.bind', $this->bindPayload($declaration), $error],
        ]);
        $this->expectExceptionObject($error);
        $registrar->register([$declaration]);
    }

    public function testAllLanguageConfigurationsValidatedBeforeNetwork(): void
    {
        $registrar = $this->registrar([]);
        $this->expectException(InvalidArgumentException::class);
        /** @var mixed $invalidLanguage */
        $invalidLanguage = ['en' => 'invalid'];
        $registrar->register([$this->declaration(), new PlacementHandlerMetadata('CODE', 'https://example.com/', $invalidLanguage)]);
    }

    public static function replacementFailures(): array
    {
        return [['false', 'api-error'], ['api-error', 'transport'], ['transport', 'true']];
    }

    #[DataProvider('replacementFailures')]
    public function testFailedReplacementConsumesLazyErrorsAndTracksRestoration(string $bindFailure, string $rollbackOutcome): void
    {
        $responses = [
            'false' => $this->response(false),
            'api-error' => $this->responseBody(['error' => 'ACCESS_DENIED', 'error_description' => 'bind denied'], 'placement.bind'),
            'transport' => new TransportException('bind transport failed'),
            'true' => $this->response(true),
        ];
        $declaration = $this->declaration('Changed');
        $result = $this->registrar([
            ['placement.get', [], $this->response([$this->row()])],
            ['placement.unbind', ['PLACEMENT' => $declaration->placementCode, 'HANDLER' => $declaration->handlerUrl->getUrl(), 'USER_ID' => 0], $this->response(['count' => 1])],
            ['placement.bind', $this->bindPayload($declaration), $responses[$bindFailure]],
            ['placement.bind', ['PLACEMENT' => 'CRM_CONTACT_DETAIL_TAB', 'HANDLER' => 'https://example.com/widget', 'LANG_ALL' => ['en' => ['TITLE' => 'Tab']], 'OPTIONS' => [], 'USER_ID' => null], $responses[$rollbackOutcome]],
        ])->register([$declaration], PlacementConflictPolicy::replace);
        $item = $result->getItems()[0];
        self::assertSame(PlacementRegistrationStatus::failed, $item->status);
        self::assertInstanceOf(BaseException::class, $item->error);
        self::assertSame($rollbackOutcome === 'true', $item->previousConfigurationRestored);
        if ($rollbackOutcome === 'true') {
            self::assertNull($item->rollbackError);
        } else {
            self::assertInstanceOf(BaseException::class, $item->rollbackError);
        }
    }

    public function testMissingAndNullUserIdsMatchGlobalIdentity(): void
    {
        foreach ([false, true] as $nullUser) {
            $row = $this->row();
            if ($nullUser) {
                $row['userId'] = null;
            } else {
                unset($row['userId']);
            }

            $result = $this->registrar([['placement.get', [], $this->response([$row])]])->register([$this->declaration()]);
            self::assertSame(PlacementRegistrationStatus::skipped, $result->getItems()[0]->status);
        }
    }


    public static function restorationUserIdentities(): array
    {
        return [[0, null], [null, null], [8, 8]];
    }

    #[DataProvider('restorationUserIdentities')]
    public function testRestorationMapsStoredGlobalUserToNullBindIdentity(?int $storedUserId, ?int $bindUserId): void
    {
        $original = $this->row('Original', ['unknown' => 'preserved']);
        $original['userId'] = $storedUserId;
        $declaration = $this->declaration('Changed', userId: $bindUserId);
        $error = new BaseException('new bind failed');
        $result = $this->registrar([
            ['placement.get', [], $this->response([$original])],
            ['placement.unbind', ['PLACEMENT' => $declaration->placementCode, 'HANDLER' => $declaration->handlerUrl->getUrl(), 'USER_ID' => $storedUserId ?? 0], $this->response(['count' => 1])],
            ['placement.bind', $this->bindPayload($declaration), $error],
            ['placement.bind', ['PLACEMENT' => $original['placement'], 'HANDLER' => $original['handler'], 'LANG_ALL' => $original['langAll'], 'OPTIONS' => $original['options'], 'USER_ID' => $bindUserId], $this->response(true)],
        ])->register([$declaration], PlacementConflictPolicy::replace);
        self::assertSame($error, $result->getItems()[0]->error);
        self::assertTrue($result->getItems()[0]->previousConfigurationRestored);
        self::assertFalse($result->isSuccess());
    }


    public static function invalidOptionalLanguageFields(): array
    {
        return [
            [['en' => ['TITLE' => 'Tab', 'DESCRIPTION' => ['bad']]]],
            [['en' => ['TITLE' => 'Tab', 'DESCRIPTION' => null]]],
            [['en' => ['TITLE' => 'Tab', 'GROUP_NAME' => 1]]],
            [['en' => ['TITLE' => 'Tab', 'GROUP_NAME' => false]]],
            [['en' => ['TITLE' => 'Tab', 'GROUP_NAME' => null]]],
        ];
    }

    #[DataProvider('invalidOptionalLanguageFields')]
    public function testOptionalLanguageFieldsValidatedAcrossAllDeclarationsBeforeNetwork(array $lang): void
    {
        $registrar = $this->registrar([]);
        $this->expectException(InvalidArgumentException::class);
        $registrar->register([$this->declaration(), new PlacementHandlerMetadata('CODE', 'https://example.com/', $lang)], PlacementConflictPolicy::replace);
    }

    public static function collidingLanguageKeys(): array
    {
        return [
            [['EN' => ['TITLE' => 'First'], 'en' => ['TITLE' => 'Second']]],
            [['en' => ['TITLE' => 'First', 'title' => 'Second']]],
            [['en' => ['TITLE' => 'Tab', 'DESCRIPTION' => 'First', 'description' => 'Second']]],
            [['en' => ['TITLE' => 'Tab', 'CUSTOM' => 'First', 'custom' => 'Second']]],
        ];
    }

    #[DataProvider('collidingLanguageKeys')]
    public function testLanguageCaseCollisionsRejectedBeforeNetwork(array $lang): void
    {
        $registrar = $this->registrar([]);
        $this->expectException(InvalidArgumentException::class);
        $registrar->register([$this->declaration(), new PlacementHandlerMetadata('CODE', 'https://example.com/', $lang)], PlacementConflictPolicy::replace);
    }

    #[DataProvider('collidingLanguageKeys')]
    public function testExistingLanguageCaseCollisionsCannotBeSkippedOrReplaced(array $lang): void
    {
        $row = $this->row();
        $row['langAll'] = $lang;
        $result = $this->registrar([['placement.get', [], $this->response([$row])]])->register([$this->declaration()], PlacementConflictPolicy::replace);
        self::assertSame(PlacementRegistrationStatus::failed, $result->getItems()[0]->status);
    }

    #[DataProvider('invalidOptionalLanguageFields')]
    public function testExistingInvalidOptionalLanguageFieldsCannotBeReplaced(array $lang): void
    {
        $row = $this->row();
        $row['langAll'] = $lang;
        $result = $this->registrar([['placement.get', [], $this->response([$row])]])->register([$this->declaration('Changed')], PlacementConflictPolicy::replace);
        self::assertSame(PlacementRegistrationStatus::failed, $result->getItems()[0]->status);
    }

    public function testBindSendsTheSameNormalizedLanguageSemanticsUsedForComparison(): void
    {
        /** @var mixed $lang */
        $lang = ['EN' => ['title' => 'Tab', 'custom' => 'preserved']];
        $declaration = new PlacementHandlerMetadata('CODE', 'https://example.com/', $lang);
        $result = $this->registrar([
            ['placement.get', [], $this->response([])],
            ['placement.bind', ['PLACEMENT' => 'CODE', 'HANDLER' => 'https://example.com/', 'LANG_ALL' => ['en' => ['TITLE' => 'Tab', 'CUSTOM' => 'preserved', 'DESCRIPTION' => '', 'GROUP_NAME' => '']], 'OPTIONS' => [], 'USER_ID' => null], $this->response(true)],
        ])->register([$declaration]);
        self::assertSame(PlacementRegistrationStatus::created, $result->getItems()[0]->status);
        self::assertSame(['EN' => ['title' => 'Tab', 'custom' => 'preserved']], $declaration->lang);
    }

    public static function mapAndListOptions(): array
    {
        return [
            [['nested' => [1 => 'a', 0 => 'b']], ['nested' => ['b', 'a']]],
            [['nested' => ['b', 'a']], ['nested' => [1 => 'a', 0 => 'b']]],
            [['nested' => ['deep' => [1 => 'a', 0 => 'b']]], ['nested' => ['deep' => ['b', 'a']]]],
        ];
    }

    #[DataProvider('mapAndListOptions')]
    public function testNestedOptionsMapAndListShapesConflict(array $existing, array $requested): void
    {
        $result = $this->registrar([['placement.get', [], $this->response([$this->row(options: $existing)])]])->register([$this->declaration(options: $requested)]);
        self::assertSame(PlacementRegistrationStatus::failed, $result->getItems()[0]->status);
        self::assertFalse($result->isSuccess());
    }


    public function testDistinctNumericLookingMapKeysRemainOrderIndependent(): void
    {
        $existing = ['nested' => ['01' => 'padded', 1 => 'number']];
        $requested = ['nested' => [1 => 'number', '01' => 'padded']];
        $result = $this->registrar([['placement.get', [], $this->response([$this->row(options: $existing)])]])->register([$this->declaration(options: $requested)]);
        self::assertSame(PlacementRegistrationStatus::skipped, $result->getItems()[0]->status);
    }

}
