{{-- STEP A: Bungkus utama halaman detail item dengan latar abu muda. --}}
<div class="bg-gray-50 min-h-screen">
    <section>
        <div class="max-w-7xl mx-auto px-4 space-y-12">
            {{-- STEP B: Hero produk berisi gambar utama dan thumbnail. --}}
            <div class="flex flex-col md:flex-row gap-4">
                <div class="w-full md:w-8/12">
                    <div class="rounded-2xl overflow-hidden bg-slate-200 h-64 md:h-[420px]">
                        @if ($item->image)
                            {{-- Tampilkan gambar item dari storage aplikasi. --}}
                            <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}"
                                class="w-full h-full object-cover">
                        @else
                            {{-- Pesan placeholder saat tidak ada gambar. --}}
                            <div class="w-full h-full flex items-center justify-center text-slate-500">
                                Tidak ada gambar
                            </div>
                        @endif
                    </div>
                </div>
                <div class="w-full md:w-4/12">
                    <div class="grid grid-cols-2 md:grid-cols-1 gap-4">
                        @foreach (range(1, 2) as $thumb)
                            {{-- Thumbnail tambahan (bisa diganti foto berbeda di iterasi berikut). --}}
                            <div class="rounded-2xl overflow-hidden h-32 md:h-[200px] bg-slate-200">
                                @if ($item->image)
                                    <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}"
                                        class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-slate-500">
                                        Tidak ada gambar
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- STEP C: Informasi konten item dan panel harga. --}}
            <div class="flex flex-col md:flex-row gap-6">
                <div class="w-full md:w-8/12 space-y-6">
                    {{-- Badge kategori item untuk memberi konteks layanan. --}}
                    <span
                        class="inline-flex items-center gap-2 bg-indigo-50 text-indigo-600 px-3 py-1 rounded-full text-sm">
                        <span class="size-1.5 bg-indigo-500 rounded-full"></span>
                        {{ $item->category?->name ?? 'General' }}
                    </span>
                    {{-- Judul item tampil besar agar langsung terbaca. --}}
                    <h2 class="text-3xl font-semibold text-slate-900">{{ $item->name }}</h2>
                    <hr class="border-slate-200">
                    {{-- Deskripsi item yang dapat memuat konten rich text. --}}
                    <div>
                        <p class="text-lg font-medium text-slate-900 mb-2">Deskripsi</p>
                        <div class="text-slate-600 leading-relaxed rich-text">
                            {!! $item->description ?? 'Deskripsi belum tersedia.' !!}
                        </div>
                    </div>
                </div>
                <div class="w-full md:w-4/12">
                    {{-- Panel ringkasan harga dan informasi bisnis. --}}
                    <div class="bg-white rounded-2xl p-6 shadow-sm sticky top-6">
                        <p class="text-sm text-slate-500 mb-1">Mulai dari</p>
                        <p class="text-2xl font-semibold text-slate-900 mb-6">
                            Rp {{ number_format($item->price, 0, ',', '.') }}
                            <span class="text-xs font-normal text-slate-500">
                                / {{ $business?->jenis_booking === 'per_hari' ? 'Hari' : 'Sesi' }}
                            </span>
                        </p>

                        <div class="space-y-2 text-sm text-slate-600 mb-6">
                            @if ($business)
                                {{-- Detail jam operasional dan jenis booking dari pengaturan admin. --}}
                                <p>
                                    <strong class="text-slate-800">Jam Operasional:</strong>
                                    {{ \Carbon\Carbon::parse($business->jam_buka)->format('H:i') }} -
                                    {{ \Carbon\Carbon::parse($business->jam_tutup)->format('H:i') }}
                                </p>
                                <p>
                                    <strong class="text-slate-800">Tipe Booking:</strong>
                                    {{ $business->jenis_booking ? $business->jenis_booking_label : 'Per Jam' }}
                                </p>
                            @else
                                {{-- Notifikasi ketika konfigurasi bisnis belum dilakukan. --}}
                                <p>Pengaturan booking belum dikonfigurasi.</p>
                            @endif
                        </div>

                        {{-- Tombol lompat ke bagian pemilihan tanggal & slot. --}}
                        <a href="#pilih-slot"
                            class="block text-center px-4 py-3 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 transition">
                            Pilih Tanggal dan Jam
                        </a>
                    </div>
                </div>
            </div>

            <hr class="border-slate-200">

            {{-- STEP D: Seksi pemilihan tanggal, slot, dan form data customer. --}}
            <div id="pilih-slot" class="space-y-10">
                {{-- STEP D1: Grid tanggal otomatis + picker custom. --}}
                <div>
                    <p class="text-lg font-medium text-slate-900 mb-4">Pilih Tanggal</p>
                    <div class="grid grid-cols-3 md:grid-cols-6 xl:grid-cols-8 gap-3">
                        @foreach ($dates as $date)
                            @php
                                $isActive = $selectedDate === $date['key']; // Tandai tanggal aktif agar mudah dikenali.
                            @endphp
                            <button type="button" wire:click="selectDate('{{ $date['key'] }}')"
                                wire:key="date-{{ $date['key'] }}"
                                class="rounded-2xl border px-4 py-3 text-center transition focus:outline-none focus:ring-2 focus:ring-indigo-300 {{ $isActive ? 'border-indigo-600 bg-indigo-50 text-indigo-900' : 'border-slate-200 bg-white hover:border-indigo-400 hover:shadow-md' }}">
                                <span class="block text-xs uppercase tracking-wide text-slate-500">
                                    {{ $date['day'] }}
                                </span>
                                <span class="block text-lg font-semibold">{{ $date['label'] }}</span>
                            </button>
                        @endforeach
                        {{-- Tombol membuka date picker manual bila butuh tanggal lain. --}}
                        <button type="button" wire:click="toggleDatePicker"
                            class="rounded-2xl border border-dashed border-slate-300 px-4 py-3 text-center text-slate-500 hover:border-indigo-400 hover:text-indigo-500 transition">
                            Pilih Tanggal Lain
                        </button>
                    </div>

                    @if ($showDatePicker)
                        {{-- Date picker manual terikat ke properti Livewire customDate. --}}
                        <div class="mt-4 max-w-xs">
                            <label class="text-sm text-slate-600 mb-1 block">Tanggal Custom</label>
                            <input type="date" min="{{ now()->addDay()->toDateString() }}" wire:model.live="customDate"
                                class="w-full rounded-xl border border-slate-200 px-4 py-3 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none">
                        </div>
                    @endif
                </div>

                {{-- STEP D2: Pilihan slot waktu booking. --}}
                <div>
                    <p class="text-lg font-medium text-slate-900 mb-4">Pilih Slot Waktu</p>

                    @if (empty($slots))
                        {{-- Pesan fallback saat slot belum tersedia. --}}
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-5 text-sm text-slate-600">
                            Pengaturan booking belum lengkap atau belum ada slot tersedia.
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            @foreach ($slots as $slot)
                                @php
                                    $isSelected = $selectedSlotKey === $slot['key']; // Highlight slot aktif.
                                @endphp
                                <button type="button" wire:click="selectSlot('{{ $slot['key'] }}')"
                                    wire:key="slot-{{ $slot['key'] }}" @disabled($slot['isBooked'])
                                    class="relative rounded-2xl border px-4 py-5 text-left transition focus:outline-none focus:ring-2 focus:ring-indigo-300 {{ $slot['isBooked'] ? 'bg-slate-100 border-slate-200 text-slate-400 cursor-not-allowed' : ($isSelected ? 'border-indigo-600 bg-indigo-50 text-indigo-900' : 'bg-white border-slate-200 hover:border-indigo-400 hover:shadow') }}">
                                    <p class="text-sm font-medium">{{ $slot['duration'] }}</p>
                                    <p class="text-lg font-semibold">{{ $slot['label'] }}</p>
                                    <p class="text-sm {{ $slot['isBooked'] ? 'text-rose-500' : 'text-slate-500' }}">
                                        {{ $slot['isBooked'] ? 'Booked' : $slot['price'] }}
                                    </p>

                                    @if ($isSelected)
                                        {{-- Icon cek ketika slot dipilih. --}}
                                        <span
                                            class="absolute top-2 right-2 inline-flex items-center justify-center size-7 rounded-full bg-indigo-600 text-white">✓</span>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- STEP D3: Form data diri customer dan tombol submit booking. --}}
                <form id="booking-form" wire:submit.prevent="submit"
                    class="bg-white rounded-3xl shadow-sm p-6 space-y-5">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-lg font-semibold text-slate-900">Isi Data Diri</p>
                            <p class="text-sm text-slate-500">Lengkapi data untuk melanjutkan proses booking.</p>
                        </div>
                        @if ($selectedSlotKey)
                            @php
                                $selectedSlot = collect($slots)->firstWhere('key', $selectedSlotKey); // Ambil detail slot terpilih.
                            @endphp
                            @if ($selectedSlot)
                                {{-- Ringkasan slot terpilih agar customer yakin sebelum submit. --}}
                                <div class="text-sm text-right text-slate-600">
                                    <p class="font-semibold text-slate-900">Slot Terpilih</p>
                                    <p>{{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('l, d F Y') }}</p>
                                    <p>{{ $selectedSlot['label'] }}</p>
                                    <p class="text-indigo-600 font-semibold">{{ $selectedSlot['price'] }}</p>
                                </div>
                            @endif
                        @endif
                    </div>

                    {{-- Alert ketika user belum memilih tanggal/slot. --}}
                    @error('selectedSlotKey')
                        <div class="rounded-xl bg-rose-50 border border-rose-200 text-rose-600 px-4 py-3 text-sm">
                            {{ $message }}
                        </div>
                    @enderror

                    {{-- Alert jika proses Midtrans gagal. --}}
                    @error('midtrans')
                        <div class="rounded-xl bg-rose-50 border border-rose-200 text-rose-600 px-4 py-3 text-sm">
                            {{ $message }}
                        </div>
                    @enderror

                    {{-- Kolom input data customer. --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm text-slate-600 mb-1 block">Nama Lengkap</label>
                            <input type="text" placeholder="Nama Lengkap" wire:model.defer="customerName"
                                class="w-full rounded-xl border border-slate-200 px-4 py-3 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none">
                            @error('customerName')
                                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="text-sm text-slate-600 mb-1 block">Nomor Telepon</label>
                            <input type="text" placeholder="628xxxxxxxx" wire:model.defer="customerPhone"
                                class="w-full rounded-xl border border-slate-200 px-4 py-3 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none">
                            @error('customerPhone')
                                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="text-sm text-slate-600 mb-1 block">Email (Opsional)</label>
                            <input type="email" placeholder="nama@email.com" wire:model.defer="customerEmail"
                                class="w-full rounded-xl border border-slate-200 px-4 py-3 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none">
                            @error('customerEmail')
                                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-sm text-slate-600 mb-1 block">Catatan (Opsional)</label>
                            <textarea rows="3" placeholder="Sampaikan kebutuhan khusus di sini" wire:model.defer="customerNote"
                                class="w-full rounded-xl border border-slate-200 px-4 py-3 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none"></textarea>
                            @error('customerNote')
                                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Tombol submit booking dengan indikator loading dari Livewire. --}}
                    <button type="submit"
                        class="w-full md:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-indigo-600 text-white font-semibold hover:bg-indigo-700 transition disabled:opacity-60"
                        wire:loading.attr="disabled" wire:target="submit" @disabled(!$selectedSlotKey)>
                        <span wire:loading.remove wire:target="submit">Booking Sekarang</span>
                        <span wire:loading wire:target="submit" class="inline-flex items-center gap-2">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Memproses...
                        </span>
                    </button>
                </form>
            </div>
        </div>
    </section>
</div>
