<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workspace_user', function (Blueprint $table) {
            $table->json('permissions')->nullable()->after('role');
            $table->boolean('inherit_existing_documents')->default(false)->after('permissions');
            $table->boolean('inherit_existing_events')->default(false)->after('inherit_existing_documents');
            $table->boolean('apply_to_future_only')->default(false)->after('inherit_existing_events');
        });
    }

    public function down(): void
    {
        Schema::table('workspace_user', function (Blueprint $table) {
            $table->dropColumn(['permissions', 'inherit_existing_documents', 'inherit_existing_events', 'apply_to_future_only']);
        });
    }
};
