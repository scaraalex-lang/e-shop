@extends('layouts.account')

@section('title', 'Profilo e accesso — MemorAI')
@section('titolo', 'Profilo e accesso')
@section('sottotitolo', 'I dati con cui ti riconosciamo e con cui entri nel tuo account.')

@section('account')
    <div class="space-y-px bg-caffe/15 border border-caffe/15">
        <div class="bg-bianco px-7 py-9">
            @include('account.partials.dati')
        </div>

        <div class="bg-bianco px-7 py-9">
            @include('account.partials.password')
        </div>

        <div class="bg-bianco px-7 py-9">
            @include('account.partials.elimina')
        </div>
    </div>
@endsection
