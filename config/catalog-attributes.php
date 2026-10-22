<?php

return [
    'enabled' => filter_var(env('CATALOG_ATTRIBUTES_ENABLED', true), FILTER_VALIDATE_BOOL),
];
