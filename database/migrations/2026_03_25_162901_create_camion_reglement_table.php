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
        Schema::create('camion_reglements', function (Blueprint $table) {
            $table->id();
            $table->foreignId("camion_id")
                ->nullable()
                ->constrained("camions", "id")
                ->onUpdate('CASCADE')
                ->onDelete("set null");
            $table->foreignId("reglement_id")
                ->nullable()
                ->constrained("reglement_locations", "id")
                ->onUpdate('CASCADE')
                ->onDelete("set null");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('camion_reglement');
    }
};
