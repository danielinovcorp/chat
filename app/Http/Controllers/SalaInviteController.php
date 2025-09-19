<?php

namespace App\Http\Controllers;

use App\Models\Sala;
use App\Models\SalaInvite;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class SalaInviteController extends Controller
{
    // cria/genera um link
    public function store(Request $r, Sala $sala)
    {
        // opcional: $this->authorize('invite', $sala);
        $data = $r->validate([
            'expires_in' => ['nullable','in:24h,7d,30d,never'],
            'max_uses'   => ['nullable','integer','min:1'],
        ]);

        $expires = match($data['expires_in'] ?? '24h') {
            '24h'  => now()->addDay(),
            '7d'   => now()->addDays(7),
            '30d'  => now()->addDays(30),
            'never'=> null,
        };

        $invite = SalaInvite::create([
            'sala_id'   => $sala->id,
            'creator_id'=> auth()->id(),
            'token'     => Str::random(40),
            'max_uses'  => $data['max_uses'] ?? null,
            'expires_at'=> $expires,
        ]);

        return back()->with('invite_url', route('salas.invites.accept', $invite->token));
    }

    // desativar (revogar) link
    public function disable(SalaInvite $invite)
    {
        // opcional: $this->authorize('invite', $invite->sala);
        $invite->update(['disabled_at'=>now()]);
        return back()->with('status','Convite desativado.');
    }

    // aceitar/entrar pela URL
    public function accept(string $token, Request $r)
    {
        $invite = SalaInvite::where('token', $token)->firstOrFail();

        // checagens
        abort_unless($invite->isActive(), 410); // 410 Gone

        // exigir login
        if (!auth()->check()) {
            session()->put('url.intended', route('salas.invites.accept', $token));
            return redirect()->route('login');
        }

        $user = auth()->user();

        // já é membro?
        if ($invite->sala->membros()->whereKey($user->id)->exists()) {
            return redirect()->route('chat.index', ['sala'=>$invite->sala_id])
                ->with('info','Já és membro desta sala.');
        }

        // transação para contagem + entrada
        DB::transaction(function () use ($invite, $user) {
            // re-check limites dentro da transação
            $inv = SalaInvite::lockForUpdate()->find($invite->id);
            if (!$inv->isActive()) { abort(410); }

            $inv->sala->membros()->syncWithoutDetaching([$user->id]);
            $inv->increment('used_count');

            if (!is_null($inv->max_uses) && $inv->used_count >= $inv->max_uses) {
                $inv->disabled_at = now();
                $inv->save();
            }
        });

        return redirect()->route('chat.index', ['sala'=>$invite->sala_id])
            ->with('success','Entraste na sala!');
    }
}
