<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\String;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\String\TransformString;

class TransformStringTest extends TestCase
{
    public function safeFilenameProvider()
    {
        return [
            [
                'actual' => 'ěščřžýáíé.dat',
                'expected' => 'escrzyaie.dat',
            ],
            [
                'actual' => 'ĚŠČŘŽÝÁÍÉ.DAT',
                'expected' => 'ESCRZYAIE.DAT',
            ],
            [
                'actual' => 'Foo     Bar.dat',
                'expected' => 'Foo_Bar.dat',
            ],
            [
                'actual' => 'Foo-Bar.dat',
                'expected' => 'Foo-Bar.dat',
            ],
            [
                'actual' => '../../Foo.dat',
                'expected' => '_._Foo.dat',
            ],
            [
                'actual' => '..\\..\\Foo.dat',
                'expected' => '_._Foo.dat',
            ],
            [
                'actual' => '.foo.dat',
                'expected' => 'foo.dat',
            ],
            [
                'actual' => 'BG 747 fixˇ.dat',
                'expected' => 'BG_747_fix.dat',
            ],
        ];
    }

    /**
     * @dataProvider safeFilenameProvider
     * @param mixed $actual
     * @param mixed $expected
     */
    public function testSafeFilename($actual, $expected)
    {
        $this->assertSame($expected, TransformString::safeFilename($actual));
    }

    public function stringToFriendlyUrlSlugProvider()
    {
        return [
            [
                'actual' => 'ěščřžýáíé foo',
                'expected' => 'escrzyaie-foo',
            ],
            [
                'actual' => 'ĚŠČŘŽÝÁÍÉ   ',
                'expected' => 'escrzyaie',
            ],
            [
                'actual' => 'Foo     Bar-Baz',
                'expected' => 'foo-bar-baz',
            ],
            [
                'actual' => 'foo-bar_baz',
                'expected' => 'foo-bar_baz',
            ],
            [
                'actual' => '$€@!?<>=;~%^&',
                'expected' => '',
            ],
            [
                'actual' => 'Příliš žluťoučký kůň úpěl ďábelské ódy',
                'expected' => 'prilis-zlutoucky-kun-upel-dabelske-ody',
            ],
            [
                'actual' => 'BG-747 is fixedˇ',
                'expected' => 'bg-747-is-fixed',
            ],
        ];
    }

    /**
     * @dataProvider stringToFriendlyUrlSlugProvider
     * @param mixed $actual
     * @param mixed $expected
     */
    public function testStringToFriendlyUrlSlug($actual, $expected)
    {
        $this->assertSame($expected, TransformString::stringToFriendlyUrlSlug($actual));
    }

    public function stringToCamelCaseProvider()
    {
        return [
            [
                'actual' => 'ěščřžýáíé foo',
                'expected' => 'escrzyaieFoo',
            ],
            [
                'actual' => 'ĚŠČŘŽÝÁÍÉ   ',
                'expected' => 'escrzyaie',
            ],
            [
                'actual' => 'Foo     Bar-Baz',
                'expected' => 'fooBarBaz',
            ],
            [
                'actual' => 'foo-bar_baz',
                'expected' => 'fooBarBaz',
            ],
            [
                'actual' => '$€@!?<>=;~%^&',
                'expected' => '',
            ],
            [
                'actual' => 'Příliš žluťoučký kůň úpěl ďábelské ódy',
                'expected' => 'prilisZlutouckyKunUpelDabelskeOdy',
            ],
            [
                'actual' => 'BG-747 is fixedˇ',
                'expected' => 'bg747IsFixed',
            ],
            [
                'actual' => 'camelCase-camelCase',
                'expected' => 'camelCaseCamelCase',
            ],
            [
                'actual' => 'camelCaseACRONYM ACRONYM',
                'expected' => 'camelCaseAcronymAcronym',
            ],
        ];
    }

    /**
     * @dataProvider stringToCamelCaseProvider
     * @param mixed $actual
     * @param mixed $expected
     */
    public function testStringToCamelCase($actual, $expected)
    {
        $this->assertSame($expected, TransformString::stringToCamelCase($actual));
    }

    /**
     * @return array
     */
    public function stringTrailingSlashesProvider(): array
    {
        return [
            [
                'foo',
                'foo/',
            ],
            [
                'foo/bar',
                'foo/bar/',
            ],
            [
                'foo/',
                'foo',
            ],
            [
                'foo/bar/',
                'foo/bar',
            ],
            [
                '',
                '/',
            ],
            [
                '/',
                '',
            ],
        ];
    }

    /**
     * @dataProvider stringTrailingSlashesProvider
     * @param string $string
     * @param string $expected
     */
    public function testAddOrRemoveTrailingSlashFromString(string $string, string $expected): void
    {
        static::assertSame($expected, TransformString::addOrRemoveTrailingSlashFromString($string));
    }

    /**
     * @dataProvider trimmedStringOrNullProvider
     * @param string|null $original
     * @param string|null $expected
     */
    public function testGetTrimmedStringOrNullOnEmpty(?string $original, ?string $expected): void
    {
        static::assertSame($expected, TransformString::getTrimmedStringOrNullOnEmpty($original));
    }

    /**
     * @return array
     */
    public function trimmedStringOrNullProvider(): array
    {
        return [
            [
                'foo ',
                'foo',
            ],
            [
                'foo  ',
                'foo',
            ],
            [
                "\t  foo",
                'foo',
            ],
            [
                "foo\n\t",
                'foo',
            ],
            [
                '',
                null,
            ],
            [
                '  ',
                null,
            ],
        ];
    }
}
