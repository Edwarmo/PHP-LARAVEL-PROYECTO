<?php

declare(strict_types=1);

namespace Tests\Unit\Requests;

use App\Http\Requests\Admin\StoreSpaceRequest;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class StoreSpaceRequestTest extends TestCase
{
    private StoreSpaceRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        $this->request = new StoreSpaceRequest();
    }

    #[Test]
    public function authorize_returns_true(): void
    {
        $this->assertTrue($this->request->authorize());
    }

    #[Test]
    public function rules_contain_required_fields(): void
    {
        $rules = $this->request->rules();
        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('type', $rules);
        $this->assertArrayHasKey('capacity', $rules);
        $this->assertArrayHasKey('price_per_hour', $rules);
        $this->assertArrayHasKey('is_active', $rules);
    }

    #[Test]
    public function rules_have_correct_types(): void
    {
        $rules = $this->request->rules();
        $this->assertContains('required', $rules['name']);
        $this->assertContains('required', $rules['type']);
        $this->assertContains('required', $rules['capacity']);
        $this->assertContains('required', $rules['price_per_hour']);
    }

    #[Test]
    public function messages_contain_spanish_translations(): void
    {
        $messages = $this->request->messages();
        $this->assertStringContainsString('obligatorio', $messages['name.required']);
        $this->assertStringContainsString('obligatorio', $messages['type.required']);
        $this->assertStringContainsString('obligatoria', $messages['capacity.required']);
        $this->assertStringContainsString('obligatorio', $messages['price_per_hour.required']);
    }

    #[Test]
    public function attributes_contain_spanish_labels(): void
    {
        $attributes = $this->request->attributes();
        $this->assertEquals('nombre', $attributes['name']);
        $this->assertEquals('tipo', $attributes['type']);
        $this->assertEquals('capacidad', $attributes['capacity']);
    }
}
