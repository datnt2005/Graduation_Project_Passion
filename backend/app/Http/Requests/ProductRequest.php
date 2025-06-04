<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'seller_id' => 'required|integer|exists:sellers,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',

            'categories' => 'required|array|min:1',
            'categories.*.category_id' => 'required|integer|exists:categories,id',

            'variants' => 'required|array|min:1',
            'variants.*.sku' => 'required|string',
            'variants.*.price' => 'required|numeric|min:0',
            'variants.*.cost_price' => 'nullable|numeric|min:0',
            'variants.*.sale_price' => 'nullable|numeric|min:0',
            'variants.*.thumbnail' => 'nullable|string',

            'variants.*.attributes' => 'nullable|array',
            'variants.*.attributes.*.attribute_id' => 'required|integer',
            'variants.*.attributes.*.value_id' => 'required|integer',

            'tags' => 'nullable|array',
            'tags.*.tag_id' => 'required|integer|exists:tags,id',

            'product_pic' => 'nullable|array',
            'product_pic.*' => 'nullable|string',
        ];
    }


    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $variants = $this->input('variants', []);

            foreach ($variants as $i => $variant) {
                $inventory = $variant['inventory'] ?? null;

                // Trường hợp inventory là 1 object
                if ($this->isAssoc($inventory)) {
                    if (!isset($inventory['quantity']) || !is_numeric($inventory['quantity'])) {
                        $validator->errors()->add("variants.$i.inventory.quantity", 'Số lượng là bắt buộc và phải là số.');
                    }
                    if (empty($inventory['location'])) {
                        $validator->errors()->add("variants.$i.inventory.location", 'Vị trí kho là bắt buộc.');
                    }
                }
                // Trường hợp inventory là mảng các kho
                elseif (is_array($inventory)) {
                    foreach ($inventory as $j => $inv) {
                        if (!isset($inv['quantity']) || !is_numeric($inv['quantity'])) {
                            $validator->errors()->add("variants.$i.inventory.$j.quantity", 'Số lượng là bắt buộc và phải là số.');
                        }
                        if (empty($inv['location'])) {
                            $validator->errors()->add("variants.$i.inventory.$j.location", 'Vị trí kho là bắt buộc.');
                        }
                    }
                }
                else {
                    $validator->errors()->add("variants.$i.inventory", 'Inventory phải là object hoặc array hợp lệ.');
                }
            }
        });
    }

    private function isAssoc(array $arr)
    {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}
