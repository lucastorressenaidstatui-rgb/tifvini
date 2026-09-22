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
        if (Schema::hasTable('frequencias')) {
            return;
        }

        Schema::create('frequencias', function (Blueprint $table) {
            $table->id();
            $table->string('nome_aluno');
            $table->string('matricula', 50);
            $table->string('turma', 100);
            $table->enum('tipo_registro', ['entrada', 'saida']);
            $table->date('data_registro');
            $table->time('hora_registro');
            $table->text('observacao')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // A tabela pode existir antes desta migração; não removê-la no rollback.
    }
};
