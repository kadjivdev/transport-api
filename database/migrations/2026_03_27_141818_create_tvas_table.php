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
        Schema::create('tvas', function (Blueprint $table) {
            $table->id();
            $table->string("reference")->nullable();
            $table->foreignId("location_id")
                ->nullable()
                ->constrained("locations", "id")
                ->onUpdate('CASCADE')
                ->onDelete("set null");
            $table->decimal("montant", 15, 2);
            $table->text("preuve")->nullable();
            $table->text("commentaire")->nullable();
            $table->foreignId("created_by")
                ->nullable()
                ->constrained("users", "id")
                ->onUpdate('CASCADE')
                ->onDelete("set null");
            $table->foreignId("validated_by")
                ->nullable()
                ->constrained("users", "id")
                ->onUpdate('CASCADE')
                ->onDelete("set null");
            $table->date("validated_at")->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tvas');
    }
};
