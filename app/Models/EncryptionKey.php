<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EncryptionKey extends Model
{
    protected $table = "encryption_keys";
    protected $fillable = ['version','key'];
}
