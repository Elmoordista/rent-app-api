<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfController extends Controller
{
    public function export(Request $request, $payments = null)
    {
        $transaction = $request->all();

        $pdf = Pdf::loadView('pdf.invoice', [
            'headerNote' => '',
            'transaction' => $transaction,
        ]);

        return $pdf->download('payments.pdf');
    }
}
