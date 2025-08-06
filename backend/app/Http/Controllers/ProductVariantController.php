<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductVariantController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Tìm seller tương ứng với user hiện tại
        $sellerId = DB::table('sellers')
            ->where('user_id', $user->id)
            ->value('id');

        if ($user->role === 'seller' && !$sellerId) {
            return response()->json([], 200);
        }

        // Lấy danh sách biến thể sản phẩm
        $query = DB::table('product_variants')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->select(
                'product_variants.id',
                'product_variants.sku',
                'product_variants.thumbnail as image_url',
                'products.name as product_name'
            );

        if ($user->role === 'seller') {
            $query->where('products.seller_id', $sellerId);
        }
        $variants = DB::table('product_variants')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->select(
                'product_variants.id',
                'product_variants.sku',
                'product_variants.thumbnail as image_url',
                'products.name as product_name'
            )
            ->when($user->role === 'seller', function ($q) use ($sellerId) {
                return $q->where('products.seller_id', $sellerId);
            })
            ->orderBy('products.name')
            ->get();

        $variantIds = $variants->pluck('id');

        $attributesMap = DB::table('variant_attributes')
            ->join('attribute_values', 'attribute_values.id', '=', 'variant_attributes.value_id')
            ->join('attributes', 'attributes.id', '=', 'attribute_values.attribute_id')
            ->whereIn('variant_attributes.product_variant_id', $variantIds)
            ->select(
                'variant_attributes.product_variant_id',
                'attributes.name as attribute_name',
                'attribute_values.value as attribute_value'
            )
            ->get()
            ->groupBy('product_variant_id');

        // Gắn thuộc tính vào từng biến thể
    foreach ($variants as $variant) {
    $variant->attributes = collect($attributesMap[$variant->id] ?? [])->map(fn($a) => [
        'name' => $a->attribute_name,
        'value' => $a->attribute_value
    ])->values();

    // 👇 Tạo tên hiển thị gồm tên sản phẩm + biến thể
    if ($variant->attributes->isNotEmpty()) {
        $attributesStr = $variant->attributes->map(fn($attr) => "{$attr['name']}: {$attr['value']}")->implode(', ');
        $variant->display_name = "{$variant->product_name} ({$attributesStr})";
    } else {
        $variant->display_name = $variant->product_name;
    }
}


        return response()->json($variants);

    }


}
