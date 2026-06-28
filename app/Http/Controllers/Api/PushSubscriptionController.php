<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PushSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'endpoint'         => 'required|string',
            'public_key'       => 'nullable|string',
            'auth_token'       => 'nullable|string',
            'content_encoding' => 'nullable|string',
        ]);

        $hash = hash('sha256', $request->endpoint);

        PushSubscription::updateOrCreate(
            ['endpoint_hash' => $hash],
            [
                'user_id'          => auth()->id(),
                'endpoint'         => $request->endpoint,
                'public_key'       => $request->public_key,
                'auth_token'       => $request->auth_token,
                'content_encoding' => $request->content_encoding ?? 'aesgcm',
                'device'           => substr($request->userAgent() ?? 'unknown', 0, 100),
            ]
        );

        return response()->json(['ok' => true]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['endpoint' => 'required|string']);

        PushSubscription::where('endpoint_hash', hash('sha256', $request->endpoint))->delete();

        return response()->json(['ok' => true]);
    }
}
