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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('owner_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('customer_name', 150);
            $table->text('address');

            $table->enum('status', [
                'pending',
                'in_progress',
                'completed',
            ])->default('pending');

            $table->timestampsTz();

            $table->index(
                ['owner_id', 'status', 'id'],
                'orders_owner_status_id_index'
            );

            $table->index(
                ['status', 'id'],
                'orders_status_id_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
