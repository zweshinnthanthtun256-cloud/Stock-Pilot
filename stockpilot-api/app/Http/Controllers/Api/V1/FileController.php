<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function productImage(Request $r, Product $product)
    {
        $r->validate(['image' => 'required|image|mimes:jpg,jpeg,png,webp|max:4096']);
        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }$path = $r->file('image')->store('products', 'public');
        $product->update(['image_path' => $path]);

        return response()->json(['message' => 'Product image uploaded.', 'data' => ['path' => $path, 'url' => Storage::disk('public')->url($path)]]);
    }

    public function document(Request $r)
    {
        $d = $r->validate(['file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx|max:10240', 'folder' => 'required|in:purchase-invoices,delivery-receipts,supplier-documents']);
        $path = $r->file('file')->store($d['folder']);

        return response()->json(['message' => 'Document uploaded.', 'data' => ['path' => $path]], 201);
    }
}
