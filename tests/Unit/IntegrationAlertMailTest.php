<?php

namespace Tests\Unit;

use App\Mail\IntegrationAlertMail;
use PHPUnit\Framework\TestCase;

class IntegrationAlertMailTest extends TestCase
{
    public function test_it_builds_an_alert_email_with_the_dashboard_link(): void
    {
        $mail = new IntegrationAlertMail(
            type: 'integration_failed',
            title: 'Falha na integração de produtos',
            alertMessage: 'O integrador retornou um erro.',
            actionUrl: '/admin#integration-monitor',
            details: [
                'source' => 'catalog',
                'status' => 'failed',
                'error_code' => 'catalog_timeout',
            ],
        );

        $this->assertSame('emails.integration_alert', $mail->content()->view);
        $this->assertSame('integration_failed', $mail->type);
        $this->assertSame('Falha na integração de produtos', $mail->title);
        $this->assertSame('/admin#integration-monitor', $mail->actionUrl);
    }
}
