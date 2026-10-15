<?php // Berkas PHP yang mendefinisikan service gateway pembayaran Midtrans.

namespace App\Services; // Namespace untuk mengelompokkan kelas-kelas layer service.

use App\Models\Booking; // Model Booking menyediakan data reservasi.
use App\Models\Item; // Model Item merepresentasikan produk yang dipesan.
use Illuminate\Support\Arr; // Helper Arr memudahkan akses array yang aman.
use Midtrans\Config; // Config dari SDK Midtrans menyimpan konfigurasi.
use Midtrans\Snap; // Klien API Midtrans Snap.

class MidtransGateway // Kelas service yang membungkus interaksi dengan Midtrans Snap.
{
    public function __construct() // Konstruktor menginisialisasi nilai konfigurasi Midtrans.
    {
        Config::$isProduction = (bool) config('midtrans.is_production', false); // Mengatur mode sandbox/production sesuai konfigurasi.
        Config::$serverKey = config('midtrans.server_key'); // Mengisi server key dari konfigurasi.
        Config::$clientKey = config('midtrans.client_key'); // Mengisi client key yang dipakai frontend.
        Config::$isSanitized = true; // Memaksa sanitasi payload demi keamanan.
        Config::$curlOptions = config('midtrans.options', []); // Memuat opsi cURL tambahan jika ada.
        if (! isset(Config::$curlOptions[CURLOPT_HTTPHEADER])) { // Memastikan array header HTTP tersedia.
            Config::$curlOptions[CURLOPT_HTTPHEADER] = []; // Menginisialisasi wadah header jika belum ada.
        }
    }

    /**
     * Membuat transaksi Snap dan mengembalikan payload.
     */
    public function createTransaction(Booking $booking, Item $item, array $customer): array // Menyusun payload transaksi untuk Midtrans Snap.
    {
        if (empty(Config::$serverKey)) { // Validasi bila server key belum diisi.
            throw new \RuntimeException('Midtrans server key belum dikonfigurasi.'); // Lempar pengecualian runtime dengan pesan yang sesuai.
        }

        $transaction = [ // Menyusun body permintaan yang dibutuhkan Midtrans.
            'transaction_details' => [ // Bagian meta transaksi utama.
                'order_id' => $booking->booking_code, // Kode booking unik sebagai ID pesanan.
                'gross_amount' => (int) round($booking->total_amount), // Total transaksi dibulatkan dan dikonversi ke integer sesuai ekspektasi API.
            ],
            'item_details' => [ // Daftar item yang dibeli.
                [
                    'id' => (string) $item->id, // ID item harus berupa string sesuai spesifikasi.
                    'price' => (int) round($booking->base_price), // Harga per unit dibulatkan ke integer.
                    'quantity' => (int) max(1, $booking->slot_count), // Memastikan kuantitas minimal satu.
                    'name' => mb_substr($item->name, 0, 50), // Memotong nama item hingga 50 karakter sesuai batas API.
                ],
            ],
            'customer_details' => [ // Informasi identitas pembeli.
                'first_name' => Arr::get($customer, 'name'), // Nama pelanggan diambil aman dari array.
                'email' => Arr::get($customer, 'email'), // Alamat email pelanggan.
                'phone' => Arr::get($customer, 'phone'), // Nomor telepon pelanggan.
            ],
            'callbacks' => array_filter([ // URL callback yang akan dipanggil Midtrans.
                'finish' => $this->resolveFinishUrl($booking), // URL selesai yang disesuaikan dengan booking.
                'unfinish' => config('midtrans.unfinish_url'), // URL opsional saat pengguna batal membayar.
                'error' => config('midtrans.error_url'), // URL opsional ketika pembayaran gagal.
            ]),
        ];

        $result = Snap::createTransaction($transaction); // Meminta token transaksi dari API Midtrans Snap.

        return is_array($result) ? $result : json_decode(json_encode($result), true); // Pastikan respons berupa array sebelum dikembalikan.
    }

    protected function resolveFinishUrl(Booking $booking): string // Menentukan URL redirect akhir setelah pembayaran.
    {
        $configured = config('midtrans.finish_url'); // Mengambil URL finish yang dikonfigurasi jika ada.

        return $configured ?: route('booking.success', ['booking' => $booking->booking_code]); // Pakai URL terkonfigurasi atau route sukses booking sebagai default.
    }
} // Akhir dari kelas service MidtransGateway.
