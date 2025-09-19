<section>
	<header class="col-span-12">
		<h2 class="text-lg font-medium text-gray-900">
			{{ __('Profile Information') }}
		</h2>
		<p class="mt-1 text-sm text-gray-600">
			{{ __("Update your account's profile information and email address.") }}
		</p>
	</header>

	<form id="send-verification" method="post" action="{{ route('verification.send') }}">
		@csrf
	</form>

	<div class="mt-6 grid grid-cols-1 lg:grid-cols-12 gap-8">
		{{-- Coluna esquerda: dados --}}
		<div class="lg:col-span-8">
			<form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-6">
				@csrf
				@method('patch')

				<div>
					<x-input-label for="name" :value="__('Name')" />
					<x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
						:value="old('name', $user->name)" required autofocus autocomplete="name" />
					<x-input-error class="mt-2" :messages="$errors->get('name')" />
				</div>

				<div>
					<x-input-label for="email" :value="__('Email')" />
					<x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
						:value="old('email', $user->email)" required autocomplete="username" />
					<x-input-error class="mt-2" :messages="$errors->get('email')" />

					@if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
					<div>
						<p class="text-sm mt-2 text-gray-800">
							{{ __('Your email address is unverified.') }}
							<button form="send-verification"
								class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
								{{ __('Click here to re-send the verification email.') }}
							</button>
						</p>

						@if (session('status') === 'verification-link-sent')
						<p class="mt-2 font-medium text-sm text-green-600">
							{{ __('A new verification link has been sent to your email address.') }}
						</p>
						@endif
					</div>
					@endif
				</div>

				{{-- Input de foto (o arquivo vai junto com este mesmo form) --}}
				<div>
					<x-input-label for="avatar" :value="__('Profile photo')" />
					<input id="avatar" name="avatar" type="file" accept="image/*"
						class="mt-1 block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4
                                  file:rounded-md file:border-0 file:bg-gray-100 file:text-gray-700
                                  hover:file:bg-gray-200" />
					<x-input-error class="mt-2" :messages="$errors->get('avatar')" />
					<p class="text-xs text-gray-500 mt-1">{{ __('PNG/JPG até 2 MB.') }}</p>
				</div>

				<div class="flex items-center gap-4">
					<x-primary-button>{{ __('Save') }}</x-primary-button>

					@if (session('status') === 'profile-updated')
					<p x-data="{ show: true }" x-show="show" x-transition
						x-init="setTimeout(() => show = false, 2000)"
						class="text-sm text-gray-600">{{ __('Saved.') }}</p>
					@endif
				</div>
			</form>
		</div>

		{{-- Coluna direita: pré-visualização da foto --}}
		<div class="lg:col-span-4">
			<div
				x-data="{
                    previewUrl: null,
                    init() {
                        const input = document.getElementById('avatar');
                        if (input) {
                            input.addEventListener('change', (e) => {
                                const [file] = e.target.files || [];
                                if (file) this.previewUrl = URL.createObjectURL(file);
                            });
                        }
                    }
                }"
				class="bg-white rounded-lg shadow p-6 flex flex-col items-center gap-4">
				<h3 class="text-sm font-medium text-gray-900">{{ __('Current photo') }}</h3>

				{{-- Imagem atual ou fallback --}}
				@php
				$avatarUrl = $user->avatar_path
				? Storage::disk('public')->url($user->avatar_path)
				: 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($user->email))) . '?s=240&d=mp';
				@endphp

				<div class="relative">
					<img x-show="!previewUrl" src="{{ $avatarUrl }}" alt="Avatar"
						class="h-40 w-40 rounded-full object-cover ring-2 ring-gray-200" />
					<img x-show="previewUrl" :src="previewUrl" alt="Preview"
						class="h-40 w-40 rounded-full object-cover ring-2 ring-indigo-300" />
				</div>

				<p class="text-xs text-gray-500 text-center">
					{{ __('Select a new image on the left and click Save to apply.') }}
				</p>

				@if ($user->avatar_path)
				<form method="post" action="{{ route('profile.update') }}">
					@csrf
					@method('patch')
					<input type="hidden" name="remove_avatar" value="1">
					<button class="text-xs text-red-600 hover:text-red-700 underline">
						{{ __('Remove photo') }}
					</button>
				</form>
				@endif
			</div>
		</div>
	</div>
</section>
