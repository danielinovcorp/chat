<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class SalaInvite extends Model
{
    protected $fillable = ['sala_id','creator_id','token','max_uses','expires_at','disabled_at'];
    protected $casts = [
        'expires_at' => 'datetime',
        'disabled_at'=> 'datetime',
    ];

    public function sala()    { return $this->belongsTo(Sala::class); }
    public function creator() { return $this->belongsTo(User::class, 'creator_id'); }

    public function isActive(): bool
    {
        return is_null($this->disabled_at)
            && (is_null($this->expires_at) || $this->expires_at->isFuture())
            && (is_null($this->max_uses)   || $this->used_count < $this->max_uses);
    }
}
