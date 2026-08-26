# Regra de ambientes e integrador

O ambiente **sax_stage** continua sendo o único ambiente das lojas autorizado para alterações durante desenvolvimento e testes. O projeto separado **INTEGRADOR-SAX-V2** também está autorizado quando a tarefa envolver a integração do catálogo.

- Não editar `/var/www/html/sax_plataforma` nem `/var/www/html/sax_otica` neste fluxo.
- É permitido editar `/var/www/html/INTEGRADOR-SAX-V2` para desenvolver e testar o integrador.
- O código é compartilhado pelo mesmo repositório Git; depois de validar no stage, a publicação nos demais ambientes é feita pelo operador com `git pull`.
- Migrações e alterações de banco devem ser executadas primeiro no banco do stage.
- Antes de qualquer alteração, confirme o diretório atual com `pwd` e preserve mudanças não relacionadas.
