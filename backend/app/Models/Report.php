<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;

class Report extends Model
{
    use HasFactory;

    public $timestamps = false; // ✅ Thêm dòng này để Laravel không ghi `updated_at`

    protected $fillable = [
        'reporter_id',
        'target_id',
        'type',
        'reason',
        'status',
    ];

    public function review()
    {
        return $this->belongsTo(Review::class, 'target_id');
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function postComment()
    {
        return $this->belongsTo(PostComment::class, 'target_id');
    }
}
