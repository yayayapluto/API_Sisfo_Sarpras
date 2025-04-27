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
        Schema::create('log_activities', function (Blueprint $table) {
            $table->id();
            $table->string("performed_by")->default("unknown");
            $table->string("entity");
            $table->integer("entity_id")->nullable();
            $table->enum("action", ["retrieve","create","update","delete"]);
            $table->json("old_value")->nullable();
            $table->json("new_value")->nullable();
            $table->foreign("performed_by")->references("username")->on("users");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_activities');
    }
};
