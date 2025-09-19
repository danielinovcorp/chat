<x-app-layout>
	<x-slot name="header">
		<h2 class="font-semibold text-xl">Chat</h2>
	</x-slot>

	<div class="py-6">
		<div class="max-w-7xl mx-auto grid grid-cols-12 gap-4">
			{{-- 1) ESQUERDA: Salas + Membros --}}
			<aside class="col-span-12 md:col-span-3 bg-white shadow rounded p-3 md:sticky md:top-20 md:h-[calc(100vh-8rem)] md:overflow-y-auto">
				@can('create', \App\Models\Sala::class)
				<div class="mb-3">
					<a href="{{ route('salas.create') }}" class="inline-flex items-center gap-2 text-sm text-white bg-green-600 px-3 py-1 rounded">
						+ Nova Sala
					</a>
				</div>
				@endcan

				{{-- Salas (chips) --}}
				<h3 class="font-semibold mb-2">Salas</h3>
				<ul class="space-y-2">
					@foreach($salas as $s)
					@php $isActive = $activeSala && $activeSala->id === $s->id; @endphp
					<li>
						<a
							href="{{ route('chat.index', ['sala' => $s->id]) }}"
							aria-current="{{ $isActive ? 'page' : 'false' }}"
							class="inline-flex w-fit items-center rounded-full select-none
                       border px-3 py-1.5 text-sm leading-5 normal-case transition-colors duration-150
                       focus:outline-none
                       {{ $isActive
                            ? 'border-blue-600 ring-2 ring-blue-500/30 text-blue-700 bg-blue-50 font-medium'
                            : 'border-base-300 text-base-content hover:border-blue-500 hover:ring-1 hover:ring-blue-400/30 hover:bg-blue-50/40' }}">
							<span class="truncate max-w-[180px]">{{ $s->nome }}</span>
						</a>
					</li>
					@endforeach
				</ul>

				{{-- Membros + Convidar (só quando estiver numa sala) --}}
				@if($activeSala)
				<div class="flex items-center justify-between mt-6 mb-2">
					<h3 class="font-semibold">Membros desta sala</h3>
					<button class="btn btn-xs btn-primary rounded-full"
						onclick="inviteModal_{{ $activeSala->id }}.showModal()">Convidar</button>
				</div>

				<ul class="space-y-2">
					@foreach($membrosSala as $m)
					@continue($m->id === auth()->id())
					@php
					$temFoto = !empty($m->avatar_path);
					$iniciais = collect(explode(' ', $m->name))->map(fn($p)=>mb_substr($p,0,1))->take(2)->implode('');
					@endphp
					<li class="flex items-center justify-between">
						<div class="flex items-center gap-2 min-w-0">
							@if($temFoto)
							<img src="{{ $m->avatar_url }}" class="h-7 w-7 rounded-full object-cover ring-1 ring-gray-200" alt="{{ $m->name }}">
							@else
							<div class="h-7 w-7 rounded-full bg-gray-200 ring-1 ring-gray-200 flex items-center justify-center text-[10px] font-semibold text-gray-700">{{ $iniciais }}</div>
							@endif
							<span class="truncate">{{ $m->name }}</span>
						</div>
						<a href="{{ route('dm.open',['user'=>$m->id]) }}" class="text-blue-600 hover:underline text-sm">DM</a>
					</li>
					@endforeach
				</ul>

				{{-- Modal de convite por link --}}
				<dialog id="inviteModal_{{ $activeSala->id }}" class="modal">
					<div class="modal-box">
						<h3 class="font-semibold mb-3">Convidar para "{{ $activeSala->nome }}"</h3>

						<form method="POST" action="{{ route('salas.invites.store', $activeSala) }}" class="space-y-4">
							@csrf
							<div class="grid grid-cols-2 gap-3">
								<label class="form-control">
									<span class="label-text mb-1">Validade</span>
									<select name="expires_in" class="select select-bordered">
										<option value="24h">24 horas</option>
										<option value="7d">7 dias</option>
										<option value="30d">30 dias</option>
										<option value="never">Nunca expira</option>
									</select>
								</label>
								<label class="form-control">
									<span class="label-text mb-1">Limite de usos</span>
									<input name="max_uses" type="number" min="1" placeholder="Ilimitado" class="input input-bordered" />
								</label>
							</div>
							<div class="modal-action">
								<button class="btn btn-primary">Gerar link</button>
								<button type="button" class="btn btn-ghost" onclick="inviteModal_{{ $activeSala->id }}.close()">Cancelar</button>
							</div>
						</form>

						@if (session('invite_url'))
						<div class="mt-4">
							<div class="text-sm mb-1">Link criado:</div>
							<div class="join w-full">
								<input id="inviteLink" class="input input-bordered join-item w-full" value="{{ session('invite_url') }}" readonly>
								<button class="btn join-item" onclick="navigator.clipboard.writeText(document.getElementById('inviteLink').value)">Copiar</button>
							</div>
						</div>
						@endif
					</div>
					<form method="dialog" class="modal-backdrop"><button>close</button></form>
				</dialog>

				{{-- reabre o modal se acabou de gerar um link --}}
				@if (session('invite_url'))
				<script>
					window.addEventListener('DOMContentLoaded', () => {
						try {
							inviteModal_ {
								{
									$activeSala -> id
								}
							}.showModal();
						} catch {}
					});
				</script>
				@endif
				@endif
			</aside>

			{{-- 2) CENTRO: Chat (Timeline) --}}
			<main class="col-span-12 md:col-span-6 bg-white shadow rounded p-4">
				@if($activeSala)
				<div class="mb-3 text-sm text-gray-600">Sala: <strong>{{ $activeSala->nome }}</strong></div>
				@elseif($activeDmKey)
				<div class="mb-3 text-sm text-gray-600">DM: <strong>{{ Str::limit($activeDmKey, 12) }}</strong></div>
				@else
				<p class="text-gray-600">Escolhe uma sala ou DM à esquerda.</p>
				@endif

				<div class="border rounded p-3 h-[55vh] overflow-y-auto mb-3" id="messages">
					@forelse($mensagens as $m)
					@php $souEu = $m->user_id === auth()->id(); @endphp

					<div class="mb-3 flex items-start gap-3 {{ $souEu ? 'justify-end' : '' }}">
						{{-- avatar do outro usuário --}}
						@unless($souEu)
						@php
						$temFoto = !empty($m->autor->avatar_path);
						$iniciais = collect(explode(' ', $m->autor->name))->map(fn($p)=>mb_substr($p,0,1))->take(2)->implode('');
						@endphp
						@if($temFoto)
						<img src="{{ $m->autor->avatar_url }}" alt="{{ $m->autor->name }} avatar"
							class="h-9 w-9 rounded-full object-cover ring-2 ring-white shadow-sm" loading="lazy">
						@else
						<div class="h-9 w-9 rounded-full ring-2 ring-white shadow-sm bg-gray-200
                        flex items-center justify-center text-xs font-semibold text-gray-700">{{ $iniciais }}</div>
						@endif
						@endunless

						{{-- BALÃO COMPACTO + TRIM DO CONTEÚDO --}}
						@php
						// 1) normaliza/trim (remove espaços/quebras de linha nas bordas)
						$conteudo = trim((string) $m->conteudo);

						// 2) escapa e linkifica URLs
						$text = e($conteudo);
						$pattern = '~(?:(?:https?|ftp)://|www\.)[^\s<]+~i';
							$text=preg_replace_callback($pattern, function($match) {
							$url=$match[0];
							$href=preg_match('~^https?://~i', $url) ? $url : 'http://' . $url;
							return '<a href="' .$href.'" target="_blank" rel="noopener noreferrer" class="underline text-blue-600 break-all hover:text-blue-700">'.$url.'</a>';
							}, $text);
							@endphp

							<div class="{{ $souEu ? 'bg-emerald-50' : 'bg-gray-100' }}
                    inline-block w-max max-w-[70%] rounded-2xl px-3 py-2 shadow-sm">
								<div class="text-[12px] leading-none mb-1">
									<span class="font-semibold {{ $souEu ? 'text-emerald-700' : 'text-gray-800' }}">{{ $m->autor->name }}</span>
									<span class="text-gray-500 ml-2">{{ $m->created_at->format('H:i') }}</span>
								</div>
								<div class="text-[15px] leading-snug text-gray-900 whitespace-pre-line break-words [overflow-wrap:anywhere]">
									{!! $text !!}
								</div>
							</div>

							{{-- avatar do próprio à direita --}}
							@if($souEu)
							@php
							$meTemFoto = !empty(auth()->user()->avatar_path);
							$meIni = collect(explode(' ', auth()->user()->name))->map(fn($p)=>mb_substr($p,0,1))->take(2)->implode('');
							@endphp
							@if($meTemFoto)
							<img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }} avatar"
								class="h-9 w-9 rounded-full object-cover ring-2 ring-white shadow-sm" loading="lazy">
							@else
							<div class="h-9 w-9 rounded-full ring-2 ring-white shadow-sm bg-gray-200
                        flex items-center justify-center text-xs font-semibold text-gray-700">{{ $meIni }}</div>
							@endif
							@endif
					</div>
					@empty
					<p class="text-gray-500">Sem mensagens.</p>
					@endforelse
				</div>

				@if($activeSala)
				<form method="POST" action="{{ route('mensagens.sala.store', $activeSala) }}" class="flex gap-2">
					@csrf
					<input name="conteudo" class="flex-1 border rounded px-3 py-2" placeholder="Escreve aqui..." required>
					<button class="px-4 py-2 bg-blue-600 text-white rounded">Enviar</button>
				</form>
				@elseif($activeDmKey)
				@php
				$pair = \App\Models\DmPair::where('dm_key',$activeDmKey)->first();
				$otherId = $pair?->user_a_id === auth()->id() ? $pair?->user_b_id : $pair?->user_a_id;
				@endphp
				@if($otherId)
				<form method="POST" action="{{ route('mensagens.dm.store', $otherId) }}" class="flex gap-2">
					@csrf
					<input name="conteudo" class="flex-1 border rounded px-3 py-2" placeholder="Escreve aqui..." required>
					<button class="px-4 py-2 bg-blue-600 text-white rounded">Enviar</button>
				</form>
				@endif
				@endif
			</main>

			{{-- 3) DIREITA: DMs --}}
			<aside class="col-span-12 md:col-span-3 bg-white shadow rounded p-3 md:sticky md:top-20 md:h-[calc(100vh-8rem)] md:overflow-y-auto">
				<h3 class="font-semibold mb-2">DMs</h3>
				<ul class="space-y-2">
					@foreach($dmPairs as $p)
					@php
					$other = $dmUsers[$p->dm_key] ?? null;
					$isOn = $activeDmKey === $p->dm_key;
					$unread = $dmUnread[$p->dm_key] ?? 0; // se não vier, fica 0
					@endphp
					<li>
						<a href="{{ route('chat.index', ['dm'=>$p->dm_key]) }}"
							class="inline-flex w-full items-center gap-2 rounded-full border px-3 py-1.5 text-sm leading-5
                        transition-colors focus:outline-none
                        {{ $isOn
                            ? 'border-blue-600 ring-2 ring-blue-500/30 bg-blue-50 font-medium'
                            : 'border-base-300 hover:border-blue-500 hover:ring-1 hover:ring-blue-400/30 hover:bg-blue-50/40' }}">
							@if($other?->avatar_url)
							<img src="{{ $other->avatar_url }}" class="h-6 w-6 rounded-full object-cover ring-1 ring-gray-200" alt="{{ $other?->name }}">
							@else
							<div class="h-6 w-6 rounded-full bg-gray-200 ring-1 ring-gray-200 grid place-items-center text-[10px] font-semibold text-gray-700">
								{{ Str::of($other?->name ?? 'U')->explode(' ')->map(fn($p)=>mb_substr($p,0,1))->take(2)->implode('') }}
							</div>
							@endif
							<span class="truncate">{{ $other?->name ?? 'User' }}</span>
							@if($unread > 0)
							<span class="ml-auto inline-flex items-center justify-center rounded-full bg-blue-600 text-white text-[11px] px-2 h-5">{{ $unread }}</span>
							@endif
						</a>
					</li>
					@endforeach
				</ul>
			</aside>
		</div>
	</div>

	{{-- Script: auto-scroll + Echo + envio otimista --}}
	<script type="module">
		const messagesEl = document.getElementById('messages');
		const myId = @json(auth() -> id());
		const myName = @json(auth() -> user() -> name);
		const SCROLL_EPS = 32;
		let stickBottom = true;

		const isNearBottom = (el) => (el.scrollHeight - (el.scrollTop + el.clientHeight)) <= SCROLL_EPS;
		const scrollToBottom = (el) => {
			el.scrollTop = el.scrollHeight;
		};

		if (messagesEl) {
			messagesEl.addEventListener('scroll', () => {
				stickBottom = isNearBottom(messagesEl);
			});
			requestAnimationFrame(() => scrollToBottom(messagesEl));
			setTimeout(() => {
				if (stickBottom) scrollToBottom(messagesEl);
			}, 120);
			messagesEl.querySelectorAll('img').forEach(img => {
				img.addEventListener('load', () => {
					if (stickBottom) scrollToBottom(messagesEl);
				}, {
					once: true
				});
			});
		}

		function escapeHtml(s = '') {
			return String(s).replace(/[&<>"']/g, c => ({
				'&': '&amp;',
				'<': '&lt;',
				'>': '&gt;',
				'"': '&quot;',
				"'": '&#039;'
			} [c]));
		}

		function linkify(text = '') {
			const esc = escapeHtml(String(text).trim());
			return esc.replace(/((https?:\/\/|www\.)[^\s<]+)/gi, (m) => {
				const href = m.toLowerCase().startsWith('http') ? m : `http://${m}`;
				return `<a href="${href}" target="_blank" rel="noopener noreferrer" class="underline text-blue-600 break-all hover:text-blue-700">${m}</a>`;
			});
		}

		function initials(name = 'U') {
			return name.split(' ').filter(Boolean).map(p => p[0]).slice(0, 2).join('').toUpperCase();
		}

		function makeAvatar(name) {
			const el = document.createElement('div');
			el.className = 'h-9 w-9 rounded-full ring-2 ring-white shadow-sm bg-gray-200 flex items-center justify-center text-xs font-semibold text-gray-700';
			el.textContent = initials(name);
			return el;
		}

		// Balão no mesmo estilo do Blade (compacto)
		function appendMessage(m) {
			const isMe = m?.user?.id === myId;
			const wrap = document.createElement('div');
			wrap.className = `mb-3 flex items-start gap-3 ${isMe ? 'justify-end' : ''}`;

			if (!isMe) wrap.appendChild(makeAvatar(m?.user?.nome ?? 'User'));

			const t = new Date(m?.created_at ?? Date.now());
			const bubble = document.createElement('div');
			bubble.className = `${isMe ? 'bg-emerald-50' : 'bg-gray-100'} inline-block rounded-2xl px-3 py-2 max-w-[70%] shadow-sm`;
			bubble.innerHTML = `
				<div class="text-[12px] leading-none mb-1">
					<span class="font-semibold ${isMe ? 'text-emerald-700' : 'text-gray-800'}">${escapeHtml(m?.user?.nome ?? 'User')}</span>
					<span class="text-gray-500 ml-2">${t.toLocaleTimeString()}</span>
				</div>
				<div class="text-[15px] leading-snug text-gray-900 whitespace-pre-line break-words [overflow-wrap:anywhere]">
					${linkify(m?.conteudo ?? '')}
				</div>
				`;
			wrap.appendChild(bubble);

			if (isMe) wrap.appendChild(makeAvatar(myName));

			messagesEl.appendChild(wrap);
			if (stickBottom || isNearBottom(messagesEl)) scrollToBottom(messagesEl);
		}

		// ---- Echo ----
		function currentChannelName() {
			@if($activeSala)
			return 'sala.{{ $activeSala->id }}';
			@elseif($activeDmKey)
			return 'dm.{{ $activeDmKey }}';
			@else
			return null;
			@endif
		}

		function subscribeNow() {
			const name = currentChannelName();
			if (!name || !window.Echo) return;

			if (window.__CHAT_CHANNEL && window.__CHAT_CHANNEL !== name) {
				try {
					window.Echo.leave(window.__CHAT_CHANNEL);
				} catch {}
				window.__CHAT_CHANNEL = null;
			}
			if (window.__CHAT_CHANNEL === name) return;

			const onMessage = (e) => {
				if (e?.user?.id === myId) return;
				appendMessage(e);
			};

			if (name.startsWith('sala.')) {
				window.Echo.join(name).listen('.message.sent', onMessage);
			} else {
				window.Echo.private(name).listen('.message.sent', onMessage);
			}
			window.__CHAT_CHANNEL = name;
			if (messagesEl) requestAnimationFrame(() => scrollToBottom(messagesEl));
		}
		if (window.Echo) subscribeNow();
		else document.addEventListener('echo:ready', () => subscribeNow(), {
			once: true
		});

		// envio otimista
		const form = document.querySelector('form[action]');
		if (form && !window.__CHAT_FORM_BOUND__) {
			window.__CHAT_FORM_BOUND__ = true;
			form.addEventListener('submit', async (e) => {
				e.preventDefault();
				const input = form.querySelector('input[name="conteudo"]');
				const url = form.getAttribute('action');
				const text = (input.value || '').trim();
				if (!text) return;

				appendMessage({
					user: {
						id: myId,
						nome: myName
					},
					conteudo: text,
					created_at: new Date().toISOString(),
				});

				try {
					await window.axios.post(url, {
						conteudo: text
					}, {
						headers: {
							Accept: 'application/json'
						}
					});
				} catch (err) {
					console.error('Falha ao enviar', err);
				} finally {
					input.value = '';
					input.focus();
				}
			});
		}
	</script>

</x-app-layout>