<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Order;
use Illuminate\Http\Response;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

trait GeneratesReceiptPdf
{
    /**
     * Render the order's PDF receipt as an inline HTTP response.
     */
    protected function streamReceiptPdf(Order $order): Response
    {
        $html = view('admin.receipt-pdf', compact('order'))->render();

        $mpdf = new Mpdf(['mode' => 'utf-8', 'format' => 'A4']);
        $mpdf->WriteHTML($html);

        return response($mpdf->Output($order->order_number.'-receipt.pdf', Destination::STRING_RETURN), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$order->order_number.'-receipt.pdf"',
        ]);
    }
}
