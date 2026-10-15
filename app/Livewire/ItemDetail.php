<?php

namespace App\Livewire;

use App\Models\Booking;
use App\Models\Business;
use App\Models\Item;
use App\Services\MidtransGateway;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Component;

class ItemDetail extends Component
{
    // STEP 1: Definisikan seluruh properti state yang akan dipakai komponen.
    public Item $item; // Item yang sedang ditampilkan pada halaman detail.

    public ?Business $business = null; // Menyimpan pengaturan usaha aktif (jam buka, tipe booking).

    public string $selectedDate; // Tanggal yang sedang dipilih user untuk booking.

    public ?string $selectedSlotKey = null; // Kunci slot yang dipilih, null bila belum memilih jam.

    public bool $showDatePicker = false; // Flag pembuka picker tanggal manual di UI.

    public ?string $customDate = null; // Nilai input tanggal kustom yang dibind ke date picker.

    public string $customerName = ''; // Menyimpan nama customer dari form booking.

    public string $customerPhone = ''; // Menyimpan nomor telepon customer dari form booking.

    public ?string $customerEmail = null; // Menyimpan email customer jika diisi.

    public ?string $customerNote = null; // Catatan tambahan dari customer.

    public bool $isProcessing = false; // Penanda proses submit sedang berjalan.

    // STEP 2: Inisialisasi data dasar komponen ketika pertama kali di-load.
    public function mount(Item $item): void
    {
        $this->item = $item; // Simpan item yang di-inject lewat route model binding.
        $this->business = Business::first(); // Ambil pengaturan usaha pertama sebagai konfigurasi booking.
        $this->selectedDate = now()->addDay()->toDateString(); // Default tanggal = besok agar selalu future date.
        $this->customDate = $this->selectedDate; // Sinkronkan date picker manual dengan tanggal default.
        $this->customerPhone = '62'; // Prefill nomor telepon dengan kode negara Indonesia.
    }

    // STEP 3: Atur validasi data form booking.
    protected function rules(): array
    {
        return [
            'customerName' => ['required', 'string', 'min:3'], // Wajib minimal 3 karakter.
            'customerPhone' => ['required', 'string', 'min:8'], // Wajib minimal 8 digit angka (string untuk fleksibilitas).
            'customerEmail' => ['nullable', 'email'], // Boleh kosong, tapi harus format email.
            'customerNote' => ['nullable', 'string', 'max:500'], // Catatan opsional maksimal 500 karakter.
        ];
    }

    // STEP 4: Bangun daftar tanggal (7 hari) yang akan ditampilkan pada UI.
    public function getUpcomingDatesProperty(): Collection
    {
        $start = now()->addDay()->startOfDay(); // Ambil tanggal besok sebagai awal rentang.

        return Collection::times(7, function ($index) use ($start) { // Loop sebanyak 7 kali menggunakan helper Collection.
            $date = $start->copy()->addDays($index - 1); // Geser tanggal sesuai index (mulai dari besok).

            return [
                'key' => $date->toDateString(), // Nilai unik untuk Livewire key dan pembeda.
                'day' => $date->isoFormat('ddd'), // Singkatan nama hari lokal (misal Sen, Sel).
                'label' => $date->isoFormat('DD MMM'), // Label tanggal cantik untuk badge UI.
            ];
        });
    }

