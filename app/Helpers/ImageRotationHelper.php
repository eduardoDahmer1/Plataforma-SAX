<?php

if (!function_exists('sax_rotate_images')) {
    /**
     * Embaralha um array de imagens de forma determinística por período de tempo.
     * A mesma "semente" (período) sempre gera a mesma ordem, então todos os
     * visitantes veem a mesma sequência — mas ela muda sozinha a cada $days dias,
     * sem precisar de cron job, admin ou JS: a rotação nasce do timestamp atual.
     */
    function sax_rotate_images(?array $images, int $days = 2): array
    {
        if (empty($images)) {
            return [];
        }

        $period = intdiv(now()->timestamp, $days * 86400);

        $shuffled = $images;
        mt_srand($period);
        shuffle($shuffled);
        mt_srand();

        return $shuffled;
    }
}
