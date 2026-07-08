<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Support\Facades\File;

class ImageCacheController extends Controller
{
    public function show(Request $request, string $path): BinaryFileResponse
    {
        $base = realpath(storage_path('app/public'));
        $full = realpath($base . '/' . $path);
        
        abort_unless($full && str_starts_with($full, $base) && file_exists($full), 404);

        if ($request->has('w')) {
            $width = (int) $request->query('w');
            $cachePath = storage_path('app/public/thumbs/' . $width . '_' . md5($path) . '.jpg');
            
            if (!file_exists($cachePath)) {
                File::ensureDirectoryExists(dirname($cachePath));
                $img = app('image')->decodePath($full);
                $img->scaleDown(width: $width);
                $img->save($cachePath, quality: 80);
            }
            return response()->file($cachePath);
        }

        return response()->file($full);
    }
}
