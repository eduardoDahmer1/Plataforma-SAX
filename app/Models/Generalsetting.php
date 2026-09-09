<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Generalsetting extends Model
{
    use HasFactory;

    protected $table = 'generalsettings';

    public const HOME_SECTIONS = [
        'main_slider' => [
            'label' => 'Slider principal',
            'description' => 'Campanhas e banners principais no topo da Home.',
            'icon' => 'fa-images',
            'enabled' => true,
        ],
        'categories' => [
            'label' => 'Categorias',
            'description' => 'Atalhos visuais para as categorias da loja.',
            'icon' => 'fa-table-cells-large',
            'enabled' => true,
        ],
        'exclusive_collection' => [
            'label' => 'Coleção exclusiva',
            'description' => 'Bloco editorial com curadoria, categorias e imagem.',
            'icon' => 'fa-gem',
            'enabled' => true,
        ],
        'recent_products' => [
            'label' => 'Recentemente atualizados',
            'description' => 'Carrossel com os produtos atualizados recentemente.',
            'icon' => 'fa-clock-rotate-left',
            'enabled' => true,
        ],
        'editorial_banners' => [
            'label' => 'Destaques da casa',
            'description' => 'Carrossel dos banners editoriais da Home.',
            'icon' => 'fa-panorama',
            'enabled' => true,
        ],
        'most_viewed' => [
            'label' => 'Produtos mais vistos',
            'description' => 'Carrossel baseado nos produtos mais acessados.',
            'icon' => 'fa-eye',
            'enabled' => true,
        ],
        'featured_products' => [
            'label' => 'Produtos destacados',
            'description' => 'Produtos marcados como destaque no catálogo.',
            'icon' => 'fa-star',
            'enabled' => true,
        ],
        'brands' => [
            'label' => 'Marcas recomendadas',
            'description' => 'Vitrine visual das marcas disponíveis na loja.',
            'icon' => 'fa-copyright',
            'enabled' => true,
        ],
        'help' => [
            'label' => 'Ajuda e informações',
            'description' => 'Cartões de compra, perguntas frequentes e atendimento.',
            'icon' => 'fa-circle-question',
            'enabled' => true,
        ],
        'newsletter' => [
            'label' => 'Formulário de newsletter',
            'description' => 'Formulário para cadastro e recebimento de novidades.',
            'icon' => 'fa-envelope',
            'enabled' => true,
        ],
    ];

    public const HOME_SECTION_CONTENT = [
        'main_slider' => [
            'pt' => ['title' => 'Campanhas em destaque', 'description' => 'Descubra a curadoria e as novidades selecionadas pela SAX.'],
            'en' => ['title' => 'Featured campaigns', 'description' => 'Discover the curation and latest selections from SAX.'],
            'es' => ['title' => 'Campañas destacadas', 'description' => 'Descubre la curaduría y las novedades seleccionadas por SAX.'],
        ],
        'categories' => [
            'pt' => ['title' => 'Categorias', 'description' => 'Explore o universo SAX por categoria.'],
            'en' => ['title' => 'Categories', 'description' => 'Explore the SAX universe by category.'],
            'es' => ['title' => 'Categorías', 'description' => 'Explora el universo SAX por categoría.'],
        ],
        'exclusive_collection' => [
            'pt' => ['title' => 'Coleção exclusiva', 'description' => 'Uma seleção pensada para destacar design, acabamentos e marcas que definem o universo SAX com mais profundidade do que um banner sozinho consegue mostrar.'],
            'en' => ['title' => 'Exclusive collection', 'description' => 'A selection designed to showcase the design, finishes and brands that define the SAX universe in greater depth.'],
            'es' => ['title' => 'Colección exclusiva', 'description' => 'Una selección pensada para destacar el diseño, los acabados y las marcas que definen el universo SAX con mayor profundidad.'],
        ],
        'recent_products' => [
            'pt' => ['title' => 'Recentemente atualizados', 'description' => 'Confira os produtos que acabaram de receber novidades em nosso catálogo.'],
            'en' => ['title' => 'Recently updated', 'description' => 'See the products that have just been updated in our catalog.'],
            'es' => ['title' => 'Actualizados recientemente', 'description' => 'Descubre los productos que acaban de actualizarse en nuestro catálogo.'],
        ],
        'editorial_banners' => [
            'pt' => ['title' => 'Destaques da casa', 'description' => 'Histórias, campanhas e seleções especiais da curadoria SAX.'],
            'en' => ['title' => 'SAX highlights', 'description' => 'Stories, campaigns and special selections curated by SAX.'],
            'es' => ['title' => 'Destacados de SAX', 'description' => 'Historias, campañas y selecciones especiales de la curaduría SAX.'],
        ],
        'most_viewed' => [
            'pt' => ['title' => 'Mais vistos', 'description' => 'Os produtos que estão despertando mais interesse agora.'],
            'en' => ['title' => 'Most viewed', 'description' => 'The products attracting the most interest right now.'],
            'es' => ['title' => 'Más vistos', 'description' => 'Los productos que están despertando más interés ahora.'],
        ],
        'featured_products' => [
            'pt' => ['title' => 'Produtos destacados', 'description' => 'Uma seleção especial de produtos escolhidos pela SAX.'],
            'en' => ['title' => 'Featured products', 'description' => 'A special selection of products chosen by SAX.'],
            'es' => ['title' => 'Productos destacados', 'description' => 'Una selección especial de productos elegidos por SAX.'],
        ],
        'brands' => [
            'pt' => ['title' => 'Suas marcas recomendadas', 'description' => 'Conheça marcas selecionadas para você.'],
            'en' => ['title' => 'Recommended brands', 'description' => 'Discover brands selected for you.'],
            'es' => ['title' => 'Marcas recomendadas', 'description' => 'Descubre marcas seleccionadas para ti.'],
        ],
        'help' => [
            'pt' => ['title' => 'Como podemos ajudar?', 'description' => 'Encontre informações para comprar e falar com a nossa equipe.'],
            'en' => ['title' => 'How can we help?', 'description' => 'Find information about shopping and contacting our team.'],
            'es' => ['title' => '¿Cómo podemos ayudarte?', 'description' => 'Encuentra información para comprar y hablar con nuestro equipo.'],
        ],
        'newsletter' => [
            'pt' => ['title' => 'Não perca nenhuma novidade', 'description' => 'Registre-se para receber promoções, novidades personalizadas, atualizações de estoque e muito mais diretamente no seu e-mail.'],
            'en' => ['title' => 'Never miss an update', 'description' => 'Sign up to receive promotions, personalized news, stock updates and more directly by email.'],
            'es' => ['title' => 'No te pierdas ninguna novedad', 'description' => 'Regístrate para recibir promociones, novedades personalizadas, actualizaciones de stock y mucho más por correo electrónico.'],
        ],
    ];

    // Adicionada a coluna 'show_highlight_famosos' para controlar os Mais Vistos
    protected $fillable = [
        'site_name',
        'home_sections',
        'show_highlight_destaque',
        'show_highlight_lancamentos',
        'show_highlight_famosos', // <--- Nova aqui
        'show_highlight_ofertas_relampago',  
    ];

    // Garantindo que o Laravel trate o valor como booleano (0 ou 1)
    protected $casts = [
        'home_sections' => 'array',
        'show_highlight_destaque' => 'boolean',
        'show_highlight_lancamentos' => 'boolean',
        'show_highlight_famosos' => 'boolean', // <--- Nova aqui
        'show_highlight_ofertas_relampago'=> 'boolean',
    ];

    public static function defaultHomeSections(): array
    {
        $sections = [];

        foreach (self::HOME_SECTIONS as $key => $section) {
            $sections[$key] = [
                ...$section,
                'content' => self::HOME_SECTION_CONTENT[$key],
                'position' => count($sections) + 1,
            ];
        }

        return $sections;
    }

    public function resolvedHomeSections(): array
    {
        $stored = is_array($this->home_sections) ? $this->home_sections : [];
        $sections = [];

        foreach (self::defaultHomeSections() as $key => $default) {
            $saved = is_array($stored[$key] ?? null) ? $stored[$key] : [];
            $savedContent = is_array($saved['content'] ?? null) ? $saved['content'] : [];
            $sections[$key] = [
                ...$default,
                'enabled' => array_key_exists('enabled', $saved)
                    ? (bool) $saved['enabled']
                    : $this->legacyHomeSectionVisibility($key, $default['enabled']),
                'position' => max(1, (int) ($saved['position'] ?? $default['position'])),
                'content' => $this->resolveSectionContent($default['content'], $savedContent),
            ];
        }

        $defaultOrder = array_flip(array_keys(self::HOME_SECTIONS));
        uksort($sections, fn (string $leftKey, string $rightKey) =>
            [$sections[$leftKey]['position'], $defaultOrder[$leftKey]]
            <=>
            [$sections[$rightKey]['position'], $defaultOrder[$rightKey]]
        );

        return $sections;
    }

    public static function contentForLocale(array $section, ?string $locale = null): array
    {
        $locale ??= app()->getLocale();
        $language = str_starts_with($locale, 'en') ? 'en' : (str_starts_with($locale, 'es') ? 'es' : 'pt');
        $content = is_array($section['content'] ?? null) ? $section['content'] : [];
        $fallback = is_array($content['pt'] ?? null) ? $content['pt'] : ['title' => '', 'description' => ''];

        return [
            'title' => (string) ($content[$language]['title'] ?? $fallback['title'] ?? ''),
            'description' => (string) ($content[$language]['description'] ?? $fallback['description'] ?? ''),
        ];
    }

    private function resolveSectionContent(array $defaults, array $saved): array
    {
        $content = [];

        foreach (['pt', 'en', 'es'] as $language) {
            $storedLanguage = is_array($saved[$language] ?? null) ? $saved[$language] : [];
            $content[$language] = [
                'title' => (string) ($storedLanguage['title'] ?? $defaults[$language]['title']),
                'description' => (string) ($storedLanguage['description'] ?? $defaults[$language]['description']),
            ];
        }

        return $content;
    }

    private function legacyHomeSectionVisibility(string $key, bool $default): bool
    {
        $legacyColumns = [
            'recent_products' => 'show_highlight_lancamentos',
            'most_viewed' => 'show_highlight_famosos',
            'featured_products' => 'show_highlight_destaque',
        ];

        return isset($legacyColumns[$key])
            ? (bool) $this->getAttribute($legacyColumns[$key])
            : $default;
    }
}
