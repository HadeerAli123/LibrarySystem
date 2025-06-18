<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
    {
        Schema::table('books', function (Blueprint $table) {
$table->foreignId('added_by_user_id')->nullable()->constrained('users')->onDelete('set null')->after('available');
        });
    }

    /**
     * Reverse the migrations.
     */
   public function down()
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropForeign(['added_by_user_id']);
            $table->dropColumn('added_by_user_id');
        });
    }
};
