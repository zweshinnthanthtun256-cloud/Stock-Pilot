<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;

class BarcodeController extends Controller
{
    public function __invoke(Product $product)
    {
        $value = $product->barcode ?: $product->sku;
        $bits = '101';
        foreach (str_split(strtoupper($value)) as $char) {
            $code = str_pad(base_convert((string) ord($char), 10, 2), 8, '0', STR_PAD_LEFT);
            $bits .= $code.'0';
        }$bits .= '101';
        $bars = '';
        foreach (str_split($bits) as $i => $bit) {
            if ($bit === '1') {
                $bars .= '<rect x="'.(10 + $i * 2).'" y="8" width="2" height="54"/>';
            }
        }$width = 20 + strlen($bits) * 2;
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="'.$width.'" height="86" viewBox="0 0 '.$width.' 86"><rect width="100%" height="100%" fill="white"/><g fill="black">'.$bars.'</g><text x="50%" y="78" text-anchor="middle" font-family="monospace" font-size="11">'.htmlspecialchars($value, ENT_XML1).'</text></svg>';

        return response($svg, 200, ['Content-Type' => 'image/svg+xml', 'Content-Disposition' => 'inline; filename="barcode-'.$product->sku.'.svg"']);
    }
}
