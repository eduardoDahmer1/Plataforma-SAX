# Design QA — Guia de Atendimento 2 banner

- Source visual truth: `public/images/contact-guide/sax-banner-1600x300.png`
- Desktop implementation: `/tmp/sax-guide-browser.LiPMUC/hero-1280.png`
- Mobile implementation: `/tmp/sax-guide-browser.LiPMUC/hero-390.png`
- Combined comparison: `/tmp/sax-guide-browser.LiPMUC/banner-comparison.png`
- Viewports: 1280 × 900 and 390 × 900 CSS px, device scale factor 1
- Source dimensions: 1600 × 300 px
- Desktop hero capture: 1248 × 234 px, normalized to 1600 × 300 in the combined comparison
- Mobile hero capture: 358 × 200 px; intentional `cover` crop with the building aligned right
- State: SAX Department Store, Ciudad del Este; default guide state at Piso 2

## Findings

No actionable P0, P1, or P2 differences remain.

- Fonts and typography: the existing ecommerce font is retained. The title and subtitle are real selectable HTML, with sufficient weight and contrast. Mobile wraps the title to two lines without clipping.
- Spacing and layout rhythm: desktop preserves the 1600:300 ratio (1248 × 234 in the rendered container). Mobile uses 200 px at 390 px and 180 px at 320 px. The breadcrumb and controls remain outside the banner.
- Colors and visual tokens: the original dark photographic palette is preserved. No gold, promotional buttons, heavy shadow, or global token changes were introduced.
- Image quality and asset fidelity: the building keeps its proportions on desktop. Mobile uses a responsive crop rather than scaling the full banner into a narrow strip; the facade remains recognizable at the right.
- Copy and content: Portuguese reads “Guia de Atendimento” and “Explore nossos pisos, setores e marcas”; Spanish and English equivalents use the project database-backed `messages.*` translation pattern.
- Focused comparison: the desktop source/implementation composite confirms identical frame ratio, crop, and facade placement; the mobile capture confirms legibility and intentional crop. A separate focused region was not needed beyond these hero-only captures.

## Comparison history

- Initial mobile browser check found the content expanding the hero to 219.42 px at 390 px (P2 responsive height drift).
- Fix: set the mobile hero to `clamp(180px, 52vw, 200px)`, reduced mobile copy padding, and restored automatic proportional height from 768 px upward.
- Post-fix evidence: `/tmp/sax-guide-browser.LiPMUC/hero-390.png` measures 358 × 200 px; the 320 px viewport measures 180 px. The title is fully visible and there is no horizontal overflow.

## Interaction verification

- Banner request completed without failed asset requests.
- Four real locations switch correctly; only SAX Department Store in Ciudad del Este shows the facade, while the other locations show the neutral header.
- Search, location selector, floor anchor navigation, sticky index, automatic active-floor highlighting, last-floor handling, mobile floor selector, and reduced-motion behavior passed.
- Tested at 320, 390, 768, and 1280 px with no page JavaScript errors or horizontal overflow.
- The original `/guia-de-atendimento` still returned successfully and retained its original guide markup.

final result: passed
# Design QA — Avaliações de produtos

- Escopo: cards do catálogo, página do produto e administração de avaliações.
- Referência: capturas fornecidas pelo usuário, adaptadas ao sistema visual neutro do SAX.
- Rotas verificadas: `produto/{product}/avaliacoes`, `avaliacoes/{review}` e `admin/products/ratings`.
- Validação estrutural: Blade compilado com sucesso; rotas registradas; CSS responsivo para desktop, tablet e mobile.
- Validação funcional: criação, média, contagem e ocultação verificadas em transação real no banco do stage, revertida ao final.
- Testes automatizados: 8 testes, 54 asserções, todos aprovados.
- Verificação visual em navegador: bloqueada neste ambiente porque não há navegador local, Playwright, Puppeteer nem Laravel Dusk disponível.

Final result: blocked (apenas a comparação visual em navegador; implementação e verificações de código concluídas).

## Filtros contextuais — 30/09/2026

