-- Horários individuais por dia da semana + "Fechado" para o SAX Café & Bistrô.
-- Idempotente: cria as chaves ausentes e atualiza as existentes.
-- Após executar, limpe o cache do Laravel:
-- php artisan cache:forget all_translations_db

SET NAMES utf8mb4;
START TRANSACTION;

INSERT INTO `languages` (`key`, `pt`, `en`, `es`, `created_at`, `updated_at`) VALUES
('cafe_tuesday', 'Terça-feira', 'Tuesday', 'Martes', NOW(), NOW()),
('cafe_wednesday', 'Quarta-feira', 'Wednesday', 'Miércoles', NOW(), NOW()),
('cafe_thursday', 'Quinta-feira', 'Thursday', 'Jueves', NOW(), NOW()),
('cafe_friday', 'Sexta-feira', 'Friday', 'Viernes', NOW(), NOW()),
('cafe_saturday', 'Sábado', 'Saturday', 'Sábado', NOW(), NOW()),
('cafe_sunday', 'Domingo', 'Sunday', 'Domingo', NOW(), NOW()),
('cafe_closed', 'Fechado', 'Closed', 'Cerrado', NOW(), NOW())
ON DUPLICATE KEY UPDATE
    `pt` = VALUES(`pt`),
    `en` = VALUES(`en`),
    `es` = VALUES(`es`),
    `updated_at` = VALUES(`updated_at`);

COMMIT;

SELECT `key`, `pt`, `en`, `es`
FROM `languages`
WHERE `key` IN ('cafe_tuesday', 'cafe_wednesday', 'cafe_thursday', 'cafe_friday', 'cafe_saturday', 'cafe_sunday', 'cafe_closed')
ORDER BY `key`;
