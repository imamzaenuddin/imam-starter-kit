<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatAiRiwayat extends Model
{
    protected $table = 't_chat_ai_riwayat';

    protected $fillable = [
        'user_id',
        'pertanyaan',
        'jawaban',
        'sumber',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
