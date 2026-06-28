<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FcmToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FcmTokenController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate(['token' => 'required|string']);

        $hash = hash('sha256', $request->token);

        FcmToken::updateOrCreate(
            ['user_id' => auth()->id(), 'token_hash' => $hash],
            ['token'   => $request->token, 'device' => substr($request->userAgent() ?? 'unknown', 0, 100)]
        );

        return response()->json(['ok' => true]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['token' => 'required|string']);

        FcmToken::where('user_id', auth()->id())
                ->where('token_hash', hash('sha256', $request->token))
                ->delete();

        return response()->json(['ok' => true]);
    }
}

