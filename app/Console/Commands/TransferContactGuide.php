<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use JsonException;
use Throwable;

class TransferContactGuide extends Command
{
    protected $signature = 'contact-guide:transfer
        {action : export ou import}
        {file : Caminho do arquivo JSON}
        {--force : Confirma a substituição dos dados na importação}';

    protected $description = 'Exporta ou importa exclusivamente as unidades e setores do guia de atendimento';

    private const TABLES = [
        'contact_guide_locations',
        'contact_guide_entries',
    ];

    public function handle(): int
    {
        if (! $this->tablesAreAvailable()) {
            $this->components->error('As tabelas do guia de atendimento ainda não existem. Execute as migrations primeiro.');

            return self::FAILURE;
        }

        return match (strtolower((string) $this->argument('action'))) {
            'export' => $this->export(),
            'import' => $this->import(),
            default => $this->invalidAction(),
        };
    }

    private function export(): int
    {
        $file = (string) $this->argument('file');
        $directory = dirname($file);

        if (! is_dir($directory) || ! is_writable($directory)) {
            $this->components->error("Diretório inválido ou sem permissão de escrita: {$directory}");

            return self::FAILURE;
        }

        $payload = [
            'format' => 'sax-contact-guide',
            'version' => 1,
            'exported_at' => now()->toIso8601String(),
            'source_database' => DB::getDatabaseName(),
            'locations' => DB::table('contact_guide_locations')->orderBy('id')->get()->map(fn ($row) => (array) $row)->all(),
            'entries' => DB::table('contact_guide_entries')->orderBy('id')->get()->map(fn ($row) => (array) $row)->all(),
        ];

        try {
            $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            $this->components->error('Não foi possível gerar o arquivo do guia: '.$exception->getMessage());

            return self::FAILURE;
        }

        if (file_put_contents($file, $json, LOCK_EX) === false) {
            $this->components->error("Não foi possível gravar o arquivo: {$file}");

            return self::FAILURE;
        }

        $this->components->info(sprintf(
            'Guia exportado: %d unidade(s) e %d setor(es).',
            count($payload['locations']),
            count($payload['entries'])
        ));

        return self::SUCCESS;
    }

    private function import(): int
    {
        if (! $this->option('force')) {
            $this->components->error('A importação substitui somente os dados do guia. Use --force para confirmar.');

            return self::FAILURE;
        }

        $file = (string) $this->argument('file');
        if (! is_file($file) || ! is_readable($file)) {
            $this->components->error("Arquivo inválido ou sem permissão de leitura: {$file}");

            return self::FAILURE;
        }

        try {
            $payload = json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            $this->components->error('Arquivo JSON inválido: '.$exception->getMessage());

            return self::FAILURE;
        }

        if (! $this->validPayload($payload)) {
            $this->components->error('O arquivo não é uma exportação válida do guia de atendimento.');

            return self::FAILURE;
        }

        try {
            DB::transaction(function () use ($payload): void {
                DB::table('contact_guide_entries')->delete();
                DB::table('contact_guide_locations')->delete();

                foreach (array_chunk($payload['locations'], 200) as $locations) {
                    DB::table('contact_guide_locations')->insert($locations);
                }
                foreach (array_chunk($payload['entries'], 200) as $entries) {
                    DB::table('contact_guide_entries')->insert($entries);
                }
            });
        } catch (Throwable $exception) {
            $this->components->error('A importação foi revertida sem alterar o guia: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->components->info(sprintf(
            'Guia sincronizado: %d unidade(s) e %d setor(es). Nenhuma outra tabela foi alterada.',
            count($payload['locations']),
            count($payload['entries'])
        ));

        return self::SUCCESS;
    }

    private function validPayload(mixed $payload): bool
    {
        if (! is_array($payload)
            || ($payload['format'] ?? null) !== 'sax-contact-guide'
            || ($payload['version'] ?? null) !== 1
            || ! is_array($payload['locations'] ?? null)
            || ! is_array($payload['entries'] ?? null)) {
            return false;
        }

        $locationIds = collect($payload['locations'])->pluck('id')->filter()->map(fn ($id) => (int) $id)->all();

        return collect($payload['locations'])->every(fn ($row) => is_array($row) && isset($row['id'], $row['city'], $row['name']))
            && collect($payload['entries'])->every(fn ($row) => is_array($row)
                && isset($row['id'], $row['location_id'], $row['floor'], $row['sector'])
                && in_array((int) $row['location_id'], $locationIds, true));
    }

    private function tablesAreAvailable(): bool
    {
        return collect(self::TABLES)->every(fn (string $table): bool => Schema::hasTable($table));
    }

    private function invalidAction(): int
    {
        $this->components->error('Ação inválida. Use export ou import.');

        return self::INVALID;
    }
}
