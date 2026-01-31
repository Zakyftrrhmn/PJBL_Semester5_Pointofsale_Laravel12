<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class AiAssistantController extends Controller
{
    public function ask(Request $request)
    {
        $question = strtolower($request->input('question'));
        $apiKey = env('GROQ_API_KEY');

        if (!$apiKey) {
            return response()->json(['answer' => 'API Key Groq belum diatur di .env']);
        }

        // KONTEKS SUPER LENGKAP
        $context = $this->getSmartContext($question);
        $prompt = $this->buildSmartPrompt($context, $question);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])
                ->withoutVerifying()
                ->timeout(30)
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => 'llama-3.3-70b-versatile',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Kamu asisten toko SUPER PINTAR yang tahu SEMUA detail toko. Jawab SINGKAT (3-7 kalimat), JELAS, dengan data PASTI. Pakai emoji relevan. Gunakan bullet points untuk list. Berikan insight kalau perlu.'
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'temperature' => 0.4,
                    'max_tokens' => 400
                ]);

            if ($response->successful()) {
                return response()->json([
                    'answer' => $response->json('choices.0.message.content')
                ]);
            }

            return response()->json(['answer' => 'Groq Error: ' . $response->body()], 500);
        } catch (\Exception $e) {
            return response()->json(['answer' => 'Koneksi Gagal: ' . $e->getMessage()], 500);
        }
    }

    private function getSmartContext($question)
    {
        // SELALU LOAD DATA INTI TOKO (INI KUNCI KEPINTARAN AI!)
        $context = [
            'info_dasar' => $this->getInfoDasar(),
        ];

        // Load data spesifik berdasarkan keyword
        if (preg_match('/omzet|pendapatan|revenue|penjualan|untung|profit/i', $question)) {
            $context['omzet'] = $this->getOmzetAnalysis($question);
        }

        if (preg_match('/produk|barang|item|stok|inventory|jumlah/i', $question)) {
            $context['produk'] = $this->getProdukAnalysis($question);
        }

        if (preg_match('/pelanggan|customer|pembeli/i', $question)) {
            $context['pelanggan'] = $this->getPelangganAnalysis();
        }

        if (preg_match('/kategori|jenis/i', $question)) {
            $context['kategori'] = $this->getKategoriAnalysis();
        }

        if (preg_match('/trend|tren|pertumbuhan|naik|turun/i', $question)) {
            $context['trend'] = $this->getTrendAnalysis();
        }

        if (preg_match('/kasir|karyawan|user|staff/i', $question)) {
            $context['kasir'] = $this->getKasirPerformance();
        }

        if (preg_match('/terlaris|favorit|populer/i', $question)) {
            $context['terlaris'] = $this->getTerlarisAnalysis();
        }

        if (preg_match('/lambat|stagnan|sepi|tidak laku/i', $question)) {
            $context['lambat'] = $this->getProdukLambat();
        }

        return $context;
    }

    private function getInfoDasar()
    {
        // DATA INTI YANG WAJIB AI KETAHUI!
        return [
            // PRODUK & STOK
            'total_produk' => DB::table('produks')->count(),
            'total_kategori' => DB::table('kategoris')->count(),
            'total_stok' => DB::table('produks')->sum('stok_produk'),
            'produk_habis' => DB::table('produks')->where('stok_produk', 0)->count(),
            'produk_kritis' => DB::table('produks')
                ->whereRaw('stok_produk > 0 AND stok_produk <= pengingat_stok')
                ->count(),

            // NILAI INVENTORY
            'nilai_modal' => DB::table('produks')
                ->selectRaw('SUM(stok_produk * harga_beli) as total')
                ->value('total') ?? 0,
            'nilai_jual' => DB::table('produks')
                ->selectRaw('SUM(stok_produk * harga_jual) as total')
                ->value('total') ?? 0,

            // OMZET & TRANSAKSI
            'omzet_hari_ini' => DB::table('penjualans')
                ->whereDate('tanggal_penjualan', Carbon::today())
                ->sum('total_bayar') ?? 0,
            'transaksi_hari_ini' => DB::table('penjualans')
                ->whereDate('tanggal_penjualan', Carbon::today())
                ->count(),
            'omzet_bulan_ini' => DB::table('penjualans')
                ->whereMonth('tanggal_penjualan', Carbon::now()->month)
                ->whereYear('tanggal_penjualan', Carbon::now()->year)
                ->sum('total_bayar') ?? 0,
            'transaksi_bulan_ini' => DB::table('penjualans')
                ->whereMonth('tanggal_penjualan', Carbon::now()->month)
                ->whereYear('tanggal_penjualan', Carbon::now()->year)
                ->count(),
            'omzet_total' => DB::table('penjualans')->sum('total_bayar') ?? 0,
            'transaksi_total' => DB::table('penjualans')->count(),

            // PELANGGAN
            'total_pelanggan' => DB::table('pelanggans')->count(),
            'total_kasir' => DB::table('users')->count(),

            // RATA-RATA
            'rata_transaksi' => DB::table('penjualans')->avg('total_bayar') ?? 0,
        ];
    }

    private function getOmzetAnalysis($question)
    {
        $range = $this->detectDateRange($question);

        $data = [
            'hari_ini' => DB::table('penjualans')
                ->whereDate('tanggal_penjualan', Carbon::today())
                ->selectRaw('SUM(total_bayar) as total, COUNT(*) as transaksi')
                ->first(),

            'kemarin' => DB::table('penjualans')
                ->whereDate('tanggal_penjualan', Carbon::yesterday())
                ->selectRaw('SUM(total_bayar) as total, COUNT(*) as transaksi')
                ->first(),

            'bulan_ini' => DB::table('penjualans')
                ->whereMonth('tanggal_penjualan', Carbon::now()->month)
                ->whereYear('tanggal_penjualan', Carbon::now()->year)
                ->selectRaw('SUM(total_bayar) as total, COUNT(*) as transaksi')
                ->first(),

            'bulan_lalu' => DB::table('penjualans')
                ->whereMonth('tanggal_penjualan', Carbon::now()->subMonth()->month)
                ->whereYear('tanggal_penjualan', Carbon::now()->subMonth()->year)
                ->selectRaw('SUM(total_bayar) as total, COUNT(*) as transaksi')
                ->first(),

            // 7 hari terakhir
            'weekly' => DB::table('penjualans')
                ->whereBetween('tanggal_penjualan', [Carbon::now()->subDays(7), Carbon::now()])
                ->selectRaw('DATE(tanggal_penjualan) as tanggal, SUM(total_bayar) as total')
                ->groupBy('tanggal')
                ->orderBy('tanggal', 'desc')
                ->limit(7)
                ->get(),
        ];

        if ($range) {
            $data['custom'] = DB::table('penjualans')
                ->whereBetween('tanggal_penjualan', [$range['start'], $range['end']])
                ->selectRaw('SUM(total_bayar) as total, COUNT(*) as transaksi')
                ->first();
        }

        return $data;
    }

    private function getProdukAnalysis($question)
    {
        $limit = 10;
        if (preg_match('/\b(\d+)\b/', $question, $matches)) {
            $limit = min((int)$matches[1], 30);
        }

        return [
            'terlaris_all' => DB::table('detail_penjualans as dp')
                ->join('produks as p', 'dp.produk_id', '=', 'p.id')
                ->selectRaw('p.nama_produk, p.stok_produk, SUM(dp.qty) as terjual, SUM(dp.subtotal) as omzet')
                ->groupBy('p.id', 'p.nama_produk', 'p.stok_produk')
                ->orderBy('terjual', 'desc')
                ->limit($limit)
                ->get(),

            'terlaris_bulan' => DB::table('detail_penjualans as dp')
                ->join('penjualans as pj', 'dp.penjualan_id', '=', 'pj.id')
                ->join('produks as p', 'dp.produk_id', '=', 'p.id')
                ->whereMonth('pj.tanggal_penjualan', Carbon::now()->month)
                ->selectRaw('p.nama_produk, SUM(dp.qty) as terjual')
                ->groupBy('p.id', 'p.nama_produk')
                ->orderBy('terjual', 'desc')
                ->limit($limit)
                ->get(),

            'stok_kritis' => DB::table('produks')
                ->whereRaw('stok_produk > 0 AND stok_produk <= pengingat_stok')
                ->select('nama_produk', 'stok_produk', 'pengingat_stok')
                ->orderBy('stok_produk', 'asc')
                ->limit(10)
                ->get(),

            'stok_habis' => DB::table('produks')
                ->where('stok_produk', 0)
                ->select('nama_produk', 'harga_jual')
                ->limit(10)
                ->get(),
        ];
    }

    private function getPelangganAnalysis()
    {
        return [
            'top_pelanggan' => DB::table('penjualans as pj')
                ->join('pelanggans as pl', 'pj.pelanggan_id', '=', 'pl.id')
                ->selectRaw('pl.nama_pelanggan, COUNT(*) as transaksi, SUM(pj.total_bayar) as total_belanja')
                ->groupBy('pl.id', 'pl.nama_pelanggan')
                ->orderBy('transaksi', 'desc')
                ->limit(10)
                ->get(),

            'aktif_bulan_ini' => DB::table('penjualans')
                ->whereMonth('tanggal_penjualan', Carbon::now()->month)
                ->distinct('pelanggan_id')
                ->count('pelanggan_id'),
        ];
    }

    private function getKategoriAnalysis()
    {
        return DB::table('kategoris as k')
            ->leftJoin('produks as p', 'k.id', '=', 'p.kategori_id')
            ->leftJoin('detail_penjualans as dp', 'p.id', '=', 'dp.produk_id')
            ->selectRaw('k.nama_kategori, COUNT(DISTINCT p.id) as jumlah_produk, SUM(dp.qty) as terjual, SUM(dp.subtotal) as omzet')
            ->groupBy('k.id', 'k.nama_kategori')
            ->orderBy('omzet', 'desc')
            ->get();
    }

    private function getTrendAnalysis()
    {
        return [
            'bulanan' => DB::table('penjualans')
                ->selectRaw('MONTH(tanggal_penjualan) as bulan, SUM(total_bayar) as omzet')
                ->whereYear('tanggal_penjualan', Carbon::now()->year)
                ->groupBy('bulan')
                ->orderBy('bulan', 'desc')
                ->limit(6)
                ->get(),
        ];
    }

    private function getKasirPerformance()
    {
        return DB::table('penjualans as pj')
            ->join('users as u', 'pj.user_id', '=', 'u.id')
            ->selectRaw('u.name, COUNT(*) as transaksi, SUM(pj.total_bayar) as omzet')
            ->groupBy('u.id', 'u.name')
            ->orderBy('omzet', 'desc')
            ->get();
    }

    private function getTerlarisAnalysis()
    {
        return DB::table('detail_penjualans as dp')
            ->join('produks as p', 'dp.produk_id', '=', 'p.id')
            ->join('penjualans as pj', 'dp.penjualan_id', '=', 'pj.id')
            ->whereMonth('pj.tanggal_penjualan', Carbon::now()->month)
            ->selectRaw('p.nama_produk, SUM(dp.qty) as terjual, SUM(dp.subtotal) as omzet')
            ->groupBy('p.id', 'p.nama_produk')
            ->orderBy('terjual', 'desc')
            ->limit(10)
            ->get();
    }

    private function getProdukLambat()
    {
        return DB::table('produks as p')
            ->leftJoin('detail_penjualans as dp', function ($join) {
                $join->on('p.id', '=', 'dp.produk_id')
                    ->where('dp.created_at', '>=', Carbon::now()->subDays(30));
            })
            ->whereNull('dp.id')
            ->where('p.stok_produk', '>', 0)
            ->select('p.nama_produk', 'p.stok_produk', 'p.harga_jual')
            ->limit(10)
            ->get();
    }

    private function detectDateRange($question)
    {
        $bulan = [
            'januari' => 1,
            'februari' => 2,
            'maret' => 3,
            'april' => 4,
            'mei' => 5,
            'juni' => 6,
            'juli' => 7,
            'agustus' => 8,
            'september' => 9,
            'oktober' => 10,
            'november' => 11,
            'desember' => 12
        ];

        if (preg_match('/(\d+)\s+(januari|februari|maret|april|mei|juni|juli|agustus|september|oktober|november|desember)\s+sampai\s+(\d+)\s+(januari|februari|maret|april|mei|juni|juli|agustus|september|oktober|november|desember)/i', $question, $matches)) {
            $start = Carbon::create(Carbon::now()->year, $bulan[strtolower($matches[2])], $matches[1]);
            $end = Carbon::create(Carbon::now()->year, $bulan[strtolower($matches[4])], $matches[3]);
            return ['start' => $start->toDateString(), 'end' => $end->toDateString()];
        }

        if (preg_match('/minggu lalu/i', $question)) {
            return [
                'start' => Carbon::now()->subWeek()->startOfWeek()->toDateString(),
                'end' => Carbon::now()->subWeek()->endOfWeek()->toDateString()
            ];
        }

        return null;
    }

    private function buildSmartPrompt($context, $question)
    {
        $prompt = "=== TOKO INTI PERAGA MANDIRI ===\n\n";
        $prompt .= "📅 " . Carbon::now()->translatedFormat('l, d F Y H:i') . "\n\n";

        // INFO DASAR (SELALU ADA - INI YANG BIKIN AI PINTAR!)
        $info = $context['info_dasar'];
        $prompt .= "📊 DATA INTI TOKO:\n";
        $prompt .= "• Total Produk: " . number_format($info['total_produk']) . " item\n";
        $prompt .= "• Total Kategori: " . number_format($info['total_kategori']) . "\n";
        $prompt .= "• Total Stok: " . number_format($info['total_stok']) . " unit\n";
        $prompt .= "• Produk Habis: " . number_format($info['produk_habis']) . "\n";
        $prompt .= "• Produk Stok Kritis: " . number_format($info['produk_kritis']) . "\n\n";

        $prompt .= "💰 NILAI INVENTORY:\n";
        $prompt .= "• Modal Stok: Rp " . number_format($info['nilai_modal'], 0, ',', '.') . "\n";
        $prompt .= "• Nilai Jual: Rp " . number_format($info['nilai_jual'], 0, ',', '.') . "\n";
        $prompt .= "• Potensi Profit: Rp " . number_format($info['nilai_jual'] - $info['nilai_modal'], 0, ',', '.') . "\n\n";

        $prompt .= "💵 OMZET:\n";
        $prompt .= "• Hari Ini: Rp " . number_format($info['omzet_hari_ini'], 0, ',', '.') . " ({$info['transaksi_hari_ini']} trx)\n";
        $prompt .= "• Bulan Ini: Rp " . number_format($info['omzet_bulan_ini'], 0, ',', '.') . " ({$info['transaksi_bulan_ini']} trx)\n";
        $prompt .= "• Total: Rp " . number_format($info['omzet_total'], 0, ',', '.') . " ({$info['transaksi_total']} trx)\n";
        $prompt .= "• Rata-rata/Transaksi: Rp " . number_format($info['rata_transaksi'], 0, ',', '.') . "\n\n";

        $prompt .= "👥 PELANGGAN & KASIR:\n";
        $prompt .= "• Total Pelanggan: " . number_format($info['total_pelanggan']) . "\n";
        $prompt .= "• Total Kasir: " . number_format($info['total_kasir']) . "\n\n";

        // DATA DETAIL
        if (isset($context['omzet'])) {
            $o = $context['omzet'];
            $prompt .= "📈 DETAIL OMZET:\n";
            $prompt .= "• Kemarin: Rp " . number_format($o['kemarin']->total ?? 0, 0, ',', '.') . "\n";
            $prompt .= "• Bulan Lalu: Rp " . number_format($o['bulan_lalu']->total ?? 0, 0, ',', '.') . "\n\n";
        }

        if (isset($context['produk'])) {
            $p = $context['produk'];
            if ($p['terlaris_all']->count() > 0) {
                $prompt .= "🏆 TOP PRODUK:\n";
                $no = 1;
                foreach ($p['terlaris_all']->take(7) as $prod) {
                    $prompt .= "{$no}. {$prod->nama_produk}: {$prod->terjual} unit (Stok: {$prod->stok_produk})\n";
                    $no++;
                }
                $prompt .= "\n";
            }

            if ($p['stok_kritis']->count() > 0) {
                $prompt .= "⚠️ STOK KRITIS:\n";
                foreach ($p['stok_kritis']->take(5) as $s) {
                    $prompt .= "• {$s->nama_produk}: {$s->stok_produk} unit\n";
                }
                $prompt .= "\n";
            }
        }

        if (isset($context['pelanggan']) && $context['pelanggan']['top_pelanggan']->count() > 0) {
            $prompt .= "👑 TOP PELANGGAN:\n";
            foreach ($context['pelanggan']['top_pelanggan']->take(5) as $pl) {
                $prompt .= "• {$pl->nama_pelanggan}: {$pl->transaksi} trx\n";
            }
            $prompt .= "\n";
        }

        if (isset($context['kategori'])) {
            $prompt .= "📁 KATEGORI:\n";
            foreach ($context['kategori']->take(5) as $k) {
                $prompt .= "• {$k->nama_kategori}: {$k->jumlah_produk} produk\n";
            }
            $prompt .= "\n";
        }

        if (isset($context['kasir'])) {
            $prompt .= "👨‍💼 PERFORMA KASIR:\n";
            foreach ($context['kasir']->take(5) as $k) {
                $prompt .= "• {$k->name}: {$k->transaksi} trx, Rp " . number_format($k->omzet, 0, ',', '.') . "\n";
            }
            $prompt .= "\n";
        }

        $prompt .= "═══════════════════════════════\n";
        $prompt .= "❓ PERTANYAAN: $question\n";
        $prompt .= "═══════════════════════════════\n\n";
        $prompt .= "✅ Jawab LANGSUNG dengan DATA PASTI di atas!\n";
        $prompt .= "✅ Pakai emoji relevan\n";
        $prompt .= "✅ Kasih insight jika perlu\n";
        $prompt .= "✅ Maksimal 5-7 kalimat\n";

        return $prompt;
    }
}
