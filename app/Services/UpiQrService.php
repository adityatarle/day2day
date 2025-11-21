<?php

namespace App\Services;

use SimpleSoftwareIO\QrCode\Facades\QrCode;

class UpiQrService
{
    /**
     * Generate UPI payment string.
     * 
     * @param float $amount
     * @param string $upiId UPI ID (e.g., merchant@paytm, merchant@phonepe)
     * @param string $merchantName
     * @param string $transactionNote
     * @return string
     */
    public function generateUpiString(
        float $amount,
        string $upiId,
        string $merchantName = 'Day2Day',
        string $transactionNote = 'Payment'
    ): string {
        // UPI payment string format
        // upi://pay?pa=<UPI_ID>&pn=<MERCHANT_NAME>&am=<AMOUNT>&cu=INR&tn=<TRANSACTION_NOTE>
        
        $amountFormatted = number_format($amount, 2, '.', '');
        
        return sprintf(
            'upi://pay?pa=%s&pn=%s&am=%s&cu=INR&tn=%s',
            urlencode($upiId),
            urlencode($merchantName),
            $amountFormatted,
            urlencode($transactionNote)
        );
    }

    /**
     * Generate QR code as SVG string.
     * 
     * @param string $upiString
     * @return string SVG content
     */
    public function generateQrCodeSvg(string $upiString): string
    {
        return QrCode::size(300)
            ->generate($upiString);
    }

    /**
     * Generate QR code as base64 image.
     * 
     * @param string $upiString
     * @return string Base64 encoded image
     */
    public function generateQrCodeBase64(string $upiString): string
    {
        $qrCode = QrCode::format('png')
            ->size(300)
            ->generate($upiString);
        
        return 'data:image/png;base64,' . base64_encode($qrCode);
    }

    /**
     * Generate complete UPI QR code data for payment.
     * 
     * @param float $amount
     * @param string $upiId
     * @param string $merchantName
     * @param string $transactionNote
     * @return array
     */
    public function generateUpiQrData(
        float $amount,
        string $upiId,
        string $merchantName = 'Day2Day',
        string $transactionNote = 'Payment'
    ): array {
        $upiString = $this->generateUpiString($amount, $upiId, $merchantName, $transactionNote);
        $qrSvg = $this->generateQrCodeSvg($upiString);
        $qrBase64 = $this->generateQrCodeBase64($upiString);

        return [
            'upi_string' => $upiString,
            'qr_code_svg' => $qrSvg,
            'qr_code_base64' => $qrBase64,
            'amount' => $amount,
            'upi_id' => $upiId,
            'merchant_name' => $merchantName,
        ];
    }
}


