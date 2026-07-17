<?php

namespace Tests\Unit;

use App\Exceptions\ValidationException;
use App\Validation\CatValidator;
use Tests\Unit\DataProviders\CatValidator\CatValidatorDataProvider;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use \PHPUnit\Framework\TestCase;

class CatValidatorTest extends TestCase
{
    private CatValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new CatValidator();
    }

    public function test_create_accepts_valid_data(): void
    {
        $result = $this->validator->validateForCreate([
            'name' => 'Kitten',
            'lang' => 'meow'
        ]);
        $this->assertSame([
            'name' => 'Kitten',
            'lang' => 'meow'
        ], $result);
    }
    public function test_create_trims_values(): void
    {
        $result = $this->validator->validateForCreate([
            'name' => '  Kitten  ',
            'lang' => '  meow  ',
        ]);

        $this->assertSame([
            'name' => 'Kitten',
            'lang' => 'meow',
        ], $result);
    }
    public function test_create_returns_only_allowed_fields(): void
    {
        $result = $this->validator->validateForCreate([
            'name' => 'Kitten',
            'lang' => 'meow',
            'smth' => 'gav',
            'true' => true,
        ]);
        $this->assertSame([
            'name' => 'Kitten',
            'lang' => 'meow',
        ], $result);
        $this->assertArrayNotHasKey('smth', $result);
        $this->assertArrayNotHasKey('true', $result);
    }
    public function test_create_requires_name_and_lang(): void
    {
        try {
            $this->validator->validateForCreate([]);

            $this->fail();
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('name', $exception->errors());
            $this->assertArrayHasKey('lang', $exception->errors());
        }
    }

    #[DataProviderExternal(CatValidatorDataProvider::class, 'emptyValues')]
    public function test_create_rejects_empty_name(string $name): void {
        try {
            $this->validator->validateForCreate([
                'name' => $name,
                'lang' => 'meow',
            ]);

            $this->fail();
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('name', $exception->errors());
        }
    }

    #[DataProviderExternal(CatValidatorDataProvider::class, 'emptyValues')]
    public function test_create_rejects_empty_lang(string $lang): void {
        try {
            $this->validator->validateForCreate([
                'name' => 'Kitten',
                'lang' => $lang,
            ]);

            $this->fail();
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('lang', $exception->errors());
        }
    }
    #[DataProviderExternal(CatValidatorDataProvider::class, 'invalidTypes')]
    public function test_create_rejects_non_string_name(mixed $name): void {
        try {
            $this->validator->validateForCreate([
                'name' => $name,
                'lang' => 'meow',
            ]);

            $this->fail();
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('name', $exception->errors());
        }
    }

    public function test_update_accepts_name_only(): void
    {
        $result = $this->validator->validateForUpdate([
            'name' => ' New name ',
        ]);

        $this->assertSame(['name' => 'New name',], $result);

        $this->assertArrayNotHasKey('lang', $result);
    }

    public function test_update_accepts_lang_only(): void
    {
        $result = $this->validator->validateForUpdate([
            'lang' => ' purr ',
        ]);

        $this->assertSame(['lang' => 'purr',], $result);
        $this->assertArrayNotHasKey('name', $result);
    }

    public function test_update_requires_at_least_one_editable_field(): void
    {
        try {
            $this->validator->validateForUpdate([]);

            $this->fail();
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey(
                'data',
                $exception->errors()
            );
        }
    }
}