    // STEP 5: Tangani submit booking, simpan ke DB, lalu buat transaksi Midtrans.
    public function submit(MidtransGateway $midtrans)
    {
        $this->validate(); // Jalankan rules() agar semua input valid sebelum lanjut.

        $slot = collect($this->slots)->firstWhere('key', $this->selectedSlotKey); // Ambil slot terpilih dari daftar slot.

        if (! $slot) { // Jika slot tidak ditemukan, tampilkan error dan hentikan proses.
            $this->addError('selectedSlotKey', 'Silakan pilih tanggal dan waktu terlebih dahulu.');

            return;
        }

        $start = $slot['start']->copy(); // Clone waktu mulai slot supaya aman dimodifikasi.
        $end = $slot['end']->copy(); // Clone waktu selesai slot.

        if ($this->hasOverlap($start, $end)) { // Cek apakah slot sudah diambil orang lain.
            $this->addError('selectedSlotKey', 'Slot ini baru saja diambil orang lain. Silakan pilih slot lain.');

            return;
        }

        $this->isProcessing = true; // Aktifkan state loading agar tombol disable saat proses.
        $booking = null; // Siapkan variabel booking untuk akses di blok catch.

        try {
            $booking = DB::transaction(function () use ($slot, $start, $end) { // Pastikan pembuatan booking atomik.
                $bookingCode = $this->generateBookingCode(); // Bangun kode booking unik.
                $duration = $start->diffInMinutes($end); // Hitung durasi slot dalam menit.

                return Booking::create([ // Simpan data booking ke database.
                    'item_id' => $this->item->id,
                    'booking_code' => $bookingCode,
                    'customer_name' => $this->customerName,
                    'customer_phone' => $this->customerPhone,
                    'customer_email' => $this->customerEmail,
                    'booking_date' => $start->toDateString(),
                    'starts_at' => $start,
                    'ends_at' => $end,
                    'slot_duration' => $duration,
                    'slot_count' => 1,
                    'base_price' => $slot['amount'],
                    'total_amount' => $slot['amount'],
                    'status' => 'pending',
                    'payment_status' => 'unpaid',
                    'meta' => [
                        'selected_slot_key' => $slot['key'],
                        'slot_label' => $slot['label'],
                        'note' => $this->customerNote,
                    ],
                    'customer_note' => $this->customerNote,
                ]);
            });

            $response = $midtrans->createTransaction($booking, $this->item, [ // Kirim booking ke Midtrans Snap.
                'name' => $this->customerName,
                'email' => $this->customerEmail,
                'phone' => $this->customerPhone,
            ]);

            if (empty($response['redirect_url'])) { // Pastikan Midtrans mengembalikan URL pembayaran.
                throw new \RuntimeException('Midtrans tidak memberikan tautan pembayaran.');
            }

            $booking->update([ // Simpan data referensi pembayaran ke record booking.
                'payment_reference' => $booking->booking_code,
                'meta' => array_merge($booking->meta ?? [], [
                    'midtrans' => [
                        'token' => $response['token'] ?? null,
                        'redirect_url' => $response['redirect_url'] ?? null,
                    ],
                ]),
            ]);

            return redirect()->away($response['redirect_url']); // Arahkan user ke halaman pembayaran Midtrans.
        } catch (\Throwable $th) {
            if ($booking) { // Jika booking sudah terbuat tapi gagal proses, hapus kembali supaya tidak nyangkut.
                $booking->delete();
            }

            report($th); // Laporkan error ke sistem monitoring Laravel jika ada.

            $this->addError('midtrans', 'Terjadi kesalahan saat membuat transaksi. Silakan coba lagi.'); // Tampilkan pesan ke user.
        } finally {
            $this->isProcessing = false; // Matikan state loading apapun hasilnya.
        }
    }

    // STEP 6: Controller mini untuk interaksi tanggal di UI.
    public function selectDate(string $date): void
    {
        $this->selectedDate = Carbon::parse($date)->toDateString(); // Simpan tanggal terpilih dalam format YYYY-MM-DD.
        $this->selectedSlotKey = null; // Reset slot terpilih supaya user pilih ulang jam.
        $this->showDatePicker = false; // Tutup picker manual setelah pilih tanggal.
        $this->customDate = $this->selectedDate; // Update input date agar tetap sinkron.
    }

    public function toggleDatePicker(): void
    {
        $this->showDatePicker = ! $this->showDatePicker; // Flip state untuk menampilkan/menyembunyikan picker manual.
    }

    public function updatedCustomDate(?string $value): void
    {
        if ($value) { // Hanya eksekusi jika input memiliki nilai.
            $this->selectDate($value); // Panggil selectDate supaya efeknya konsisten seperti klik tombol tanggal.
        }
    }

    // STEP 7: Handle pemilihan slot jam pada form booking.
    public function selectSlot(string $slotKey): void
    {
        $slot = collect($this->slots)->firstWhere('key', $slotKey); // Cari slot berdasarkan key yang dikirim dari UI.

        if ($slot && ! $slot['isBooked']) { // Hanya simpan slot jika masih tersedia.
            $this->selectedSlotKey = $slotKey; // Tandai slot sebagai pilihan aktif user.
        }
    }

