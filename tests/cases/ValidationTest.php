<?php

namespace Nudelsalat\Tests;

use Nudelsalat\Migrations\Fields\EmailField;
use Nudelsalat\Migrations\Fields\StringField;
use Nudelsalat\ORM\Model;

class ValidationTest extends TestCase
{
    public function testValidateEnforcesMaxLengthOnStringField(): void
    {
        $errors = ValidationUser::validate([
            'login' => str_repeat('x', 200),
        ], true);

        $this->assertTrue(isset($errors['login']), 'Expected login to fail validation due to max_length.');
        $this->assertContains('at most 80', $errors['login']);
    }

    public function testValidateEmailFieldChecksFormat(): void
    {
        $errors = ValidationEmailUser::validate([
            'email' => 'not-an-email',
        ], true);

        $this->assertTrue(isset($errors['email']), 'Expected email to fail validation due to invalid format.');
        $this->assertSame('Enter a valid email address.', $errors['email']);
    }

    public function testValidateUsesDefaultOnlyWhenValueOmitted(): void
    {
        $omitted = ValidationDefaults::validate([], true);
        $this->assertSame([], $omitted);

        $explicitNull = ValidationDefaults::validate(['status' => null], true);
        $this->assertTrue(isset($explicitNull['status']));
        $this->assertSame("Field 'status' cannot be null", $explicitNull['status']);
    }

    public function testValidateEnforcesChoices(): void
    {
        $invalid = ValidationChoices::validate(['status' => 'c'], true);
        $this->assertTrue(isset($invalid['status']));
        $this->assertSame('Select a valid choice. That choice is not one of the available choices.', $invalid['status']);

        $valid = ValidationChoices::validate(['status' => 'a'], true);
        $this->assertSame([], $valid);
    }
}

class ValidationUser extends Model
{
    public static function fields(): array
    {
        return [
            'login' => new StringField(length: 80),
        ];
    }
}

class ValidationEmailUser extends Model
{
    public static function fields(): array
    {
        return [
            'email' => new EmailField(),
        ];
    }
}

class ValidationDefaults extends Model
{
    public static function fields(): array
    {
        return [
            'status' => new StringField(length: 20, default: 'active'),
        ];
    }
}

class ValidationChoices extends Model
{
    public static function fields(): array
    {
        return [
            'status' => new StringField(length: 20, options: [
                'choices' => [
                    'a' => 'A',
                    'b' => 'B',
                ],
            ]),
        ];
    }
}

