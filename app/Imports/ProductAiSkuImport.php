<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithFormatData;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductAiSkuImport implements ToArray, WithFormatData, WithHeadingRow
{
    public function array(array $array): void
    {
        // Excel::toArray returns the parsed rows; no side effects are needed here.
    }
}
