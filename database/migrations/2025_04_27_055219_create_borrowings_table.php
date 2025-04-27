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
        Schema::create('borrowings', function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->constrained("users")->cascadeOnDelete();
            $table->foreignId("item_id")->constrained("items")->cascadeOnDelete();
            $table->integer("quantity");
            $table->enum("status", ["pending","approved","rejected","returned"])->default("pending");
            $table->dateTime("approved_at")->nullable();
            $table->string("approved_by")->nullable();
            $table->dateTime("due")->default(\Illuminate\Support\Carbon::now()->addDay());
            $table->foreign("approved_by")->references("username")->on("users");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('borrowings');
    }
};
