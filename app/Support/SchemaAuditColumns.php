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
        $table->string('CreatedBy', 32);
        $table->dateTime('CreatedDate');
        $table->string('LastUpdatedBy', 32);
        $table->dateTime('LastUpdatedDate');
    }
}
