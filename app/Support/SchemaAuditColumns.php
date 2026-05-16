<?php

namespace App\Support;

use Illuminate\Database\Schema\Blueprint;

class SchemaAuditColumns
{
    public static function add(Blueprint $table): void
    {
        $table->string('CompanyCode', 20);
        $table->tinyInteger('Status')->default(1);
        $table->tinyInteger('IsDeleted')->default(0);
        $table->string('CreatedBy');
        $table->dateTime('CreatedDate');
        $table->string('LastUpdatedBy');
        $table->dateTime('LastUpdatedDate');
    }
}
