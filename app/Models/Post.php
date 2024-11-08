<?php

namespace App\Models;

use App\Models\Traits\EaasTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory, EaasTrait;

    protected $fillable = ['title','content','tenant_id'];
    // List of columns that should be encrypted
    protected $encryptedColumns = ['title', 'content'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
