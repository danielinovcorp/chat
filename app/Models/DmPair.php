<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DmPair extends Model
{
	protected $fillable = ['user_a_id','user_b_id','dm_key'];

	public function a() { return $this->belongsTo(User::class, 'user_a_id'); }
	public function b() { return $this->belongsTo(User::class, 'user_b_id'); }

	public static function keyFor(int $a, int $b): string {
		[$x,$y] = $a < $b ? [$a,$b] : [$b,$a];
		return hash('sha256', "dm:{$x}:{$y}");
	}
}
