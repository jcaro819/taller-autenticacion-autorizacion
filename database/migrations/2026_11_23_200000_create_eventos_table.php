<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eventos', function (Blueprint $table) {
            $table->id();
            // Organizador dueño del evento (FK a users). Si se borra el usuario, se borran sus eventos.
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('titulo', 100);
            $table->text('descripcion')->nullable();
            $table->unsignedInteger('cupo_maximo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eventos');
    }
};
