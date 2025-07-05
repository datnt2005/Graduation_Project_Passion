<?php

namespace App\Http\Controllers;

use App\Services\SearchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SearchController extends Controller
{
    protected $searchService;

    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    public function getSuggestions(Request $request)
    {
        $userId = auth()->id() ?: $request->query('user_id');
        $sessionId = $request->query('session_id', session()->getId());
        Log::info('Fetching suggestions', ['user_id' => $userId, 'session_id' => $sessionId]);
        $suggestions = $this->searchService->getSearchSuggestions($userId, $sessionId);

        return response()->json([
            'success' => true,
            'data' => $suggestions,
        ]);
    }

    public function addSearch(Request $request)
    {
        $userId = auth()->id() ?: $request->input('user_id');
        $sessionId = $request->input('session_id', session()->getId());
        $keyword = htmlspecialchars(trim($request->input('keyword')));
        Log::info('Adding search keyword', ['user_id' => $userId, 'session_id' => $sessionId, 'keyword' => $keyword]);

        if (empty($keyword)) {
            return response()->json([
                'success' => false,
                'message' => 'Từ khóa không hợp lệ.',
            ], 400);
        }

        $this->searchService->addSearch($userId, $sessionId, $keyword);

        return response()->json([
            'success' => true,
            'message' => 'Thêm từ khóa tìm kiếm thành công.',
        ]);
    }

    public function deleteHistory(Request $request)
    {
        $userId = auth()->id() ?: $request->query('user_id');
        $sessionId = $request->query('session_id', session()->getId());
        $keyword = htmlspecialchars(trim($request->query('keyword')));
        Log::info('Deleting history', ['user_id' => $userId, 'session_id' => $sessionId, 'keyword' => $keyword]);

        $this->searchService->deleteSearchHistory($userId, $sessionId, $keyword);

        return response()->json([
            'success' => true,
            'message' => 'Xóa lịch sử tìm kiếm thành công.',
        ]);
    }

    public function syncHistory(Request $request)
    {
        $userId = auth()->id();
        $sessionId = $request->query('session_id');
        Log::info('Syncing history', ['user_id' => $userId, 'session_id' => $sessionId]);

        if (!$userId || !$sessionId) {
            return response()->json([
                'success' => false,
                'message' => 'Thiếu user_id hoặc session_id.',
            ], 400);
        }

        $this->searchService->syncSessionToUser($sessionId, $userId);
        return response()->json([
            'success' => true,
            'message' => 'Đồng bộ lịch sử tìm kiếm thành công.',
        ]);
    }
}