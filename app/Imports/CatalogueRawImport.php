<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class CatalogueRawImport implements ToArray
{
    public function array(array $array): void
    {
        // Utilisé uniquement comme transport pour Excel::toArray().
    }
}
