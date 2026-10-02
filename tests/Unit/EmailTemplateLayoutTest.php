<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class EmailTemplateLayoutTest extends TestCase
{
    public function test_shared_email_layout_is_compact_light_and_responsive(): void
    {
        $layout = file_get_contents(__DIR__.'/../../resources/views/layout/email.blade.php');

        $this->assertStringContainsString('content="light only"', $layout);
        $this->assertStringContainsString('max-width:620px', $layout);
        $this->assertStringContainsString('class="sax-email-brand"', $layout);
        $this->assertStringContainsString('background:#303236', $layout);
        $this->assertStringContainsString('@media only screen and (max-width:640px)', $layout);
        $this->assertStringNotContainsString('email_header_logo.png', $layout);
        $this->assertStringNotContainsString('linear-gradient', $layout);
    }

    public function test_all_transactional_email_templates_use_the_shared_layout(): void
    {
        $templates = [
            'abandoned_cart_help',
            'integration_alert',
            'marketing_campaign',
            'order_paid',
            'order_status',
            'password_changed',
            'reset_password',
            'resume_forward',
            'welcome',
        ];

        foreach ($templates as $template) {
            $view = file_get_contents(__DIR__."/../../resources/views/emails/{$template}.blade.php");
            $this->assertStringContainsString("@extends('layout.email')", $view, $template);
        }
    }

    public function test_temporary_purchase_notice_is_a_compact_corner_indicator(): void
    {
        $notice = file_get_contents(__DIR__.'/../../resources/views/components/catalog-integration-notice.blade.php');

        $this->assertStringContainsString('position:fixed', $notice);
        $this->assertStringContainsString('min-height:24px', $notice);
        $this->assertStringContainsString('Loja em atualização', $notice);
        $this->assertStringNotContainsString('<p>Carrinho e checkout continuam disponíveis', $notice);
    }
}

