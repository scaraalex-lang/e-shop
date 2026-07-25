<section class="max-w-xl">
    <header>
        <h2 class="font-serif text-2xl font-medium">I tuoi dati</h2>
        <p class="mt-2 font-sans font-light text-[14px] leading-relaxed text-testo-soft">
            Nome e indirizzo email dell'account.
        </p>
    </header>

    {{-- modulo separato: serve solo al pulsante "rimanda l'email di verifica" --}}
    <form id="invia-verifica" method="POST" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="POST" action="{{ route('account.profilo.update') }}" class="mt-7 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" value="Nome e cognome" />
            <x-text-input id="name" name="name" type="text" :value="old('name', $user->name)"
                          required autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" value="Indirizzo email" />
            <x-text-input id="email" name="email" type="email" :value="old('email', $user->email)"
                          required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <p class="mt-3 font-sans font-light text-[13px] text-testo-soft">
                    Questo indirizzo non è ancora verificato.
                    <button form="invia-verifica" type="submit"
                            class="text-oro-scuro hover:text-caffe transition-colors duration-300
                                   underline underline-offset-4 decoration-oro/40 cursor-pointer">
                        Rimanda l'email di verifica
                    </button>
                </p>

                @if (session('status') === 'verification-link-sent')
                    <p class="mt-2 font-sans text-[13px] text-successo">
                        Ti abbiamo inviato un nuovo link di verifica.
                    </p>
                @endif
            @endif
        </div>

        <div class="flex flex-wrap items-center gap-5">
            <x-primary-button>Salva</x-primary-button>

            @if (session('status') === 'profile-updated')
                <span class="font-sans text-[13px] text-successo">Dati aggiornati.</span>
            @endif
        </div>
    </form>
</section>
