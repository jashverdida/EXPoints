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
        Schema::create('user_info', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('username')->unique();
            $table->string('profile_picture')->nullable();
            $table->integer('exp_points')->default(0);
            $table->boolean('is_banned')->default(false);
            $table->string('role')->default('user'); // user, admin, moderator
            $table->timestamps();
            
            $table->index('username');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_info');
    }
};
