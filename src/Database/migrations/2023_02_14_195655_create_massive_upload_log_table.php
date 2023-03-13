<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMassiveUploadLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('massive_upload_log')) {
            Schema::create('massive_upload_log', function (Blueprint $table) {
                $table->id();
                $table->string('action', 100);
                $table->string('friendly_name', 250);
                $table->string('type', 10);
                $table->json('entities');
                $table->string('file_name', 250);
                $table->enum('upload_status', ['in_progress', 'complete', 'incomplete']);
                $table->integer('user_id');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('massive_upload_log');
    }
}
