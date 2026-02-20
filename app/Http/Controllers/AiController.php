<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AiController extends Controller
{
    public function ask(Request $request)
    {
        $request->validate([
            'question' => 'required|string',
            'history' => 'nullable|array'
        ]);

        $question = $request->input('question');
        $history = $request->input('history', []);

        try {
            // Contoh menggunakan OpenAI API
            // Ganti dengan API key Anda
            $apiKey = env('OPENAI_API_KEY');

            // Build messages dengan history
            $messages = [];

            // System prompt
            $messages[] = [
                'role' => 'system',
                'content' => 'Anda adalah AI Assistant yang membantu pengguna dengan ramah dan profesional. Jawab dalam bahasa Indonesia.'
            ];

            // Tambahkan history (maksimal 10 pesan terakhir)
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

            // Call OpenAI API
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-3.5-turbo',
                'messages' => $messages,
                'max_tokens' => 500,
                'temperature' => 0.7,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $answer = $data['choices'][0]['message']['content'] ?? 'Tidak ada respon.';

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
