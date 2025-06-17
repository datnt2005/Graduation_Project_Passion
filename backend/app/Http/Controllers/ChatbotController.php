<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    public function chat(Request $request)
    {
        try {
$res = Http::withHeaders([
    'Authorization' => 'Bearer ' . env('OPENROUTER_API_KEY'),
    'HTTP-Referer' => 'http://localhost',
    'Content-Type' => 'application/json',
])->post('https://openrouter.ai/api/v1/chat/completions', [
    'model' => 'mistralai/mistral-7b-instruct:free',
    'messages' => [
        ['role' => 'system', 'content' => 'Bạn là trợ lý chatbot hỗ trợ khách hàng.'],
        ['role' => 'user', 'content' => $request->input('message')],
    ]
]);
Log::info('OpenRouter response:', $res->json());

    return response()->json($res->json());

} catch (\Exception $e) {
    return response()->json([
        'error' => 'API thất bại',
        'details' => $e->getMessage()
    ], 500);
}

    }
}