    // STEP 8: Bangun daftar slot berdasarkan pengaturan usaha dan booking yang sudah ada.
    public function getSlotsProperty(): array
    {
        $settings = $this->settings(); // Ambil konfigurasi jam buka, durasi slot, dsb.

        if (! $settings['isConfigured']) { // Jika bisnis belum di-set, kembalikan array kosong.
            return [];
        }

        $date = Carbon::parse($this->selectedDate); // Jadikan string tanggal menjadi objek Carbon.
        $dayStart = $settings['open']->copy()->setDate($date->year, $date->month, $date->day); // Merge jam buka dengan tanggal terpilih.
        $dayEnd = $settings['close']->copy()->setDate($date->year, $date->month, $date->day); // Merge jam tutup dengan tanggal terpilih.

        if ($dayStart->gte($dayEnd)) { // Jika jam buka >= jam tutup, tidak ada slot valid.
            return [];
        }

        $duration = $settings['slotDuration']; // Ambil durasi tiap slot (menit).
        $bookings = $this->bookingsForDate($date); // Ambil booking yang sudah ada pada tanggal tersebut.

        if ($settings['kind'] === 'per_hari') { // Jika tipe booking per hari, hanya ada satu slot penuh.
            return [
                $this->buildSlot($dayStart, $dayEnd, $bookings, 'FULL-DAY'),
            ];
        }

        $slots = []; // Inisialisasi array slot.
        $cursor = $dayStart->copy(); // Mulai dari jam buka.
        $counter = 1; // Digunakan untuk memberi nama slot berurutan.

        while ($cursor->lt($dayEnd)) { // Loop sampai melewati jam tutup.
            $slotStart = $cursor->copy(); // Waktu mulai slot saat ini.
            $slotEnd = $slotStart->copy()->addMinutes($duration); // Waktu selesai slot = start + durasi.

            if ($slotEnd->gt($dayEnd)) { // Jika melewati jam tutup, hentikan loop.
                break;
            }

            $slots[] = $this->buildSlot($slotStart, $slotEnd, $bookings, "SLOT-{$counter}"); // Tambahkan slot ke list.

            $cursor = $slotEnd; // Geser cursor ke akhir slot (mulai slot berikutnya).
            $counter++; // Naikkan nomor slot.
        }

        return $slots; // Kembalikan semua slot yang sudah disusun.
    }

    // STEP 9: Utility pembentuk data slot (label, harga, status booking).
    protected function buildSlot(Carbon $start, Carbon $end, Collection $bookings, string $key): array
    {
        $isBooked = $bookings->contains(function (Booking $booking) use ($start, $end) {
            return $booking->starts_at->lt($end) && $booking->ends_at->gt($start); // Slot dianggap booked jika rentang waktunya overlap.
        });

        $statusLabel = $isBooked ? 'Booked' : 'Tersedia'; // Label status untuk UI.

        return [
            'key' => $key, // Identifier slot unik.
            'start' => $start, // Objek Carbon mulai slot (dipakai lagi di submit).
            'end' => $end, // Objek Carbon akhir slot.
            'duration' => $this->formatDuration($start->diffInMinutes($end)), // Durasi manusiawi (Jam/Menit).
            'amount' => $this->pricePerSlot($start, $end), // Nilai angka harga slot.
            'price' => $this->formattedPrice($this->pricePerSlot($start, $end)), // Format harga untuk tampil di UI.
            'isBooked' => $isBooked, // Boolean apakah slot sudah diambil.
            'statusLabel' => $statusLabel, // Label status ringkas.
            'label' => $start->format('H:i') . ' - ' . $end->format('H:i'), // Label jam untuk tombol di UI.
        ];
    }

    // STEP 10: Ambil pengaturan booking dari model Business, fallback ke default jika kosong.
    protected function settings(): array
    {
        if (! $this->business || ! $this->business->jam_buka || ! $this->business->jam_tutup) { // Jika tidak ada data di DB.
            return [
                'isConfigured' => false, // Flag konfigurasi belum lengkap.
                'slotDuration' => 60, // Durasi default 60 menit.
                'open' => now()->startOfDay(), // Jam buka default 00:00.
                'close' => now()->copy()->setHour(23), // Jam tutup default 23:00.
                'kind' => 'per_jam', // Tipe booking default per jam.
            ];
        }

        $open = Carbon::parse($this->business->jam_buka); // Konversi jam buka string ke Carbon.
        $close = Carbon::parse($this->business->jam_tutup); // Konversi jam tutup string ke Carbon.
        $kind = $this->business->jenis_booking ?? 'per_jam'; // Gunakan jenis booking dari admin, fallback per_jam.

        return [
            'isConfigured' => true, // Tandai bahwa pengaturan sudah ada.
            'slotDuration' => $this->resolveSlotDuration($kind, $open, $close), // Hitung durasi slot sesuai jenis booking.
            'open' => $open, // Simpan jam buka.
            'close' => $close, // Simpan jam tutup.
            'kind' => $kind, // Simpan tipe booking.
        ];
    }

