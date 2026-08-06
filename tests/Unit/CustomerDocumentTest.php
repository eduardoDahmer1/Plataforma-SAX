<?php

namespace Tests\Unit;

use App\Support\CustomerDocument;
use PHPUnit\Framework\TestCase;

class CustomerDocumentTest extends TestCase
{
    public function test_formats_each_supported_document_type(): void
    {
        $this->assertSame('433.111.608-51', CustomerDocument::format('43311160851', CustomerDocument::CPF));
        $this->assertSame('12.345.678-9', CustomerDocument::format('123456789', CustomerDocument::RG_BR));
        $this->assertSame('84.800.007', CustomerDocument::format('84800007', CustomerDocument::CI_PY));
        $this->assertSame('80121232-4', CustomerDocument::format('801212324', CustomerDocument::RUC_PY));
        $this->assertSame('PA-123456', CustomerDocument::format('pa-123456', CustomerDocument::FOREIGN));
    }

    public function test_validates_cpf_check_digits(): void
    {
        $this->assertTrue(CustomerDocument::isValid('433.111.608-51', CustomerDocument::CPF));
        $this->assertFalse(CustomerDocument::isValid('101.982.556-67', CustomerDocument::CPF));
        $this->assertFalse(CustomerDocument::isValid('111.111.111-11', CustomerDocument::CPF));
    }

    public function test_validates_paraguayan_ruc_with_official_modulo_eleven(): void
    {
        $this->assertTrue(CustomerDocument::isValid('80121232-4', CustomerDocument::RUC_PY));
        $this->assertFalse(CustomerDocument::isValid('80121232-5', CustomerDocument::RUC_PY));
        $this->assertSame(4, CustomerDocument::rucCheckDigit('80121232'));
    }

    public function test_structurally_validates_rg_and_ci(): void
    {
        $this->assertTrue(CustomerDocument::isValid('12.345.678-9', CustomerDocument::RG_BR));
        $this->assertTrue(CustomerDocument::isValid('84.800.007', CustomerDocument::CI_PY));
        $this->assertFalse(CustomerDocument::isValid('11111', CustomerDocument::CI_PY));
    }

    public function test_infers_legacy_documents_without_breaking_existing_users(): void
    {
        $this->assertSame(CustomerDocument::CPF, CustomerDocument::inferType(null, '43311160851', '55'));
        $this->assertSame(CustomerDocument::RG_BR, CustomerDocument::inferType(null, '123456789', '55'));
        $this->assertSame(CustomerDocument::CI_PY, CustomerDocument::inferType(null, '84800007', '595'));
    }

    public function test_accepts_international_passport_or_document(): void
    {
        $this->assertTrue(CustomerDocument::isValid('PA-123456', CustomerDocument::FOREIGN));
        $this->assertFalse(CustomerDocument::isValid('123', CustomerDocument::FOREIGN));
    }
}
