<?php

namespace App\Models;

use App\Models\Concerns\HasAuditFields;
use Illuminate\Database\Eloquent\Model;

abstract class BaseModel extends Model
{
    use HasAuditFields;

    protected function casts(): array
    {
        return [
            'CreatedDate' => 'datetime',
            'LastUpdatedDate' => 'datetime',
        ];
    }
}
