<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeIdColumnOnBrandRelatedTables extends Migration
{
    const TABLE_NAME = 'series';

    public function up()
    {
        if ('sqlite' === DB::connection()->getName()) {
            return;
        }

        Schema::table('brand_supplier', function(Blueprint $table) {
            $table->dropForeign(['brand_id']);
        });
        Schema::table('brand_supplier', function(Blueprint $table) {
            $table->unsignedBigInteger('brand_id')->change();
        });
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->unsignedBigInteger('brand_id')->after('id')->change();
        });
        Schema::table('brands', function(Blueprint $table) {
            $table->unsignedBigInteger('id')->autoIncrement()->change();
        });
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->foreign('brand_id')->references('id')->on('brands')->cascadeOnUpdate()->cascadeOnDelete();
        });
        Schema::table('brand_supplier', function(Blueprint $table) {
            $table->foreign('brand_id')->references('id')->on('brands')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    public function down()
    {
        Schema::table('brand_supplier', function(Blueprint $table) {
            $table->dropForeign(['brand_id']);
        });
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropForeign(['brand_id']);
        });
        Schema::table('brands', function(Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement()->change();
        });
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->unsignedInteger('brand_id')->after('id')->change();
        });
        Schema::table('brand_supplier', function(Blueprint $table) {
            $table->unsignedInteger('brand_id')->change();
        });
        Schema::table('brand_supplier', function(Blueprint $table) {
            $table->foreign('brand_id')->references('id')->on('brands')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }
}
