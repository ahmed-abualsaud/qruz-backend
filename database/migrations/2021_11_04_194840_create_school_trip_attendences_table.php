<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSchoolTripAttendencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('school_trip_attendences', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('date');
            $table->unsignedBigInteger('trip_id');
            $table->unsignedBigInteger('student_id');
            $table->boolean('is_absent');
            $table->string('comment')->nullable();
            $table->timestamps();

            $table->index(['trip_id', 'date']);
            $table->index('student_id');

            $table->foreign('trip_id')->references('id')->on('school_trips')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('school_trip_attendences');
    }
}
