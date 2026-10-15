<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->string('nama_usaha');
            $table->string('deskripsi_singkat')->nullable();
            $table->string('alamat')->nullable();
            $table->string('telepon')->nullable();
            $table->string('email')->nullable();

            // Jenis booking & durasi waktu
            $table->enum('jenis_booking', ['per_jam', 'per_12_jam', 'per_hari', 'custom'])
                ->default('per_jam');
            $table->unsignedInteger('durasi_booking')->default(60)
                ->comment('durasi dalam menit jika custom');

            // Jam operasional
            $table->time('jam_buka')->default('08:00');
            $table->time('jam_tutup')->default('22:00');

            // Logo, foto, dsb
            $table->string('logo')->nullable();

            // Pengaturan tambahan
            $table->boolean('is_booking_open')->default(true)
                ->comment('apakah menerima booking');
            $table->text('catatan_booking')->nullable()
                ->comment('misal: “harap datang 15 menit sebelum jam main”');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};
