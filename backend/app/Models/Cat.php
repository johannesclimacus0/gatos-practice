<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cat extends Model
{
    protected $fillable =[
        'user_id',
        'name',
        'lang',
    ];
    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
    ];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}