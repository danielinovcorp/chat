<x-app-layout>
	<x-slot name="header">
		<h2 class="font-semibold text-xl">Nova Sala</h2>
	</x-slot>

	<div class="py-6">
		<div class="max-w-xl mx-auto bg-white shadow rounded p-6">
			<form method="POST" action="{{ route('salas.store') }}" enctype="multipart/form-data" class="space-y-4">
				@csrf

				<div>
					<label class="block text-sm font-medium mb-1">Nome</label>
					<input name="nome" class="w-full border rounded px-3 py-2" required value="{{ old('nome') }}">
					@error('nome') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
				</div>

				<div>
					<label class="block text-sm font-medium mb-1">Avatar (opcional)</label>
					<input type="file" name="avatar" accept="image/*">
					@error('avatar') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
				</div>

				<label class="inline-flex items-center gap-2">
					<input type="checkbox" name="is_private" value="1" {{ old('is_private') ? 'checked' : '' }}>
					<span>Privada</span>
				</label>

				<div class="flex gap-2">
					<a href="{{ route('chat.index') }}" class="px-4 py-2 rounded border">Cancelar</a>
					<button class="px-4 py-2 bg-blue-600 text-white rounded">Criar</button>
				</div>
			</form>
		</div>
	</div>
</x-app-layout>