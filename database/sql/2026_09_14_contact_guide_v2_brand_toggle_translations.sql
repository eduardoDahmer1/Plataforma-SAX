-- Textos do controle de expansão das marcas em /guia-de-atendimento-2.
-- Idempotente: cria as chaves ausentes e atualiza as existentes.
-- Após executar, limpe o cache do Laravel:
-- php artisan cache:forget all_translations_db

SET NAMES utf8mb4;
START TRANSACTION;

INSERT INTO `languages` (`key`, `pt`, `en`, `es`, `created_at`, `updated_at`) VALUES
('contact_guide_v2_see_more', 'Ver mais (:count)', 'Show more (:count)', 'Ver más (:count)', NOW(), NOW()),
('contact_guide_v2_see_less', 'Ver menos', 'Show less', 'Ver menos', NOW(), NOW())
ON DUPLICATE KEY UPDATE
    `pt` = VALUES(`pt`),
    `en` = VALUES(`en`),
    `es` = VALUES(`es`),
    `updated_at` = VALUES(`updated_at`);

COMMIT;

SELECT `key`, `pt`, `en`, `es`
FROM `languages`
WHERE `key` IN ('contact_guide_v2_see_more', 'contact_guide_v2_see_less')
ORDER BY `key`;
