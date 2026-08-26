# Regra de ambientes

Este diretório é o ambiente **sax_stage** e é o único ambiente autorizado para alterações durante desenvolvimento e testes.

- Não editar `/var/www/html/sax_plataforma` nem `/var/www/html/sax_otica` neste fluxo.
- O código é compartilhado pelo mesmo repositório Git; depois de validar no stage, a publicação nos demais ambientes é feita pelo operador com `git pull`.
- Migrações e alterações de banco devem ser executadas primeiro no banco do stage.
- Antes de qualquer alteração, confirme o diretório atual com `pwd` e preserve mudanças não relacionadas.
