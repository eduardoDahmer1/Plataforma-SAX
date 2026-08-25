# DHL Express MyDHL API — stage/sandbox

## Estado da integração

A fundação REST, o empacotamento e a cotação do checkout estão implementados. O checkout internacional só libera a compra quando a DHL está ativa, responde com uma tarifa válida e todos os itens podem ser embalados. Enquanto o catálogo não possui medidas reais, perfis logísticos conservadores são identificados como estimativas no checkout.

## Escopo comercial confirmado

- Origem: CEPAL S.A. / SAX Department Store, Av. San Blás esq. Regimiento Sauce, Centro, Ciudad del Este, Alto Paraná, 100134, Paraguai (`PY`).
- Contato operacional inicial: E-commerce SAX, `ecommerce@saxdepartment.com`, `+595 984 167575`.
- Moeda declarada: dólar americano (`USD`).
- Impostos e taxas no destino: responsabilidade do destinatário (`receiver`), com configuração inicial `DAP`.
- DHL: todos os países, exceto Brasil e Paraguai.
- Brasil: continua no fluxo manual atual.
- Paraguai: continua no frete nacional atual.

## Segurança

As credenciais nunca devem ser salvas no repositório nem em logs. O painel master `/admin/dhl` pode armazená-las no banco usando o cast criptografado do Laravel; os valores nunca voltam ao navegador. Se uma chave ou segredo for exposto em conversa, ticket ou captura de tela, regenere-o antes de usar.

## Configuração

Copie apenas as variáveis `DHL_*` do `.env.example` para o ambiente. O sandbox oficial usa:

```text
https://express.api.dhl.com/mydhlapi/test
```

A autenticação é HTTP Basic com `DHL_API_KEY` como usuário e `DHL_API_SECRET` como senha. `DHL_ACCOUNT_NUMBER` é a conta DHL Express usada para tarifa/faturamento; não é a chave da API.

Depois de alterar o `.env`, limpe o cache de configuração:

```bash
php artisan config:clear
```

## Diagnóstico

Inspeção local, sem chamada externa:

```bash
php artisan dhl:diagnose
```

Cotação controlada no sandbox (consome uma chamada da cota diária):

```bash
php artisan dhl:diagnose --live
```

O comando nunca imprime chave ou segredo e mascara o número da conta.

## Embalagens iniciais

- Pequena / DHL Box 2: `33 × 18 × 10 cm`, tara `0,150 kg`, limite operacional inicial `1,2 kg` bruto. O peso volumétrico é `1,188 kg`, criando a base de tarifa semelhante para mercadorias entre 300 g e 1 kg que caibam na caixa.
- Média / DHL Box 4: `33 × 32 × 18 cm`, tara `0,320 kg`, peso bruto recomendado `5 kg`.
- Grande / DHL Box 5: `33 × 32 × 34 cm`, tara `0,770 kg`, peso bruto recomendado `10 kg`.

Todas são editáveis no painel. O algoritmo tenta a menor embalagem compatível, pode promover um volume para uma caixa maior para evitar caixas extras e abre volumes adicionais quando necessário. Usa ocupação segura inicial de 70% e peso faturável `max(peso real, C × L × A / 5000)`.

## Limites atuais

- Perfumes, álcool, bebidas, aerossóis, baterias e itens marcados como perigosos não recebem cotação automática até validação operacional.
- Frete é retornado pela MyDHL API e recebe o acréscimo editável do painel (inicialmente 5%).
- Impostos de importação não são estimados nem adicionados ao pedido; DAP/receiver informa que serão cobrados no destino quando aplicáveis.
- A promoção de frete grátis existe, mas começa desligada. Pode exigir mínimo de itens, subtotal e peso faturável máximo.
- Antes da produção, a expedição deve medir fisicamente as três caixas e os produtos prioritários, substituindo estimativas por dados reais.