    // STEP 11: Tentukan durasi slot berdasarkan jenis booking yang dipilih.
    protected function resolveSlotDuration(string $kind, Carbon $open, Carbon $close): int
    {
        return match ($kind) {
            'per_jam' => 60, // Slot per jam.
            'per_12_jam' => 12 * 60, // Slot per 12 jam.
            'per_hari' => max(60, $open->diffInMinutes($close)), // Slot sehari penuh (minimal 60 menit).
            'custom' => max(15, (int) ($this->business?->durasi_booking ?? 60)), // Slot kustom minimal 15 menit.
            default => 60, // Default fallback 60 menit.
        };
    }

    // STEP 12: Ambil semua booking existing pada tanggal tertentu untuk memeriksa ketersediaan slot.
    protected function bookingsForDate(Carbon $date): Collection
    {
        return Booking::query()
            ->where('item_id', $this->item->id) // Filter booking milik item ini.
            ->whereDate('starts_at', $date->toDateString()) // Filter pada tanggal yang sama.
            ->get()
            ->filter(function (Booking $booking) {
                return strtolower($booking->status ?? '') !== 'cancelled'; // Abaikan booking yang sudah dibatalkan.
            });
    }

    // STEP 13: Utility untuk mengubah menit ke label durasi yang mudah dibaca.
    protected function formatDuration(int $minutes): string
    {
        if ($minutes % 60 === 0) { // Jika durasi kelipatan 60, tampilkan dalam jam.
            $hours = $minutes / 60;

            return $hours === 24
                ? 'Sehari Penuh' // Khusus 24 jam.
                : $hours . ' Jam'; // Format "x Jam".
        }

        return $minutes . ' Menit'; // Selain itu tampilkan dalam menit.
    }

    // STEP 14: Format angka harga menjadi string Rupiah.
    protected function formattedPrice(int $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.'); // Contoh output: Rp 150.000.
    }

    // STEP 15: Tentukan harga per slot (saat ini masih flat per item).
    protected function pricePerSlot(Carbon $start, Carbon $end): int
    {
        return (int) $this->item->price; // Ambil harga dasar item, bisa dikembangkan ke logika lain.
    }

    // STEP 16: Generator kode booking unik dengan prefix tanggal.
    protected function generateBookingCode(): string
    {
        do {
            $code = 'BK-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6)); // Contoh: BK-20250101-ABC123.
        } while (Booking::where('booking_code', $code)->exists()); // Ulangi jika kode sudah ada di DB.

        return $code; // Return kode unik.
    }

    // STEP 17: Cek apakah ada booking lain yang overlap dengan rentang waktu slot.
    protected function hasOverlap(Carbon $start, Carbon $end): bool
    {
        $overlaps = Booking::query()
            ->where('item_id', $this->item->id) // Booking untuk item yang sama.
            ->where('starts_at', '<', $end) // Mulainya sebelum akhir slot baru.
            ->where('ends_at', '>', $start) // Selesainya sesudah awal slot baru.
            ->whereNull('deleted_at') // Abaikan booking terhapus (soft delete).
            ->whereNotIn('status', ['cancelled']) // Abaikan booking yang dibatalkan.
            ->get(['id', 'booking_code', 'starts_at', 'ends_at', 'status', 'payment_status']); // Ambil kolom relevan.

        if ($overlaps->isNotEmpty()) { // Jika ada overlap, log peringatannya.
            Log::warning('Detected overlapping booking', [
                'item_id' => $this->item->id,
                'start' => $start->toDateTimeString(),
                'end' => $end->toDateTimeString(),
                'records' => $overlaps->map(function (Booking $booking) {
                    return [
                        'id' => $booking->id,
                        'code' => $booking->booking_code,
                        'start' => $booking->starts_at?->toDateTimeString(),
                        'end' => $booking->ends_at?->toDateTimeString(),
                        'status' => $booking->status,
                        'payment_status' => $booking->payment_status,
                    ];
                })->all(),
            ]);
        } else {
            Log::debug('No overlapping booking detected', [
                'item_id' => $this->item->id,
                'start' => $start->toDateTimeString(),
                'end' => $end->toDateTimeString(),
            ]);
        }

        return $overlaps->isNotEmpty(); // Return true jika ada bentrok.
    }

    // STEP 18: Render Blade view dan kirim data yang diperlukan.
    public function render()
    {
        return view('livewire.item-detail', [
            'business' => $this->business, // Kirim pengaturan usaha.
            'dates' => $this->upcomingDates, // Kirim daftar tanggal untuk UI.
            'slots' => $this->slots, // Kirim daftar slot yang sudah dihitung.
        ])->layout('layouts.home'); // Gunakan layout publik.
    }
}
