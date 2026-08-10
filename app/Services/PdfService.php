<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\View;

class PdfService
{
    public function render(string $view, array $data, string $filename): Response
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('tempDir', storage_path('framework/cache'));

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(View::make($view, $data)->render());
        $dompdf->setPaper('A4', $data['orientation'] ?? 'portrait');
        $dompdf->render();

        return $dompdf->stream($filename . '.pdf', ['Attachment' => true]);
    }
}
