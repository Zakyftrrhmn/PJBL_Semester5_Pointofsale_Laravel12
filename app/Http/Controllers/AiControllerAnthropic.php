<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AiControllerAnthropic extends Controller
{
    /**
     * Versi menggunakan Anthropic Claude API
     */
    public function ask(Request $request)
    {
        $request->validate([
            'question' => 'required|string',
            'history' => 'nullable|array'
        ]);

        $question = $request->input('question');
        $history = $request->input('history', []);

        try {
            $apiKey = env('ANTHROPIC_API_KEY');

            // Build messages dengan history
            $messages = [];

            // Tambahkan history
            foreach ($history as $msg) {
                if (isset($msg['role']) && isset($msg['content'])) {
                    $messages[] = [
                        'role' => $msg['role'],
                        'content' => $msg['content']
                    ];
                }
            }

            // Tambahkan pertanyaan baru
            $messages[] = [
                'role' => 'user',
                'content' => $question
            ];

            // Call Anthropic API
            $response = Http::withHeaders([
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
                'Content-Type' => 'application/json',
            ])->timeout(30)->post('https://api.anthropic.com/v1/messages', [
                'model' => 'claude-3-sonnet-20240229',
                'max_tokens' => 1024,
                'system' => 'Anda adalah AI Assistant yang membantu pengguna dengan ramah dan profesional. Jawab dalam bahasa Indonesia.',
                'messages' => $messages
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $answer = $data['content'][0]['text'] ?? 'Tidak ada respon.';

                return response()->json([
                    'success' => true,
                    'answer' => $answer
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'answer' => 'Maaf, terjadi kesalahan saat memproses permintaan Anda.'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'answer' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
