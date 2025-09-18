<x-app-layout>
	<x-slot name="header">
		<h2 class="font-semibold text-xl">Editar Sala</h2>
	</x-slot>

	<div class="py-6">
		<div class="max-w-xl mx-auto bg-white shadow rounded p-6">
			<form method="POST" action="{{ route('salas.update', $sala) }}" enctype="multipart/form-data" class="space-y-4">
				@csrf
				@method('PUT')

				<div>
					<label class="block text-sm font-medium mb-1">Nome</label>
					<input name="nome" class="w-full border rounded px-3 py-2" required value="{{ old('nome', $sala->nome) }}">
					@error('nome') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
				</div>

				<div>
					<label class="block text-sm font-medium mb-1">Avatar (opcional)</label>
					@if($sala->avatar_url)
					<img src="{{ $sala->avatar_url }}" class="h-12 w-12 rounded mb-2" alt="Avatar atual">
					@endif
					<input type="file" name="avatar" accept="image/*">
					@error('avatar') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
				</div>

				<label class="inline-flex items-center gap-2">
					<input type="checkbox" name="is_private" value="1" {{ old('is_private', $sala->is_private) ? 'checked' : '' }}>
					<span>Privada</span>
				</label>

				<div class="flex gap-2">
					<a href="{{ route('chat.index', ['sala' => $sala->id]) }}" class="px-4 py-2 rounded border">Cancelar</a>
					<button class="px-4 py-2 bg-blue-600 text-white rounded">Guardar</button>
				</div>
			</form>

			<form method="POST" action="{{ route('salas.destroy', $sala) }}" class="mt-6"
				onsubmit="return confirm('Apagar a sala? Esta ação não pode ser desfeita.');">
				@csrf @method('DELETE')
				<button class="px-4 py-2 bg-red-600 text-white rounded">Apagar Sala</button>
			</form>
		</div>
	</div>
</x-app-layout>