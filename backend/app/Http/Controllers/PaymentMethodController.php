<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $paymentMethods = PaymentMethod::all();
        return response()->json(['data' => $paymentMethods]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|string|in:active,inactive'
        ], [
            'name.required' => 'Tên phương thức thanh toán là bắt buộc',
            'name.string' => 'Tên phương thức thanh toán phải là chuỗi',
            'name.max' => 'Tên phương thức thanh toán không được vượt quá 255 ký tự',
            'status.required' => 'Trạng thái là bắt buộc',
            'status.string' => 'Trạng thái phải là chuỗi',
            'status.in' => 'Trạng thái không hợp lệ'
        ]);

        $paymentMethod = PaymentMethod::create($request->all());
        return response()->json(['data' => $paymentMethod], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $paymentMethod = PaymentMethod::findOrFail($id);
        return response()->json(['data' => $paymentMethod]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'string|max:255',
            'status' => 'string|in:active,inactive'
        ], [
            'name.string' => 'Tên phương thức thanh toán phải là chuỗi',
            'name.max' => 'Tên phương thức thanh toán không được vượt quá 255 ký tự',
            'status.string' => 'Trạng thái phải là chuỗi',
            'status.in' => 'Trạng thái không hợp lệ'
        ]);

        $paymentMethod = PaymentMethod::findOrFail($id);
        $paymentMethod->update($request->all());
        return response()->json(['data' => $paymentMethod]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $paymentMethod = PaymentMethod::findOrFail($id);
        $paymentMethod->delete();
        return response()->json(null, 204);
    }
}
