<?php

return [
    'enabled' => filter_var(env('CATALOG_PEER_SYNC_ENABLED', false), FILTER_VALIDATE_BOOL),
    'connection' => env('CATALOG_PEER_DB_CONNECTION', 'catalog_peer'),
    'peer_storage_path' => env('CATALOG_PEER_STORAGE_PATH'),
    'optical_category_ref_code' => (string) env('CATALOG_OPTICAL_CATEGORY_REF_CODE', '13'),
    'optical_category_slugs' => array_values(array_filter(array_map(
        static fn (string $slug): string => strtolower(trim($slug)),
        explode(',', env('CATALOG_OPTICAL_CATEGORY_SLUGS', 'optico,otica'))
    ))),
    'optical_database_names' => array_values(array_filter(array_map(
        static fn (string $database): string => trim($database),
        explode(',', env('CATALOG_OPTICAL_DATABASE_NAMES', 'plataforma_otica'))
    ))),
];
