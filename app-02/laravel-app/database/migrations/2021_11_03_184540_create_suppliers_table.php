<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSuppliersTable extends Migration
{
    const TABLE_NAME = 'suppliers';

    public function up()
    {
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->bigInteger('airtable_id')->unique()->nullable();
            $table->string('name');
            $table->string('branch')->nullable();
            $table->string('address')->nullable();
            $table->string('address_2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('country')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('phone')->nullable();
            $table->string('fax')->nullable();
            $table->string('prokeep_phone')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('contact_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_secondary_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_job')->nullable();
            $table->string('url')->nullable();
            $table->text('about')->nullable();
            $table->string('image')->nullable();
            $table->boolean('offers_delivery')->default(false);
            $table->string('monday_hours')->nullable();
            $table->string('tuesday_hours')->nullable();
            $table->string('wednesday_hours')->nullable();
            $table->string('thursday_hours')->nullable();
            $table->string('friday_hours')->nullable();
            $table->string('saturday_hours')->nullable();
            $table->string('sunday_hours')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('welcome_displayed_at')->nullable();
            $table->timestamps();

            $table->unique(['name', 'branch']);
        });
    }

    public function down()
    {
        Schema::drop(self::TABLE_NAME);
    }
}
