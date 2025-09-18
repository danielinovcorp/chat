<x-app-layout>
	<x-slot name="header">
		<h2 class="font-semibold text-xl">Chat</h2>
	</x-slot>

	<div class="py-6">
		<div class="max-w-7xl mx-auto grid grid-cols-12 gap-4">
			<!-- Sidebar -->
			<aside class="col-span-3 bg-white shadow rounded p-3">
				@can('create', \App\Models\Sala::class)
					<div class="mb-3">
					<a href="{{ route('salas.create') }}" class="inline-flex items-center gap-2 text-sm text-white bg-green-600 px-3 py-1 rounded">
						+ Nova Sala
					</a>
					</div>
				@endcan

				<h3 class="font-semibold mb-2">Salas</h3>
				<ul class="space-y-1">
					@foreach($salas as $s)
					<li>
						<a class="text-blue-600 hover:underline {{ $activeSala && $activeSala->id===$s->id ? 'font-bold' : '' }}"
						href="{{ route('chat.index',['sala'=>$s->id]) }}">{{ $s->nome }}</a>
					</li>
					@endforeach
				</ul>

				<h3 class="font-semibold mt-4 mb-2">DMs</h3>
				<ul class="space-y-1">
					@foreach($dmPairs as $p)
					@php
						$otherId = $p->user_a_id === auth()->id() ? $p->user_b_id : $p->user_a_id;
						$other = \App\Models\User::find($otherId);
					@endphp
					<li>
						<a class="text-blue-600 hover:underline {{ $activeDmKey===$p->dm_key ? 'font-bold' : '' }}"
						href="{{ route('chat.index',['dm'=>$p->dm_key]) }}">{{ $other?->name ?? 'User '.$otherId }}</a>
					</li>
					@endforeach
				</ul>

				{{-- SEÇÃO --}}
				@if($activeSala)
					<h3 class="font-semibold mt-4 mb-2">Membros desta sala</h3>
					<ul class="space-y-1">
					@foreach($membrosSala as $m)
						@continue($m->id === auth()->id())
						<li class="flex items-center justify-between">
						<span>{{ $m->name }}</span>
						<a href="{{ route('dm.open', ['user' => $m->id]) }}"
							class="text-blue-600 hover:underline text-sm">DM</a>
						</li>
					@endforeach
					</ul>
				@endif
			</aside>


			<!-- Timeline -->
			<main class="col-span-9 bg-white shadow rounded p-4">
				@if($activeSala)
				<div class="mb-3 text-sm text-gray-600">Sala: <strong>{{ $activeSala->nome }}</strong></div>
				@elseif($activeDmKey)
				<div class="mb-3 text-sm text-gray-600">DM: <strong>{{ Str::limit($activeDmKey, 12) }}</strong></div>
				@else
				<p class="text-gray-600">Escolhe uma sala ou DM à esquerda.</p>
				@endif

				<div class="border rounded p-3 h-[55vh] overflow-y-auto mb-3" id="messages">
					@forelse($mensagens as $m)
					<div class="mb-2">
						<strong>{{ $m->autor->name }}</strong>
						<span class="text-xs text-gray-500">{{ $m->created_at->format('H:i') }}</span>
						<div>{{ $m->conteudo }}</div>
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
		</div>
	</div>

	<script type="module">
		const messagesEl = document.getElementById('messages');
		const myId = @json(auth() -> id());

		function appendMessage(m) {
			const div = document.createElement('div');
			div.className = 'mb-2';
			const t = new Date(m.created_at ?? Date.now());
			div.innerHTML = `<strong>${m.user?.nome ?? 'User'}</strong>
      <span class="text-xs text-gray-500">${t.toLocaleTimeString()}</span>
      <div>${m.conteudo}</div>`;
			messagesEl.appendChild(div);
			messagesEl.scrollTop = messagesEl.scrollHeight;
		}

		// retorna o nome do canal atual (sala ou dm)
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

			// ---- GARANTE 1 ÚNICA ASSINATURA ----
			// se já estamos noutro canal, sai dele
			if (window.__CHAT_CHANNEL && window.__CHAT_CHANNEL !== name) {
				try {
					window.Echo.leave(window.__CHAT_CHANNEL);
				} catch {}
				window.__CHAT_CHANNEL = null;
			}
			// se já assinou este mesmo canal, não assina de novo
			if (window.__CHAT_CHANNEL === name) return;

			const onMessage = (e) => {
				// evita eco do próprio utilizador
				if (e?.user?.id === myId) return;
				appendMessage(e);
			};

			let ch;
			if (name.startsWith('sala.')) {
				ch = window.Echo.join(name).listen('.message.sent', onMessage);
			} else {
				ch = window.Echo.private(name).listen('.message.sent', onMessage);
			}

			// guarda o canal ativo (global) para impedir duplicatas
			window.__CHAT_CHANNEL = name;

			// (opcional) log
			if (import.meta.env?.DEV) console.debug('[chat] subscribed to', name);
		}

		if (window.Echo) {
			subscribeNow();
		} else {
			document.addEventListener('echo:ready', () => subscribeNow(), {
				once: true
			});
		}

		// ===== envio sem reload =====
		const form = document.querySelector('form[action]');
		if (form && !window.__CHAT_FORM_BOUND__) {
			window.__CHAT_FORM_BOUND__ = true; // evita bind duplo do submit

			form.addEventListener('submit', async (e) => {
				e.preventDefault();
				const input = form.querySelector('input[name="conteudo"]');
				const url = form.getAttribute('action');
				const text = (input.value || '').trim();
				if (!text) return;

				// UI otimista
				appendMessage({
					user: {
						id: myId,
						nome: @json(auth() -> user() -> name)
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
					// toOthers() no backend + filtro acima evitam duplicar para o próprio
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