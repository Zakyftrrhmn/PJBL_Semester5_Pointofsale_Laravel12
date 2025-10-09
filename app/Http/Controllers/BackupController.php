<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use ZipArchive;

class BackupController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:backup.index')->only('index', 'download');
        $this->middleware('permission:backup.create')->only('importNow');
        $this->middleware('permission:backup.delete')->only('delete');
    }

    public function index()
    {
        $backupPath = storage_path('app/private/Laravel');
        if (!File::exists($backupPath)) {
            File::makeDirectory($backupPath, 0755, true);
        }
        $files = collect(File::files($backupPath))
            ->filter(
                fn($file) =>
                str_ends_with($file->getFilename(), '.zip') ||
                    str_ends_with($file->getFilename(), '.sql')
            )
            ->map(fn($file) => [
                'filename' => $file->getFilename(),
                'size' => $this->formatBytes($file->getSize()),
                'date' => date('Y-m-d H:i:s', $file->getMTime()),
            ])
            ->sortByDesc('date')
            ->values();

        return view('pages.backup.index', ['backups' => $files]);
    }

    public function importNow()
    {
        try {
            $backupPath = storage_path('app/private/Laravel');
            if (!File::exists($backupPath)) {
                File::makeDirectory($backupPath, 0755, true);
            }

            $filename = 'backup-' . now()->format('Y-m-d_H-i-s') . '.sql';
            $filepath = $backupPath . '/' . $filename;

            // ambil konfigurasi database dari .env
            $dbHost = env('DB_HOST');
            $dbUser = env('DB_USERNAME');
            $dbPass = env('DB_PASSWORD');
            $dbName = env('DB_DATABASE');

            // jalankan perintah mysqldump
            $command = sprintf(
                'mysqldump --user=%s --password=%s --host=%s %s > %s',
                escapeshellarg($dbUser),
                escapeshellarg($dbPass),
                escapeshellarg($dbHost),
                escapeshellarg($dbName),
                escapeshellarg($filepath)
            );

            exec($command, $output, $result);

            if ($result !== 0) {
                throw new \Exception('Gagal mengekspor database. Pastikan mysqldump sudah terpasang dan PATH sudah benar.');
            }

            return redirect()->route('backup.index')->with('success', "Backup database berhasil dibuat: $filename");
        } catch (\Exception $e) {
            return redirect()->route('backup.index')->with('error', 'Gagal membuat backup: ' . $e->getMessage());
        }
    }


    public function download($filename)
    {
        $file = storage_path('app/private/Laravel/' . $filename);
        if (!File::exists($file)) {
            return redirect()->route('backup.index')->with('error', 'File tidak ditemukan.');
        }
        return response()->download($file);
    }

    // 🔥 Tambahan baru: HAPUS FILE BACKUP
    public function delete($filename)
    {
        $file = storage_path('app/private/Laravel/' . $filename);
        if (!File::exists($file)) {
            return redirect()->route('backup.index')->with('error', 'File backup tidak ditemukan.');
        }

        try {
            File::delete($file);
            return redirect()->route('backup.index')->with('success', 'File backup berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->route('backup.index')->with('error', 'Gagal menghapus file: ' . $e->getMessage());
        }
    }

    private function formatBytes($size)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = $size > 0 ? floor(log($size, 1024)) : 0;
        return number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
    }
}
