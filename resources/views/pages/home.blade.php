@extends('layouts.home')

@section('content')
    <section class="py-12 md:py-24">
        <div class="max-w-7xl mx-auto px-4">
            <p class="mb-3 text-center text-sm text-indigo-700 font-bold">
                Welcome to Booking App
            </p>
            <h1 class="w-full md:w-7/12 mx-auto mb-5 text-center text-4xl md:text-5xl font-extrabold">
                Temukan dan pesan lapangan terbaik untuk aktivitas Anda
            </h1>
            <p class="w-full md:w-7/12 mx-auto mb-12 text-center text-gray-600">
                Lorem ipsum dolor sit amet consectetur adipisicing elit. Voluptates ea cumque nostrum tenetur accusantium,
                provident odit nisi quaerat quibusdam, non quod molestias.
            </p>
            <a href="#"
                class="w-max mx-auto block py-3 px-4 rounded-xl bg-indigo-700 text-white hover:bg-indigo-900 transition">
                Lihat Lapangan
            </a>
        </div>
    </section>
    <section class="py-12 md:py-24 bg-gray-100">
        <div class="max-w-7xl mx-auto px-4">
            <p class="mb-2 text-center text-sm text-indigo-700 font-bold">Temukan Lapangan</p>
            <h2 class="text-center text-2xl md:text-4xl font-semibold mb-12">Pilih Lapangan</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach ($items as $item)
                    <div class="bg-white border border-gray-200 rounded-xl p-1 hover:border-indigo-700">
                        @if ($item->image)
                            <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}"
                                class="rounded-xl w-full h-48 object-cover">
                        @else
                            <div class="rounded-xl w-full h-48 bg-gray-100 flex items-center justify-center text-gray-400">
                                Tidak ada gambar
                            </div>
                        @endif

                        <div class="p-4">
                            <h3 class="text-lg font-semibold mb-2">{{ $item->name }}</h3>
                            <p class="text-gray-500 text-sm mb-4">
                                Rp. {{ number_format($item->price) }}
                            </p>
                            <a href="{{ route('items.show', $item) }}"
                                class="inline-block py-2 px-4 rounded-xl bg-indigo-700 text-white hover:bg-indigo-900 transition">
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection
