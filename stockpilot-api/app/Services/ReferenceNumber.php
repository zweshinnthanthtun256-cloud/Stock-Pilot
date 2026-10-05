<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ReferenceNumber
{
    public static function next(string $table, string $prefix): string
    {
        $year = now()->year;
        $last = DB::table($table)->where('number', 'like', "{$prefix}-{$year}-%")->lockForUpdate()->orderByDesc('id')->value('number');
        $sequence = $last ? ((int) substr($last, -6)) + 1 : 1;

        return sprintf('%s-%s-%06d', $prefix, $year, $sequence);
    }
}
