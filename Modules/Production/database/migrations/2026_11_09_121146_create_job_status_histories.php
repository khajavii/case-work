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
        Schema::create('job_status_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('job_id')
                ->constrained('production_jobs')
                ->cascadeOnDelete();

            $table->foreignId('changed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->enum('from_status', [
                'cutting',
                'edge_banding',
                'assembly',
                'ready_for_installation',
                'installation',
                'completed',
            ]);

            $table->enum('to_status', [
                'cutting',
                'edge_banding',
                'assembly',
                'ready_for_installation',
                'installation',
                'completed',
            ]);

            $table->timestampTz('created_at')
                ->useCurrent();

            $table->index(
                ['job_id', 'created_at', 'id'],
                'job_status_histories_job_created_id_index'
            );

            $table->index('changed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_status_histories');
    }
};
