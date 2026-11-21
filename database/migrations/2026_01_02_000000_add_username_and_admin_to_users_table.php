<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 30)->nullable();
            $table->boolean('is_admin')->default(false);
        });

        DB::table('users')->select('id')->chunkById(500, function ($users) {
            foreach ($users as $user) {
                DB::table('users')->where('id', $user->id)->update(['username' => 'user-'.$user->id]);
            }
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 30)->change();
            $table->unique('username');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn(['username', 'is_admin']);
        });
    }
};
