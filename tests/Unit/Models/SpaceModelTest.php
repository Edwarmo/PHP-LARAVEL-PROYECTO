<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Space;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class SpaceModelTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function slug_auto_generated_on_create(): void
    {
        $space = Space::create([
            'name'           => 'Mi Sala Especial',
            'type'           => 'reunión',
            'capacity'       => 10,
            'price_per_hour' => 10000,
            'is_active'      => true,
        ]);
        $this->assertStringContainsString('mi-sala-especial', $space->slug);
    }

    #[Test]
    public function slug_generated_from_name_without_manual_slug(): void
    {
        $space = Space::create([
            'name'           => 'Sala Automática',
            'type'           => 'reunión',
            'capacity'       => 10,
            'price_per_hour' => 10000,
            'is_active'      => true,
        ]);
        $this->assertEquals('sala-automatica', $space->slug);
    }

    #[Test]
    public function manual_slug_preserved_when_provided(): void
    {
        $space = Space::create([
            'name'           => 'Sala Manual',
            'slug'           => 'custom-slug',
            'type'           => 'reunión',
            'capacity'       => 10,
            'price_per_hour' => 10000,
            'is_active'      => true,
        ]);
        $this->assertEquals('custom-slug', $space->slug);
    }
}
