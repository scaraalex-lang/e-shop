{{--
    Conferma di eliminazione senza JavaScript: <details> nativo, come le sezioni
    a fisarmonica del Ricordino Designer. Si apre da solo se la password inserita
    era sbagliata, così l'errore resta visibile.
--}}
<section class="max-w-xl">
    <header>
        <h2 class="font-serif text-2xl font-medium">Elimina l'account</h2>
        <p class="mt-2 font-sans font-light text-[14px] leading-relaxed text-testo-soft">
            L'eliminazione è definitiva: dati, bozze e storico vengono rimossi senza
            possibilità di recupero. Scarica prima ciò che vuoi conservare.
        </p>
    </header>

    <details class="mt-7 group" @if ($errors->userDeletion->isNotEmpty()) open @endif>
        <summary class="inline-flex items-center justify-center gap-2 cursor-pointer select-none list-none
                        font-sans uppercase text-[12px] tracking-[0.22em] px-8 py-3.5
                        bg-transparent border-2 border-errore text-errore
                        hover:bg-errore hover:text-bianco transition-all duration-300 ease-out">
            Elimina l'account
        </summary>

        <form method="POST" action="{{ route('account.profilo.destroy') }}"
              class="mt-6 border border-errore/30 bg-panna/60 px-6 py-7 space-y-5">
            @csrf
            @method('delete')

            <p class="font-serif text-lg">Vuoi davvero eliminare il tuo account?</p>
            <p class="font-sans font-light text-[14px] leading-relaxed text-testo-soft">
                Inserisci la password per confermare.
            </p>

            <div>
                <x-input-label for="password_eliminazione" value="Password" />
                <x-text-input id="password_eliminazione" name="password" type="password"
                              autocomplete="current-password" />
                <x-input-error :messages="$errors->userDeletion->get('password')" />
            </div>

            <div class="flex flex-wrap items-center gap-4">
                <x-danger-button>Elimina definitivamente</x-danger-button>
                <a href="{{ route('account.profilo') }}"
                   class="font-sans text-[13px] text-testo-soft hover:text-oro-scuro transition-colors duration-300
                          underline underline-offset-4 decoration-oro/40">
                    Annulla
                </a>
            </div>
        </form>
    </details>
</section>
