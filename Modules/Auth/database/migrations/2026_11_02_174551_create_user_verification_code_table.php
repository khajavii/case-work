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
        Schema::create('user_verification_code', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('purpose')->comment('0=>login,1=>change_password');
            $table->string('identifier')->index();
            $table->string('code');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->dateTime('expires_at');
            $table->timestamps();

            $table->index(['identifier', 'purpose']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_verification_code');
    }
};
