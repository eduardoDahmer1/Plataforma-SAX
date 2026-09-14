<?php

namespace Tests\Unit;

use App\Models\CafeBistro;
use PHPUnit\Framework\TestCase;

class CafeBistroWhatsappLinkTest extends TestCase
{
    public function test_it_uses_the_reservation_whatsapp_before_the_public_phone(): void
    {
        $cafe = new CafeBistro([
            'whatsapp' => '+595 981 111222',
            'telefono' => '+595 993 333444',
        ]);

        $this->assertSame('https://wa.me/595981111222', $cafe->whatsapp_link);
    }

    public function test_it_falls_back_to_the_public_phone_when_whatsapp_is_empty(): void
    {
        $cafe = new CafeBistro([
            'telefono' => '+595 993 333444',
        ]);

        $this->assertSame('https://wa.me/595993333444', $cafe->whatsapp_link);
    }
}
