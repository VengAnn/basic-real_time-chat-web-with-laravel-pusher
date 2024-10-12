<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('from_id'); // ID of the sender
            $table->unsignedBigInteger('to_id');   // ID of the receiver
            $table->text('body');                   // Message content
            $table->string('attachment')->nullable(); // Optional file attachment
            $table->boolean('seen')->default(false); // Read status
            $table->timestamps();                   // Created and updated timestamps

            // Foreign key constraints (optional)
            $table->foreign('from_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('to_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('chat_messages');
    }
};
