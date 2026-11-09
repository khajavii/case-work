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
        Schema::create('production_jobs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('assigned_installer_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('title', 200);
            $table->text('description')->nullable();

            $table->enum('status', [
                'cutting',
                'edge_banding',
                'assembly',
                'ready_for_installation',
                'installation',
                'completed',
            ])->default('cutting');

            $table->timestampsTz();

            $table->index(
                ['order_id', 'id'],
                'production_jobs_order_id_id_index'
            );

            $table->index(
                ['assigned_installer_id', 'status', 'id'],
                'production_jobs_installer_status_id_index'
            );

            $table->index(
                ['status', 'id'],
                'production_jobs_status_id_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_jobs');
    }
};