- Referência: busca avançada existente e capturas de categoria/filtro fornecidas pelo usuário.
- Implementado: mesmo filtro em busca, categoria, subcategoria, categoria-filha e marca; filtros persistentes; atualização AJAX; preço com faixa dupla; cupom; cores existentes; tamanhos agrupados semanticamente.
- Regra verificada: busca e categoria de Perfumes exibem somente o grupo Volume (`9 ml` a `250 ml` nos dados atuais), sem tamanhos de roupa ou numeração.
- Removido: disponibilidade por loja e respectivos controles/chips.
- Renderização interna: categoria, subcategoria, categoria-filha e marca retornaram HTTP 200 com filtro e runtime em tempo real.
- Verificação visual em navegador: bloqueada porque este ambiente não dispõe de cloud browser, navegador local, Playwright, Puppeteer ou Dusk.

Final result: blocked (somente comparação visual em navegador; implementação, dados e regressões automatizadas validados).

## Institucional SAX — 02/10/2026

- Source visual truth: captura de referência anexada pelo usuário (1918 × 998 px; Chrome desktop).
- Implementation route: `/institucional`.
- Implementation screenshot path: indisponível; este ambiente não possui cloud browser, Chrome/Chromium, Firefox, Playwright, Puppeteer ou Dusk.
- Intended viewport: desktop 1440 × 900 CSS px e mobile 390 × 844 CSS px, device scale factor 1.
- State: página pública, primeiro slide, idioma ativo do stage.
- Render evidence: resposta HTTP 200, HTML final com 126.231 bytes, assets reais do storage e todas as seções renderizadas.
- Full-view comparison evidence: bloqueada pela ausência de navegador renderizador.
- Focused region comparison evidence: bloqueada pelo mesmo motivo.

### Findings

- P0/P1 funcionais: nenhum encontrado na renderização HTTP, compilação Blade, rotas ou testes focados.
- P2 visual: não classificável sem uma captura real do navegador no mesmo viewport da referência.
- Fonts and typography: Playfair Display + Montserrat seguem a linguagem editorial da referência; confirmação pixel a pixel bloqueada.
- Spacing and layout rhythm: definidos breakpoints para desktop, tablet e mobile; inspeção renderizada bloqueada.
- Colors and visual tokens: paleta preto, marfim e dourado centralizada em tokens CSS; contraste visual final não aferido em navegador.
- Image quality and asset fidelity: reutilizados banners, galeria, capa e logos reais; o runtime evita repetição imediata entre cenários.
- Copy and content: hero, sobre, experiências, números, galeria, história, vídeos e CTA presentes; os novos textos de seção são editáveis em PT/ES/EN.

### Interaction verification

- Slider com autoplay configurável, navegação e paginação implementados.
- Imagens de cenário são sorteadas por visita sem repetição enquanto houver opções.
- Contadores usam IntersectionObserver e respeitam redução de movimento.
- Galeria, links para experiências, Guia de Setores, vídeos e âncoras estão conectados.
- JavaScript passou em `node --check`; Blade compilou; 8 testes focados passaram com 61 asserções.

### Comparison history

- Primeira verificação estrutural: página retornou HTTP 200 e todas as áreas principais estavam presentes no HTML.
- Não houve iteração visual porque nenhum navegador renderizador está instalado no ambiente.

final result: blocked

# Design QA — Conteúdos do admin

- Source visual truth: direção visual 3 aprovada pelo usuário, registrada em `/home/remoto/.codex/generated_images/01a16415-684b-7143-955b-ee5a9cba8d0b/exec-fe8d8846-06b6-45e3-b063-e47c8241fa7c.png`.
- Source pixels: `1487 x 1058`.
- Intended CSS viewport: desktop `1440 x 1024` e mobile `390 x 844`.
- Implementation screenshot: indisponível neste ambiente.
- State: páginas administrativas autenticadas de Blog, Contato, Guia de atendimento, Trabalhe conosco e Políticas.

## Full-view comparison evidence

Bloqueada. Não há navegador autenticado conectado ao stage nesta sessão, portanto não foi possível capturar as rotas administrativas no mesmo viewport da referência.

## Focused-region comparison evidence

