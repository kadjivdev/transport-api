<?php

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use function Symfony\Component\Clock\now;

return new class extends Migration
{
    use SoftDeletes;
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string("reference")->nullable()->unique();
            $table->foreignId("client_id")
                ->nullable()
                ->constrained("clients")
                ->onUpdate("CASCADE")
                ->onDelete("set null");
            $table->foreignId("location_type_id")
                ->nullable()
                ->constrained("location_types")
                ->onUpdate("CASCADE")
                ->onDelete("set null");
            // $table->decimal("type_location_price", 15, 2);
            $table->decimal("montant_total", 15, 2)->nullable();
            $table->date("date_location")->useCurrent();
            $table->text("contrat")->nullable();
            $table->text("commentaire")->nullable();
            $table->foreignId("created_by")
                ->nullable()
                ->constrained("users")
                ->onUpdate("CASCADE")
                ->onDelete("set null");
            $table->foreignId("validated_by")
                ->nullable()
                ->constrained("users")
                ->onUpdate("CASCADE")
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
        Schema::table("locations", function (Blueprint $tbale) {
            $tbale->dropForeign(["client_id",]);
            $tbale->dropForeign(["location_type"]);
        });

        Schema::dropIfExists('locations');
    }
};
