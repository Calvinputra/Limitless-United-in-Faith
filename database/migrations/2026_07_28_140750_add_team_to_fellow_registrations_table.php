<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fellow_registrations', function (Blueprint $table) {
            $table->unsignedTinyInteger('team')->nullable()->after('bukti_tf_path');
            $table->index('team');
        });
    }

    public function down(): void
    {
        Schema::table('fellow_registrations', function (Blueprint $table) {
            $table->dropIndex(['team']);
            $table->dropColumn('team');
        });
    }
};
