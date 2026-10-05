<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CatalogRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $r, string $resource)
    {
        $model = $this->model($resource);
        $q = $model::query();
        if ($s = $r->string('search')->trim()->value()) {
            $q->where(fn ($x) => $x->where('name', 'like', "%{$s}%")->when($resource === 'suppliers', fn ($y) => $y->orWhere('company_name', 'like', "%{$s}%")));
        }

return response()->json($q->orderBy($r->input('sort', 'id'), $r->input('direction', 'desc'))->paginate(min($r->integer('per_page', 15), 100)));
    }

    public function store(CatalogRequest $r, string $resource)
    {
        $model = $this->model($resource);

        return response()->json(['message' => 'Created.', 'data' => $model::create($r->validated())], 201);
    }

    public function show(string $resource, int $id)
    {
        $model = $this->model($resource);

        return response()->json(['data' => $model::findOrFail($id)]);
    }

    public function update(CatalogRequest $r, string $resource, int $id)
    {
        $model = $this->model($resource);
        $item = $model::findOrFail($id);
        $item->update($r->validated());

        return response()->json(['message' => 'Updated.', 'data' => $item]);
    }

    private function model(string $r): string
    {
        return match ($r) {
            'products' => Product::class,'categories' => Category::class,'brands' => Brand::class,'units' => Unit::class,'suppliers' => Supplier::class,'warehouses' => Warehouse::class,default => abort(404)
        };
    }
}
