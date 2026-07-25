@extends('layouts.accesso')

@section('title', 'Accesso non consentito — MemorAI')
@section('occhiello', 'Area riservata')
@section('titolo', 'Questa pagina non è per te')

@section('modulo')
    <p class="font-sans font-light text-[15px] leading-relaxed text-testo-soft">
        {{ $exception?->getMessage() ?: "Il tuo account non ha accesso a questa sezione." }}
    </p>

    <p class="mt-5 font-sans font-light text-[15px] leading-relaxed text-testo-soft">
        Se pensi che sia un errore, scrivici: si sistema in fretta.
    </p>

    <div class="mt-8 flex flex-wrap gap-4">
        <x-button :href="auth()->check() ? route('account') : url('/')">
            {{ auth()->check() ? 'Torna al mio account' : 'Torna alla vetrina' }}
        </x-button>
    </div>
@endsection
