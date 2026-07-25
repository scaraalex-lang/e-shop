@extends('layouts.gestione')

@section('title', 'Accesso')

@section('content')
<div class="mx-auto max-w-md">
    <h1 class="font-serif text-4xl text-caffe">Accesso</h1>
    <p class="mt-3 font-sans font-light text-testo-soft leading-relaxed">
        Area riservata a chi gestisce la vetrina.
    </p>

    @if ($errors->any())
        <div role="alert" class="mt-6 border-2 border-oro-scuro bg-panna px-5 py-3 font-sans text-[14px]">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('gestione.entra.post') }}" class="mt-8 space-y-6">
        @csrf
        <div>
            <label for="password" class="block font-sans text-[11px] tracking-[0.28em] uppercase text-oro-scuro mb-2">
                Password
            </label>
            <input id="password" name="password" type="password" required autofocus
                   autocomplete="current-password"
                   class="w-full border-2 border-caffe bg-bianco px-4 py-3 font-sans text-[15px]
                          focus:outline-none focus:border-oro-scuro transition-colors">
        </div>

        <x-button variant="piena" type="submit">Entra</x-button>
    </form>
</div>
@endsection
