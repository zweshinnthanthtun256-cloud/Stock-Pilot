<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class CatalogRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->hasPermission('master.manage') ?? false; }
    public function rules(): array
    {
        $id=$this->route('id');
        return match($this->route('resource')) {
            'products'=>['sku'=>['required','max:80',Rule::unique('products')->ignore($id)],'product_code'=>['required','max:80',Rule::unique('products')->ignore($id)],'barcode'=>['nullable','max:120',Rule::unique('products')->ignore($id)],'name'=>'required|string|max:180','description'=>'nullable|string','category_id'=>'required|exists:categories,id','brand_id'=>'nullable|exists:brands,id','unit_id'=>'required|exists:units,id','purchase_cost'=>'required|decimal:0,2|min:0','selling_price'=>'required|decimal:0,2|min:0','minimum_stock'=>'nullable|numeric|min:0','reorder_quantity'=>'nullable|numeric|min:0','is_active'=>'boolean','is_discontinued'=>'boolean'],
            'categories'=>['name'=>'required|string|max:120','slug'=>['required','alpha_dash',Rule::unique('categories')->ignore($id)],'parent_id'=>['nullable','exists:categories,id',Rule::notIn([$id])],'is_active'=>'boolean'],
            'brands'=>['name'=>['required','max:120',Rule::unique('brands')->ignore($id)],'is_active'=>'boolean'],
            'units'=>['name'=>['required','max:80',Rule::unique('units')->ignore($id)],'symbol'=>'required|max:20','precision'=>'integer|min:0|max:3','is_active'=>'boolean'],
            'suppliers'=>['code'=>['required','max:80',Rule::unique('suppliers')->ignore($id)],'company_name'=>'required|string|max:180','contact_person'=>'nullable|string|max:120','phone'=>'nullable|string|max:40','email'=>'nullable|email|max:180','address'=>'nullable|string','payment_terms'=>'nullable|string|max:120','notes'=>'nullable|string','is_active'=>'boolean'],
            'warehouses'=>['code'=>['required','max:80',Rule::unique('warehouses')->ignore($id)],'name'=>'required|string|max:180','address'=>'nullable|string','manager_id'=>'nullable|exists:users,id','phone'=>'nullable|string|max:40','capacity'=>'nullable|numeric|min:0','status'=>'required|in:active,inactive','notes'=>'nullable|string'],
            default=>[],
        };
    }
}
