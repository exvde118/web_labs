<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('albums', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained()->restrictOnDelete();
            $table->index('user_id');
        });

        if (DB::table('albums')->whereNull('user_id')->exists()) {
            $ownerId = DB::table('users')->insertGetId([
                'name' => 'Архив ЛР №3',
                'username' => 'legacy-'.Str::lower(Str::random(12)),
                'email' => 'archive-'.Str::uuid().'@example.invalid',
                'password' => Hash::make(Str::random(48)),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('albums')->whereNull('user_id')->update(['user_id' => $ownerId]);
        }

        Schema::table('albums', function (Blueprint $table) {
            $table->foreignId('user_id')->change();
        });
    }

    public function down(): void
    {
        Schema::table('albums', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
