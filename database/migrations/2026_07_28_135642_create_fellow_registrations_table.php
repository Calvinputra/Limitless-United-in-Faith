<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fellow_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('gender', 20);
            $table->unsignedTinyInteger('umur');
            $table->string('whatsapp', 20);
            $table->string('gereja_lokal');
            $table->string('bukti_tf_path');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fellow_registrations');
    }
};
