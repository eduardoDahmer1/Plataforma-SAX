<?php

namespace App\Console\Commands;

use App\Exceptions\DhlApiException;
use App\Services\Dhl\DhlRateService;
use App\Services\Dhl\MyDhlApiClient;
use Illuminate\Console\Command;
use InvalidArgumentException;

class DiagnoseDhl extends Command
{
    protected $signature = 'dhl:diagnose
        {--live : Executa uma cotação de leitura no sandbox}
        {--destination-country=US : País ISO-2 usado apenas no diagnóstico live}
        {--destination-postal-code=10001 : Código postal usado apenas no diagnóstico live}
        {--destination-city=New York : Cidade usada apenas no diagnóstico live}';

    protected $description = 'Verifica a configuração da MyDHL API e, opcionalmente, executa uma cotação sandbox';

    public function handle(MyDhlApiClient $client, DhlRateService $rates): int
    {
        $summary = $client->configurationSummary();

        $this->components->twoColumnDetail('Ambiente', (string) $summary['environment']);
        $this->components->twoColumnDetail('Base URL', (string) $summary['base_url']);
        $this->components->twoColumnDetail('Conta', (string) ($summary['account'] ?: 'não configurada'));
        $this->components->twoColumnDetail('Credenciais', $summary['credentials_present'] ? 'presentes' : 'ausentes');

        if (! $client->isConfigured()) {
            $this->components->error('Configuração incompleta: '.implode(', ', $summary['missing']));

            return self::FAILURE;
        }

        if (! $this->option('live')) {
            $this->components->info('Configuração carregada. Use --live somente quando quiser consumir uma chamada do sandbox.');

            return self::SUCCESS;
        }

        if ((string) $summary['environment'] !== 'sandbox') {
            $this->components->error('O diagnóstico live é permitido somente com DHL_ENVIRONMENT=sandbox.');

            return self::FAILURE;
        }

        try {
            $response = $rates->quote([
                'country_code' => (string) $this->option('destination-country'),
                'postal_code' => (string) $this->option('destination-postal-code'),
                'city_name' => (string) $this->option('destination-city'),
            ], [[
                'weight' => 1,
                'length' => 10,
                'width' => 10,
                'height' => 10,
            ]], 100);

            $products = (array) ($response['products'] ?? []);
            $this->components->info('A DHL respondeu com sucesso. Produtos retornados: '.count($products).'.');

            foreach (array_slice($products, 0, 10) as $product) {
                $this->line(sprintf(
                    '%s — %s',
                    (string) ($product['productCode'] ?? '?'),
                    (string) ($product['productName'] ?? 'produto sem nome')
                ));
            }

            return self::SUCCESS;
        } catch (DhlApiException $exception) {
            $this->components->error($exception->getMessage());
            if ($exception->httpStatus()) {
                $this->line('HTTP: '.$exception->httpStatus());
            }
            if ($exception->requestId()) {
                $this->line('Request ID: '.$exception->requestId());
            }

            return self::FAILURE;
        } catch (InvalidArgumentException $exception) {
            $this->components->error($exception->getMessage());

            return self::INVALID;
        }
    }
}
