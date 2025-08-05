<?php

declare(strict_types=1);

use App\Enums\UserStatus;

describe('UserStatus Enum', function () {
    describe('Enum Cases', function () {
        it('has all expected cases', function () {
            expect(UserStatus::cases())->toHaveCount(4);
            expect(UserStatus::ACTIVE)->toBeInstanceOf(UserStatus::class);
            expect(UserStatus::INACTIVE)->toBeInstanceOf(UserStatus::class);
            expect(UserStatus::SUSPENDED)->toBeInstanceOf(UserStatus::class);
            expect(UserStatus::PENDING)->toBeInstanceOf(UserStatus::class);
        });

        it('has correct string values', function () {
            expect(UserStatus::ACTIVE->value)->toBe('active');
            expect(UserStatus::INACTIVE->value)->toBe('inactive');
            expect(UserStatus::SUSPENDED->value)->toBe('suspended');
            expect(UserStatus::PENDING->value)->toBe('pending');
        });

        it('can be compared directly', function () {
            expect(UserStatus::ACTIVE)->toBe(UserStatus::ACTIVE);
            expect(UserStatus::ACTIVE)->not->toBe(UserStatus::INACTIVE);
        });

        it('can be used in switch/match statements', function () {
            $status = UserStatus::ACTIVE;

            $result = match ($status) {
                UserStatus::ACTIVE => 'active_status',
                UserStatus::INACTIVE => 'inactive_status',
                UserStatus::SUSPENDED => 'suspended_status',
                UserStatus::PENDING => 'pending_status',
            };

            expect($result)->toBe('active_status');
        });
    });

    describe('values() method', function () {
        it('returns all enum values as array', function () {
            $values = UserStatus::values();

            expect($values)->toBeArray();
            expect($values)->toHaveCount(4);
            expect($values)->toContain('active');
            expect($values)->toContain('inactive');
            expect($values)->toContain('suspended');
            expect($values)->toContain('pending');
        });

        it('returns values in correct order', function () {
            $values = UserStatus::values();

            expect($values[0])->toBe('active');
            expect($values[1])->toBe('inactive');
            expect($values[2])->toBe('suspended');
            expect($values[3])->toBe('pending');
        });

        it('returns unique values', function () {
            $values = UserStatus::values();
            $uniqueValues = array_unique($values);

            expect($values)->toHaveCount(count($uniqueValues));
        });
    });

    describe('label() method', function () {
        it('returns correct labels for all cases', function () {
            expect(UserStatus::ACTIVE->label())->toBe('Active');
            expect(UserStatus::INACTIVE->label())->toBe('Inactive');
            expect(UserStatus::SUSPENDED->label())->toBe('Suspended');
            expect(UserStatus::PENDING->label())->toBe('Pending');
        });

        it('returns string type', function () {
            expect(UserStatus::ACTIVE->label())->toBeString();
            expect(UserStatus::INACTIVE->label())->toBeString();
            expect(UserStatus::SUSPENDED->label())->toBeString();
            expect(UserStatus::PENDING->label())->toBeString();
        });

        it('returns non-empty labels', function () {
            expect(UserStatus::ACTIVE->label())->not->toBeEmpty();
            expect(UserStatus::INACTIVE->label())->not->toBeEmpty();
            expect(UserStatus::SUSPENDED->label())->not->toBeEmpty();
            expect(UserStatus::PENDING->label())->not->toBeEmpty();
        });
    });

    describe('color() method', function () {
        it('returns correct colors for all cases', function () {
            expect(UserStatus::ACTIVE->color())->toBe('green');
            expect(UserStatus::INACTIVE->color())->toBe('gray');
            expect(UserStatus::SUSPENDED->color())->toBe('red');
            expect(UserStatus::PENDING->color())->toBe('yellow');
        });

        it('returns string type', function () {
            expect(UserStatus::ACTIVE->color())->toBeString();
            expect(UserStatus::INACTIVE->color())->toBeString();
            expect(UserStatus::SUSPENDED->color())->toBeString();
            expect(UserStatus::PENDING->color())->toBeString();
        });

        it('returns valid color names', function () {
            $validColors = ['green', 'gray', 'red', 'yellow'];

            expect(UserStatus::ACTIVE->color())->toBeIn($validColors);
            expect(UserStatus::INACTIVE->color())->toBeIn($validColors);
            expect(UserStatus::SUSPENDED->color())->toBeIn($validColors);
            expect(UserStatus::PENDING->color())->toBeIn($validColors);
        });
    });

    describe('Integration Tests', function () {
        it('can be used in array operations', function () {
            $statuses = [
                UserStatus::ACTIVE,
                UserStatus::INACTIVE,
                UserStatus::SUSPENDED,
                UserStatus::PENDING,
            ];

            expect($statuses)->toHaveCount(4);
            expect($statuses[0])->toBe(UserStatus::ACTIVE);
            expect($statuses[1])->toBe(UserStatus::INACTIVE);
        });

        it('can be used in collection operations', function () {
            $statuses = collect(UserStatus::cases());

            expect($statuses)->toHaveCount(4);
            expect($statuses->first())->toBe(UserStatus::ACTIVE);
            expect($statuses->last())->toBe(UserStatus::PENDING);
        });

        it('can be serialized to JSON', function () {
            $status = UserStatus::ACTIVE;

            expect(json_encode($status))->toBe('"active"');
        });

        it('can be used in database operations', function () {
            // Test that the enum can be used in database queries
            $activeStatus = UserStatus::ACTIVE;

            expect($activeStatus->value)->toBe('active');
            expect($activeStatus->label())->toBe('Active');
            expect($activeStatus->color())->toBe('green');
        });

        it('maintains consistency across all methods', function () {
            foreach (UserStatus::cases() as $status) {
                // Each status should have a value, label, and color
                expect($status->value)->toBeString();
                expect($status->label())->toBeString();
                expect($status->color())->toBeString();

                // Values should be lowercase
                expect($status->value)->toBe(mb_strtolower($status->value));

                // Labels should be properly capitalized
                expect($status->label())->toBe(ucfirst(mb_strtolower($status->label())));
            }
        });
    });

    describe('Edge Cases', function () {
        it('handles all cases in match statements', function () {
            foreach (UserStatus::cases() as $status) {
                $label = match ($status) {
                    UserStatus::ACTIVE => 'Active',
                    UserStatus::INACTIVE => 'Inactive',
                    UserStatus::SUSPENDED => 'Suspended',
                    UserStatus::PENDING => 'Pending',
                };

                expect($label)->toBe($status->label());
            }
        });

        it('values method returns same result multiple times', function () {
            $values1 = UserStatus::values();
            $values2 = UserStatus::values();

            expect($values1)->toBe($values2);
        });

        it('can be used in type declarations', function () {
            $function = function (UserStatus $status): string {
                return $status->label();
            };

            expect($function(UserStatus::ACTIVE))->toBe('Active');
            expect($function(UserStatus::INACTIVE))->toBe('Inactive');
        });
    });
});
