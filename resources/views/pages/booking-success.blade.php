@extends('layouts.home')

@section('content')
    <section class="py-10 md:py-16">
        <div class="max-w-3xl mx-auto px-4">
            <div class="text-center mb-10">
                <div class="mx-auto size-16 flex items-center justify-center rounded-full bg-emerald-100 text-emerald-600 mb-4">
                    ✓
                </div>
                <h1 class="text-3xl font-semibold text-slate-900 mb-2">Pembayaran Berhasil</h1>
                <p class="text-slate-500">Terima kasih! Berikut ringkasan booking Anda.</p>
            </div>

            <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
                <div class="bg-slate-900 text-white px-6 py-4 flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-200">Kode Booking</p>
                        <p class="text-xl font-semibold">{{ $booking->booking_code }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-slate-300">Status</p>
                        <p class="text-lg font-semibold capitalize">{{ $booking->payment_status }}</p>
                    </div>
                </div>

                <div class="px-6 py-6 space-y-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <p class="text-sm text-slate-500">Item</p>
                            <p class="text-lg font-semibold text-slate-900">{{ $item?->name ?? '-' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-slate-500">Total Pembayaran</p>
                            <p class="text-2xl font-semibold text-slate-900">Rp
                                {{ number_format($booking->total_amount, 0, ',', '.') }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="rounded-2xl border border-slate-200 p-4">
                            <p class="text-sm text-slate-500 mb-1">Waktu Booking</p>
                            <p class="font-semibold text-slate-900">
                                {{ $booking->starts_at->translatedFormat('l, d F Y') }}
                            </p>
                            <p class="text-slate-600">
                                {{ $booking->starts_at->format('H:i') }} - {{ $booking->ends_at->format('H:i') }}
                            </p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 p-4">
                            <p class="text-sm text-slate-500 mb-1">Kontak Customer</p>
                            <p class="font-semibold text-slate-900">{{ $booking->customer_name }}</p>
                            <p class="text-slate-600">{{ $booking->customer_phone }}</p>
                            @if ($booking->customer_email)
                                <p class="text-slate-600">{{ $booking->customer_email }}</p>
                            @endif
                        </div>
                    </div>

                    @if ($booking->customer_note)
                        <div class="rounded-2xl border border-slate-200 p-4">
                            <p class="text-sm text-slate-500 mb-1">Catatan</p>
                            <p class="text-slate-700">{{ $booking->customer_note }}</p>
                        </div>
                    @endif

                    <div class="rounded-2xl border border-slate-200 p-4">
                        <p class="text-sm text-slate-500 mb-1">Pembayaran</p>
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                            <p class="font-semibold text-slate-900">Status: {{ ucfirst($booking->payment_status) }}</p>
                            <p class="text-slate-600 text-sm">
                                Referensi: {{ $booking->payment_reference ?? $booking->booking_code }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 text-center">
                <a href="{{ route('home') }}"
                    class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 transition">
                    Kembali ke Beranda
                </a>
            </div>
        </div>
    </section>
@endsection
