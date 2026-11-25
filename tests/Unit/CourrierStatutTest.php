<?php

namespace App\Tests\Unit;

use App\Enum\CourrierStatut;
use PHPUnit\Framework\TestCase;

class CourrierStatutTest extends TestCase
{
    public function testLabelsExist(): void
    {
        foreach (CourrierStatut::cases() as $statut) {
            $this->assertNotSame('', $statut->label());
            $this->assertNotSame('', $statut->badgeClass());
        }
    }

    public function testArchiveValue(): void
    {
        $this->assertSame('archive', CourrierStatut::Archive->value);
    }
}
