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
        Schema::create('movimentacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuario')->cascadeOnDelete();
            $table->string('nome');
            $table->string('matricula', 50);
            $table->string('turma', 100);
            $table->dateTime('saida');
            $table->dateTime('retorno')->nullable();
            $table->text('observacao')->nullable();
            $table->timestamps();
            $table->index(['matricula', 'retorno', 'saida']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimentacoes');
    }
};
