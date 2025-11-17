<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HelpController extends Controller
{
    public function index()
    {
        // No DB needed, just return static view
        return view('help.index');
    }

    /**
     * Stream PDF inline for view-only display.
     *
     * The PDF file is expected to be stored at storage/app/private/help/help_manual.pdf
     * (not in public storage). We return it with Content-Disposition: inline to hint browsers to display it.
     */

    public function viewPdf()
    {
        $relativePath = 'private/help/help_manual.pdf';
        $path = storage_path('app/' . $relativePath);

        // echo'<pre>';print_r($path);die;

        if (! file_exists($path) || ! is_readable($path)) {
            abort(404, 'Help PDF not found or not readable: ' . $path);
        }

        $size = filesize($path);

        return new StreamedResponse(function () use ($path) {
            $fp = fopen($path, 'rb');
            if ($fp === false) {
                return;
            }
            while (! feof($fp)) {
                echo fread($fp, 1024 * 8);
                flush();
            }
            fclose($fp);
        }, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Length' => $size,
            'Content-Disposition' => 'inline; filename="help_manual.pdf"',
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'public, must-revalidate, max-age=0',
        ]);
    }
}
