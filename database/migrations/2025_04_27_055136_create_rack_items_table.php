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
        Schema::create('rack_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId("rack_id")->constrained("racks")->cascadeOnDelete();
            $table->foreignId("item_id")->constrained("items")->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rack_items');
    }
};
