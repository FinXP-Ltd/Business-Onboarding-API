<?php

namespace App\Traits\Support;

trait SortHeaders
{
    public static function sortHeaders(string $column): string
    {
        if (isset(self::$sortedColumns[$column])) {
            return self::$sortedColumns[$column];
        }

        return self::$sortedColumns['id'];
    }
}
