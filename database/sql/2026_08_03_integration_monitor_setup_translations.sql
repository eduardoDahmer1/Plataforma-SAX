-- Mensagens de diagnóstico da configuração do monitor de integração.
-- languages.key é UNIQUE; INSERT IGNORE não duplica nem sobrescreve chaves existentes.

INSERT IGNORE INTO `languages` (`key`, `pt`, `en`, `es`, `created_at`, `updated_at`) VALUES
('integration_token_missing', 'As tabelas do monitor existem, mas o token da API não está configurado neste ambiente. Defina INTEGRATION_MONITOR_TOKEN no .env e limpe o cache de configuração.', 'The monitoring tables exist, but the API token is not configured in this environment. Set INTEGRATION_MONITOR_TOKEN in .env and clear the configuration cache.', 'Las tablas del monitor existen, pero el token de la API no está configurado en este ambiente. Define INTEGRATION_MONITOR_TOKEN en el .env y limpia la caché de configuración.', NOW(), NOW()),
('integration_waiting_first_report', 'O monitor está pronto e aguarda o primeiro envio do integrador para este ambiente.', 'The monitor is ready and waiting for the integrator first report to this environment.', 'El monitor está listo y espera el primer envío del integrador a este ambiente.', NOW(), NOW());
