<?php

namespace App\Http\Controllers;

use App\Models\FellowRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BuktiTfController extends Controller
{
    public function show(Request $request, FellowRegistration $registration): StreamedResponse
    {
        abort_unless($request->user() !== null, 403);

        $path = $registration->bukti_tf_path;

        abort_unless(is_string($path) && $path !== '', 404);
        abort_unless(Storage::disk('public')->exists($path), 404);

        $absolutePath = Storage::disk('public')->path($path);
        $mime = Storage::disk('public')->mimeType($path) ?: 'application/octet-stream';
        $filename = basename($path);

        return response()->file($absolutePath, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }
}
