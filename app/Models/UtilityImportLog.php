<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UtilityImportLog extends Model
{
    use HasFactory;

    protected $table = 'utility_import_logs';

    protected $primaryKey = 'utility_import_log_id';

    protected $fillable = [
        'period',
        'utility_type',
        'file_name',
        'total_rows',
        'success_rows',
        'error_rows',
        'errors',
        'imported_by',
        'imported_at',
    ];
}
