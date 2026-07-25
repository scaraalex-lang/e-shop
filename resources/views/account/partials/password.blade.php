<section class="max-w-xl">
    <header>
        <h2 class="font-serif text-2xl font-medium">Password</h2>
        <p class="mt-2 font-sans font-light text-[14px] leading-relaxed text-testo-soft">
            Scegline una lunga e usata solo qui: è la chiave del tuo account.
        </p>
    </header>

    <form method="POST" action="{{ route('password.update') }}" class="mt-7 space-y-6">
        @csrf
        @method('put')

        <div>
            <x-input-label for="current_password" value="Password attuale" />
            <x-text-input id="current_password" name="current_password" type="password"
                          autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" />
        </div>

        <div>
            <x-input-label for="nuova_password" value="Nuova password" />
            <x-text-input id="nuova_password" name="password" type="password"
                          autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" />
        </div>

        <div>
            <x-input-label for="nuova_password_confirmation" value="Conferma nuova password" />
            <x-text-input id="nuova_password_confirmation" name="password_confirmation" type="password"
                          autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" />
        </div>

        <div class="flex flex-wrap items-center gap-5">
            <x-primary-button>Aggiorna password</x-primary-button>

            @if (session('status') === 'password-updated')
                <span class="font-sans text-[13px] text-successo">Password aggiornata.</span>
            @endif
        </div>
    </form>
</section>
