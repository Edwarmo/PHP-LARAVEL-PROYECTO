<?php

declare(strict_types=1);

namespace Tests\Unit\Requests;

use App\Http\Requests\Admin\UpdateSpaceRequest;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class UpdateSpaceRequestTest extends TestCase
{
    private UpdateSpaceRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        $this->request = new UpdateSpaceRequest();
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
    public function messages_contain_spanish_translations(): void
    {
        $messages = $this->request->messages();
        $this->assertStringContainsString('obligatorio', $messages['name.required']);
        $this->assertStringContainsString('obligatorio', $messages['type.required']);
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
