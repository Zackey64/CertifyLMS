<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 受講生メモの管理
 */
class EnrollmentNote extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'user_id',
        'body',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }
}
