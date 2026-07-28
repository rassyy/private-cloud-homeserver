<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $storageUsage = null;
        if ($request->user()) {
            $storagePath = storage_path('app/uploads');
            if (!is_dir($storagePath)) {
                @mkdir($storagePath, 0755, true);
            }
            $totalBytes = @disk_total_space($storagePath) ?: 1;
            $freeBytes = @disk_free_space($storagePath) ?: 1;
            $usedBytes = max(0, $totalBytes - $freeBytes);
            $percentage = min(100, round(($usedBytes / $totalBytes) * 100, 1));

            $formatBytes = function ($bytes) {
                $units = ['B', 'KB', 'MB', 'GB', 'TB'];
                $bytes = max($bytes, 0);
                $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
                $pow = min($pow, count($units) - 1);
                $bytes /= pow(1024, $pow);
                return round($bytes, 2) . ' ' . $units[$pow];
            };

            $storageUsage = [
                'total' => $totalBytes,
                'used' => $usedBytes,
                'free' => $freeBytes,
                'percentage' => $percentage,
                'formatted_used' => $formatBytes($usedBytes),
                'formatted_total' => $formatBytes($totalBytes),
            ];
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'storage_usage' => $storageUsage,
        ];
    }
}
