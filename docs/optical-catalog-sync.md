# Catálogo compartilhado entre SAX e Ótica

## Comportamento

- O `INTEGRADOR-SAX-V2` grava o catálogo completo em `plataforma`.
- Depois de uma rodada bem-sucedida, ele espelha em `plataforma_otica` somente
  produtos da categoria ERP `13` (`optico`).
- O mesmo heartbeat é enviado para stage, produção e Ótica.
- Uma edição administrativa de produto óptico sincroniza a família completa
  (cores e tamanhos), traduções, vínculos e mídias com a outra loja.
- Produtos não ópticos nunca são enviados pelo espelho do integrador.

## Ordem de publicação

1. Publicar este código primeiro no stage e validar.
2. Publicar o mesmo commit em `sax_plataforma` e `sax_otica`.
3. Executar `php artisan migrate --force` nas duas lojas antes de limpar a
   Ótica. A migração torna `order_items.product_id` nullable e troca a exclusão
   em cascata por `SET NULL`, preservando pedidos antigos.
4. Configurar o peer nas duas aplicações e executar `php artisan config:clear`.
5. Recompilar o integrador com `go build -o integrator .`.
6. Rodar uma integração manual com `OTICA_PRUNE_NON_OPTICAL=false` e conferir
   os três monitores.
7. Na aplicação Ótica, visualizar a limpeza com
   `php artisan catalog:prune-non-optical`. Se os números estiverem corretos,
   executar `php artisan catalog:prune-non-optical --force`.
8. Após a limpeza inicial, definir `OTICA_PRUNE_NON_OPTICAL=true` no integrador
   para impedir que registros fora do catálogo óptico permaneçam no banco.

## `.env` da plataforma

```dotenv
CATALOG_PEER_SYNC_ENABLED=true
CATALOG_PEER_DB_HOST=127.0.0.1
CATALOG_PEER_DB_PORT=3306
CATALOG_PEER_DB_DATABASE=plataforma_otica
CATALOG_PEER_DB_USERNAME=plataforma
CATALOG_PEER_DB_PASSWORD=
CATALOG_PEER_STORAGE_PATH=/var/www/html/sax_otica/storage/app/public
```

Senha vazia reutiliza `DB_PASSWORD` da própria aplicação.

## `.env` da Ótica

```dotenv
CATALOG_PEER_SYNC_ENABLED=true
CATALOG_PEER_DB_HOST=127.0.0.1
CATALOG_PEER_DB_PORT=3306
CATALOG_PEER_DB_DATABASE=plataforma
CATALOG_PEER_DB_USERNAME=plataforma
CATALOG_PEER_DB_PASSWORD=
CATALOG_PEER_STORAGE_PATH=/var/www/html/sax_plataforma/storage/app/public
```

## `.env` do integrador

```dotenv
OTICA_DB_ENABLED=true
OTICA_DB_HOST=127.0.0.1
OTICA_DB_PORT=3306
OTICA_DB_DATABASE=plataforma_otica
OTICA_DB_USERNAME=plataforma
OTICA_DB_PASSWORD=
OTICA_CATEGORY_REF_CODE=13
OTICA_PRUNE_NON_OPTICAL=false

INTEGRATION_MONITOR_OTICA_URL=https://otica.saxdepartment.com/api/integrations/catalog/heartbeat
# Opcional quando produção e Ótica usam o mesmo token:
INTEGRATION_MONITOR_OTICA_TOKEN=
```

O sincronizador é desativado por padrão e não conecta ao peer no stage sem o
opt-in explícito. O comando de limpeza também recusa qualquer banco cujo nome
não esteja em `CATALOG_OPTICAL_DATABASE_NAMES`.
