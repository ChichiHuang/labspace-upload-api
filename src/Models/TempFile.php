<?php

namespace Labspace\UploadApi\Models;

use Illuminate\Database\Eloquent\Model;

class TempFile extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'temp_files';
    protected $guarded = ['updated_at','created_at'];
    protected $fillable = [
        'filepath'
    ];
    public $timestamps = false;



}
