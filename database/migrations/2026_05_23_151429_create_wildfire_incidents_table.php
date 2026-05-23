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
        Schema::create('wildfire_incidents', function (Blueprint $table) {
            $table->id();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('burned_area', 12, 2);
            $table->date('fire_date');
            $table->enum('severity', ['Low', 'Medium', 'High', 'Extreme']);
            $table->string('country');
            $table->unsignedTinyInteger('month');
            $table->year('year');
            $table->unsignedInteger('duration')->comment('Duration in hours');
            $table->string('name');
            $table->timestamps();

            // Indices
            $table->index('latitude');
            $table->index('longitude');
            $table->index('month');
            $table->index('year');
            $table->index('severity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wildfire_incidents');
    }
};
