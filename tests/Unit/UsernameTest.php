<?php

declare(strict_types=1);

use App\Validation\Username;
use Illuminate\Support\Facades\Validator;

/**
 * @param  array<int, string>  $extraRules
 */
function validateUsername(string $username, array $extraRules = []): bool
{
    return Validator::make(
        ['username' => $username],
        ['username' => [...$extraRules, ...Username::strict()]]
    )->passes();
}

describe('Username', function () {
    describe('accepted characters', function () {
        it('accepts usernames built from letters, numbers and underscores', function (string $username) {
            expect(validateUsername($username))->toBeTrue();
        })->with([
            'lowercase letters'  => ['testuser'],
            'uppercase letters'  => ['TESTUSER'],
            'mixed case'         => ['TestUser'],
            'digits only'        => ['123456'],
            'underscores only'   => ['___'],
            'letters and digits' => ['user123'],
            'leading underscore' => ['_testuser'],
            'leading digit'      => ['1testuser'],
            'all three'          => ['Test_User_123'],
            'single character'   => ['a'],
            'maximum length'     => [str_repeat('a', Username::MAX_LENGTH)],
        ]);
    });

    describe('rejected characters', function () {
        it('rejects usernames containing anything else', function (string $username) {
            expect(validateUsername($username))->toBeFalse();
        })->with([
            'accented letters'    => ['tëstüser'],
            'cyrillic letters'    => ['тестюзер'],
            'cjk characters'      => ['テストユーザー'],
            'emoji'               => ['test🔥user'],
            'ligature'            => ['teﬀstuser'],
            'mathematical script' => ['𝓊𝓈𝑒𝓇'],
            'space'               => ['test user'],
            'leading space'       => [' testuser'],
            'trailing space'      => ['testuser '],
            'tab'                 => ["test\tuser"],
            'newline'             => ["test\nuser"],
            'dot'                 => ['test.user'],
            'dash'                => ['test-user'],
            'plus'                => ['test+user'],
            'at sign'             => ['test@user'],
            'hash'                => ['test#user'],
            'percent'             => ['test%user'],
            'forward slash'       => ['test/user'],
            'back slash'          => ['test\\user'],
            'single quote'        => ["test'user"],
            'double quote'        => ['test"user'],
            'semicolon'           => ['test;user'],
            'null byte'           => ["test\0user"],
            'path traversal'      => ['../../etc/passwd'],
            'html tag'            => ['<script>alert(1)</script>'],
            'sql fragment'        => ["admin'--"],
        ]);

        it('rejects a blank username once paired with required, as the commands pair it', function (string $username) {
            expect(validateUsername($username, ['required']))->toBeFalse();
        })->with([
            'empty string'    => [''],
            'single space'    => [' '],
            'multiple spaces' => ['   '],
        ]);
    });

    describe('length', function () {
        it('accepts a username at the maximum length', function () {
            expect(validateUsername(str_repeat('a', Username::MAX_LENGTH)))->toBeTrue();
        });

        it('rejects a username longer than the maximum length', function () {
            expect(validateUsername(str_repeat('a', Username::MAX_LENGTH + 1)))->toBeFalse();
        });
    });

    describe('rule set', function () {
        it('exposes a maximum length of 40 characters', function () {
            expect(Username::MAX_LENGTH)->toBe(40);
        });

        it('builds a rule set covering type, length and format', function () {
            expect(Username::strict())->toBe([
                'string',
                'max:40',
                'regex:'.Username::PATTERN,
            ]);
        });

        it('provides a message naming the allowed characters', function () {
            expect(Username::message())
                ->toBe('The :attribute may only contain letters, numbers and underscores.');
        });
    });
});
