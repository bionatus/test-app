<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class DropQuotesTable extends Migration
{
    const TABLE_NAME       = 'quotes';
    const TABLE_NAME_ITEMS = 'quote_items';

    public function up()
    {
        Schema::dropIfExists(self::TABLE_NAME_ITEMS);
        Schema::dropIfExists(self::TABLE_NAME);
    }

    public function down()
    {
        //
    }
}
