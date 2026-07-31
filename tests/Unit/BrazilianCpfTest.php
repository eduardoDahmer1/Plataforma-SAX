<?php

namespace Tests\Unit;

use App\Rules\BrazilianCpf;
use PHPUnit\Framework\TestCase;

class BrazilianCpfTest extends TestCase
{
    public function test_accepts_a_valid_formatted_cpf(): void
    {
        $failed = false;

        (new BrazilianCpf())->validate('document', '529.982.247-25', function () use (&$failed) {
            $failed = true;
        });

        $this->assertFalse($failed);
    }

    /**
     * @dataProvider invalidCpfProvider
     */
    public function test_rejects_invalid_cpf(string $cpf): void
    {
        $failed = false;

        (new BrazilianCpf())->validate('document', $cpf, function () use (&$failed) {
            $failed = true;
        });

        $this->assertTrue($failed);
    }

    public static function invalidCpfProvider(): array
    {
        return [
            ['111.111.111-11'],
            ['529.982.247-24'],
            ['123'],
            [''],
        ];
    }
}
