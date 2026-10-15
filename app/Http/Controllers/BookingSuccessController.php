<?php

namespace App\Http\Controllers; // Tempatkan controller dalam namespace HTTP Laravel.

use App\Models\Booking; // Import model Booking yang akan dipakai.
use App\Models\Business;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class BookingSuccessController extends Controller
{
    public function __invoke(Booking $booking)
    {
        $booking->load('item'); // Pastikan relasi item ikut dimuat untuk ditampilkan.

        if ($booking->payment_status !== 'paid') { // Cek apakah pembayaran belum ditandai selesai.
            $booking->update([ // Tandai pembayaran sebagai sukses dan sesuaikan status booking.
                'payment_status' => 'paid', // Set kolom payment_status menjadi paid.
                'status' => $booking->status === 'pending' ? 'confirmed' : $booking->status, // Ubah status pending menjadi confirmed.
                'meta' => array_merge($booking->meta ?? [], [ // Simpan metadata tambahan tanpa kehilangan data lama.
                    'payment_status_manually_set_at' => now()->toDateTimeString(), // Rekam kapan status diubah manual.
                ]),
            ]);

            $booking->refresh(); // Reload model agar nilai terbaru tersedia untuk view.
        }

        $this->sendSuccessNotification($booking);

        return view('pages.booking-success', [ // Render halaman sukses booking dengan data lengkap.
            'booking' => $booking, // Kirim data booking ke view.
            'item' => $booking->item, // Kirim data item terkait ke view.
        ]);
    }

    protected function sendSuccessNotification(Booking $booking): void
    {
        if (empty($booking->customer_phone)) {
            return;
        }

        $meta = $booking->meta ?? [];

        if (data_get($meta, 'notifications.whatsapp_sent_at')) {
            return; // Hindari pengiriman berulang saat pengguna membuka halaman berkali-kali.
        }

        $message = $this->buildSuccessMessage($booking);

        if (! $message) {
            return;
        }

        $token = config('fonnte.token');
        $endpoint = config('fonnte.endpoint', 'https://api.fonnte.com/send');

        if (empty($token) || empty($endpoint)) {
            return;
        }

        $target = $this->normalizePhoneNumber($booking->customer_phone);

        if (! $target) {
            return;
        }

        $payload = [
            'target' => $target,
            'message' => $message,
        ];

        $countryCode = config('fonnte.default_country_code', '62');

        if ($countryCode && ! str_starts_with($target, $countryCode)) {
            $payload['countryCode'] = $countryCode;
        }

        try {
            $response = Http::asForm()
                ->withHeaders(['Authorization' => $token])
                ->post($endpoint, $payload);
        } catch (\Throwable) {
            return;
        }

        if (! $response->successful()) {
            return;
        }

        Arr::set($meta, 'notifications.whatsapp_sent_at', now()->toDateTimeString());

        $booking->update([
            'meta' => $meta,
        ]);
    }

    protected function buildSuccessMessage(Booking $booking): string
    {
        if (! $booking->item || ! $booking->starts_at || ! $booking->ends_at) {
            return '';
        }

        $businessName = Business::value('nama_usaha') ?: config('app.name');
        $bookingDate = $booking->starts_at->copy()->timezone(config('app.timezone'));
        $bookingEnd = $booking->ends_at->copy()->timezone(config('app.timezone'));

        $formattedDate = $bookingDate->translatedFormat('l, d F Y');
        $timeRange = $bookingDate->format('H:i') . ' - ' . $bookingEnd->format('H:i') . ' WIB';
        $total = number_format((float) $booking->total_amount, 0, ',', '.');

        return implode("\n", [
            "{$businessName} - Booking Berhasil",
            '',
            "Halo {$booking->customer_name},",
            "Terima kasih sudah melakukan pemesanan {$booking->item->name}. Berikut ringkasan booking kamu:",
            '',
            "Kode Booking : {$booking->booking_code}",
            "Tanggal      : {$formattedDate}",
            "Waktu        : {$timeRange}",
            "Pesanan      : {$booking->item->name}",
            "Total        : Rp {$total}",
            '',
            'Sampai jumpa di tanggal tersebut. Jika ada pertanyaan, cukup balas pesan ini ya!',
        ]);
    }

    protected function normalizePhoneNumber(string $number): ?string
    {
        $digitsOnly = preg_replace('/\D+/', '', $number);

        return $digitsOnly !== '' ? $digitsOnly : null;
    }
}
