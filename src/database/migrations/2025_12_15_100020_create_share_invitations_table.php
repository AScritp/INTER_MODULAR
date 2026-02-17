<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('share_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inviter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('invitee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('invitee_email')->nullable();
            $table->foreignId('workspace_id')->nullable()->constrained('workspaces')->cascadeOnDelete();
            $table->enum('resource_type', ['document', 'event'])->nullable();
            $table->unsignedBigInteger('resource_id')->nullable();
            $table->json('permissions');
            $table->enum('status', ['pending', 'accepted', 'rejected', 'expired'])->default('pending');
            $table->string('token')->unique();
            $table->timestamps();
            $table->index(['resource_type', 'resource_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('share_invitations');
    }
};
