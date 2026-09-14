<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class CafeBistro extends Model
{
    use HasFactory;

    protected $fillable = [
        // General
        'name',
        'slug',
        'is_active',
        'whatsapp',
        'meta_title',
        'meta_description',

        // Hero
        'hero_imagen',
        'hero_titulo',
        'hero_subtitulo',

        // Sobre Nós
        'sobre_imagen',
        'sobre_titulo',
        'sobre_texto',

        // Cardápio
        'cardapio_titulo',
        'cardapio_subtitulo',
        'cardapio_pdf',
        'cardapio_galeria',

        // Eventos
        'eventos_titulo',
        'eventos_subtitulo',
        'eventos_texto',
        'eventos_tipos',
        'eventos_galeria',

        // Horários
        'horarios',

        // Contacto
        'direccion',
        'telefono',
        'instagram_url',
        'facebook_url',
        'mapa_embed',
    ];

    protected $casts = [
        'is_active'       => 'boolean',
        'eventos_tipos'   => 'array',
        'eventos_galeria' => 'array',
        'cardapio_galeria' => 'array',
        'horarios'        => 'array',
    ];

    // Usa o WhatsApp configurado para reservas e mantém o telefone como fallback.
    public function getWhatsappLinkAttribute(): string
    {
        $number = $this->whatsapp ?: $this->telefono;

        return $number
            ? 'https://wa.me/' . preg_replace('/\D/', '', $number)
            : '#';
    }

    // Verifica si hay un embed de mapa cargado
    public function getHasMapaAttribute(): bool
    {
        return !empty($this->mapa_embed);
    }

    /**
     * Días de la semana en el orden natural de presentación.
     *
     * @var array<int, string>
     */
    public const DIAS = ['segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado', 'domingo'];

    /**
     * Normaliza los horarios al nuevo formato por día, aceptando tanto la
     * estructura nueva (array por día) como la antigua (textos agrupados).
     *
     * @return array<string, array{aberto: bool, inicio: ?string, fim: ?string}>
     */
    public function horariosNormalizados(): array
    {
        $horarios = $this->horarios ?? [];

        // Mapa del formato antiguo: clave agrupada => días individuales.
        $mapaAntiguo = [
            'segunda'      => ['segunda'],
            'terca_quinta' => ['terca', 'quarta', 'quinta'],
            'sexta_sabado' => ['sexta', 'sabado'],
            'domingo'      => ['domingo'],
        ];

        $normalizados = [];

        foreach (self::DIAS as $dia) {
            $valor = $horarios[$dia] ?? null;

            // Si el día no está en el formato nuevo, busca su clave antigua.
            if (! is_array($valor)) {
                foreach ($mapaAntiguo as $claveAntigua => $dias) {
                    if (in_array($dia, $dias, true) && isset($horarios[$claveAntigua])) {
                        $valor = $horarios[$claveAntigua];
                        break;
                    }
                }
            }

            $normalizados[$dia] = $this->normalizarDia($valor);
        }

        return $normalizados;
    }

    /**
     * Agrupa días consecutivos que comparten exactamente el mismo horario.
     *
     * @return array<int, array{inicio_dia: string, fim_dia: string, aberto: bool, inicio: ?string, fim: ?string}>
     */
    public function horariosAgrupados(): array
    {
        $normalizados = $this->horariosNormalizados();
        $grupos = [];

        foreach (self::DIAS as $dia) {
            $d = $normalizados[$dia];
            $chave = $d['aberto'] ? $d['inicio'].'|'.$d['fim'] : 'fechado';

            $ultimo = count($grupos) - 1;
            if ($ultimo >= 0 && $grupos[$ultimo]['chave'] === $chave) {
                $grupos[$ultimo]['fim_dia'] = $dia;
                continue;
            }

            $grupos[] = [
                'inicio_dia' => $dia,
                'fim_dia'    => $dia,
                'chave'      => $chave,
                'aberto'     => $d['aberto'],
                'inicio'     => $d['inicio'],
                'fim'        => $d['fim'],
            ];
        }

        return $grupos;
    }

    /**
     * Convierte un valor (nuevo array o texto antiguo) al formato normalizado.
     *
     * @return array{aberto: bool, inicio: ?string, fim: ?string}
     */
    private function normalizarDia($valor): array
    {
        if (is_array($valor)) {
            return [
                'aberto' => (bool) ($valor['aberto'] ?? false),
                'inicio' => $valor['inicio'] ?? null,
                'fim'    => $valor['fim'] ?? null,
            ];
        }

        $texto = is_string($valor) ? trim($valor) : '';

        if ($texto === '' || preg_match('/fechado/i', $texto)) {
            return ['aberto' => false, 'inicio' => null, 'fim' => null];
        }

        // Formato antiguo tipo "09:00 — 23:30" (acepta —, – y -).
        if (preg_match('/(\d{1,2}:\d{2})\s*[—–\-]\s*(\d{1,2}:\d{2})/u', $texto, $m)) {
            return [
                'aberto' => true,
                'inicio' => $m[1],
                'fim'    => $m[2],
            ];
        }

        return ['aberto' => false, 'inicio' => null, 'fim' => null];
    }

    public function translations(): MorphMany
    {
        return $this->morphMany(PageTranslation::class, 'pageable', 'page_type', 'page_id');
    }
}
