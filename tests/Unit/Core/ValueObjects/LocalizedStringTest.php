<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Core\ValueObjects;

use Bitrix24\SDK\Core\Contracts\LangCodes;
use Bitrix24\SDK\Core\ValueObjects\LocalizedString;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(LocalizedString::class)]
class LocalizedStringTest extends TestCase
{
    #[Test]
    #[TestDox('single language via constructor maps to a lang => text array')]
    public function testSingleLanguage(): void
    {
        $this->assertSame(
            ['en' => 'My robot'],
            (new LocalizedString(LangCodes::EN, 'My robot'))->toArray()
        );
    }

    #[Test]
    #[TestDox('with() adds languages immutably (original is unchanged)')]
    public function testWithAddsLanguagesImmutably(): void
    {
        $localizedString = new LocalizedString(LangCodes::EN, 'My robot');
        $both = $localizedString->with(LangCodes::DE, 'Mein Roboter');

        $this->assertSame(['en' => 'My robot'], $localizedString->toArray());
        $this->assertSame(['en' => 'My robot', 'de' => 'Mein Roboter'], $both->toArray());
    }

    #[Test]
    #[TestDox('with() on the same language overwrites the value')]
    public function testWithOverwritesSameLanguage(): void
    {
        $this->assertSame(
            ['en' => 'new'],
            (new LocalizedString(LangCodes::EN, 'old'))->with(LangCodes::EN, 'new')->toArray()
        );
    }
}