Bloqueada pelo mesmo motivo. As regiões prioritárias para a captura são cabeçalhos, filtros, cards de resumo, listas, editores, estados semânticos, formulários e barras de ação.

## Findings

- P0/P1 funcionais: nenhum encontrado na compilação das views ou no teste de acesso do admin.
- O sistema visual aprovado foi aplicado a todas as páginas e subpáginas do grupo Conteúdos, preservando campos, ações, textos e rotas existentes.
- Cores semânticas: preto para ações principais, verde para ativo/sucesso, amarelo para atenção/rascunho, vermelho para perigo/erro e cinza claro para superfícies neutras.
- Espaçamentos foram normalizados em passos de 8/10/12/14/16/18 px, com cards mais compactos, alinhamento consistente e empilhamento responsivo.
- O menu Conteúdos agora permanece aberto e marca Blog corretamente nas categorias; Trabalhe conosco também mantém o grupo aberto.
- O detalhe do pedido recebeu ritmo vertical uniforme de 16 px entre Produtos, Logística, Atualizações e cards laterais.

## Automated verification

- `git diff --check`: passou.
- CSS: chaves balanceadas em `admin-content.css` e `admin-commerce.css`.
- Blade: `php artisan view:clear && php artisan view:cache` passou.
- Permissões: `AdminRoleAccessTest` passou com 4 testes e 45 asserções.

## Implementation checklist

- Capturar as rotas autenticadas de Conteúdos no desktop e no mobile.
- Conferir quebra dos botões e filtros com dados reais, especialmente Contato e Guia de atendimento.
- Conferir editor de Blog/Políticas, estados de erro e listas vazias.
- Ajustar somente diferenças visuais observadas nas capturas e repetir a comparação.

final result: blocked

# Design QA — Pedidos e carrinhos abandonados

- Source visual truth: `/home/remoto/.codex/generated_images/01a16415-684b-7143-955b-ee5a9cba8d0b/exec-fe8d8846-06b6-45e3-b063-e47c8241fa7c.png`
- Source pixels: `1487 x 1058`
- Intended CSS viewport: `1440 x 1024`, desktop admin
- Implementation screenshot: unavailable
- Implementation pixels / density: unavailable
- State: order detail, pending order and pending payment

## Full-view comparison evidence

Blocked. The selected source visual was inspected, but this session has no authenticated browser surface connected to the stage admin. The Blade implementation cannot be captured in the same authenticated route and viewport yet.

## Focused-region comparison evidence

Blocked for the same reason. The intended focused regions are the order summary strip, operator action rail, semantic status colors, product table, and the corresponding abandoned-cart states.

## Findings

- No code-level P0/P1 issue was found. Blade compilation passes and all existing actions, forms, links, data fields, and responsive content remain present.
- Visual fidelity, responsive wrapping, focus states, and browser console behavior still require an authenticated browser capture.
- Typography uses the project's existing Inter stack; semantic tokens map to black, light gray, green, yellow, and red from the selected direction.
- Product imagery continues using the existing stored product assets; Font Awesome remains the established icon source.
- Existing application copy was preserved, with short helper labels added only where they improve scanning.

## Comparison history

- Initial implementation: four admin pages received the selected visual system.
- Automated validation: CSS braces balanced; Git whitespace check passed; all Blade views compiled successfully.
- Focused PHP tests: admin role-access tests passed. Order-note feature tests were blocked before application execution by an existing SQLite migration limitation involving multiple column renames.
- No browser-rendered comparison iteration was possible in this session.

## Implementation checklist

- Capture `/admin/orders`, `/admin/orders/{id}`, `/admin/abandoned-carts`, and `/admin/abandoned-carts/{id}` from the authenticated stage environment.
- Compare the order detail at a `1440 x 1024` viewport against the selected source visual.
- Verify status and payment forms, filters, detail links, destructive confirmation, hover/focus states, mobile stacking, and browser console.
- Fix any P0/P1/P2 visual differences and repeat the comparison.

## Follow-up polish

- Tune only minor density or wrapping differences discovered in the authenticated capture.

final result: blocked
