<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Messaggi di validazione
    |--------------------------------------------------------------------------
    | Traduzione italiana dei messaggi standard di Laravel. I nomi leggibili
    | dei campi si aggiungono in fondo, nella voce "attributes".
    */

    'accepted' => 'Devi accettare :attribute.',
    'accepted_if' => 'Devi accettare :attribute quando :other è :value.',
    'active_url' => ':attribute non è un URL valido.',
    'after' => ':attribute deve essere una data successiva al :date.',
    'after_or_equal' => ':attribute deve essere una data uguale o successiva al :date.',
    'alpha' => ':attribute può contenere solo lettere.',
    'alpha_dash' => ':attribute può contenere solo lettere, numeri, trattini e trattini bassi.',
    'alpha_num' => ':attribute può contenere solo lettere e numeri.',
    'any_of' => ':attribute non è valido.',
    'array' => ':attribute deve essere un elenco.',
    'ascii' => ':attribute può contenere solo caratteri e simboli alfanumerici a un byte.',
    'base64' => ':attribute deve essere una stringa base64 valida.',
    'before' => ':attribute deve essere una data precedente al :date.',
    'before_or_equal' => ':attribute deve essere una data uguale o precedente al :date.',
    'between' => [
        'array' => ':attribute deve contenere tra :min e :max elementi.',
        'file' => ':attribute deve pesare tra :min e :max kilobyte.',
        'numeric' => ':attribute deve essere un valore tra :min e :max.',
        'string' => ':attribute deve contenere tra :min e :max caratteri.',
    ],
    'boolean' => ':attribute può valere solo vero o falso.',
    'can' => ':attribute contiene un valore non consentito.',
    'confirmed' => 'La conferma di :attribute non corrisponde.',
    'contains' => 'A :attribute manca un valore obbligatorio.',
    'current_password' => 'La password non è corretta.',
    'date' => ':attribute non è una data valida.',
    'date_equals' => ':attribute deve essere una data uguale al :date.',
    'date_format' => ':attribute non rispetta il formato :format.',
    'decimal' => ':attribute deve avere :decimal cifre decimali.',
    'declined' => ':attribute deve essere rifiutato.',
    'declined_if' => ':attribute deve essere rifiutato quando :other è :value.',
    'different' => ':attribute e :other devono essere diversi.',
    'digits' => ':attribute deve essere di :digits cifre.',
    'digits_between' => ':attribute deve avere tra :min e :max cifre.',
    'dimensions' => 'Le dimensioni dell\'immagine :attribute non sono valide.',
    'distinct' => ':attribute contiene un valore duplicato.',
    'doesnt_contain' => ':attribute non può contenere nessuno dei valori indicati.',
    'doesnt_end_with' => ':attribute non può terminare con uno dei valori indicati.',
    'doesnt_start_with' => ':attribute non può iniziare con uno dei valori indicati.',
    'email' => ':attribute non è un indirizzo email valido.',
    'encoding' => ':attribute deve usare la codifica :encoding.',
    'ends_with' => ':attribute deve terminare con uno dei valori indicati.',
    'enum' => 'Il valore di :attribute non è tra quelli ammessi.',
    'exists' => 'Il valore di :attribute non è valido.',
    'extensions' => ':attribute deve avere una delle estensioni indicate.',
    'file' => ':attribute deve essere un file.',
    'filled' => ':attribute è obbligatorio.',
    'gt' => [
        'array' => ':attribute deve contenere più di :value elementi.',
        'file' => ':attribute deve pesare più di :value kilobyte.',
        'numeric' => ':attribute deve essere maggiore di :value.',
        'string' => ':attribute deve contenere più di :value caratteri.',
    ],
    'gte' => [
        'array' => ':attribute deve contenere almeno :value elementi.',
        'file' => ':attribute deve pesare almeno :value kilobyte.',
        'numeric' => ':attribute deve essere maggiore o uguale a :value.',
        'string' => ':attribute deve contenere almeno :value caratteri.',
    ],
    'hex_color' => ':attribute deve essere un colore esadecimale valido.',
    'image' => ':attribute deve essere un\'immagine.',
    'in' => 'Il valore di :attribute non è valido.',
    'in_array' => 'Il valore di :attribute non è presente in :other.',
    'in_array_keys' => ':attribute deve contenere almeno una delle chiavi indicate.',
    'integer' => ':attribute deve essere un numero intero.',
    'ip' => ':attribute deve essere un indirizzo IP valido.',
    'ipv4' => ':attribute deve essere un indirizzo IPv4 valido.',
    'ipv6' => ':attribute deve essere un indirizzo IPv6 valido.',
    'json' => ':attribute deve essere una stringa JSON valida.',
    'list' => ':attribute deve essere un elenco.',
    'lowercase' => ':attribute deve essere tutto in minuscolo.',
    'lt' => [
        'array' => ':attribute deve contenere meno di :value elementi.',
        'file' => ':attribute deve pesare meno di :value kilobyte.',
        'numeric' => ':attribute deve essere minore di :value.',
        'string' => ':attribute deve contenere meno di :value caratteri.',
    ],
    'lte' => [
        'array' => ':attribute non può contenere più di :value elementi.',
        'file' => ':attribute non può pesare più di :value kilobyte.',
        'numeric' => ':attribute deve essere minore o uguale a :value.',
        'string' => ':attribute non può superare :value caratteri.',
    ],
    'mac_address' => ':attribute deve essere un indirizzo MAC valido.',
    'max' => [
        'array' => ':attribute non può contenere più di :max elementi.',
        'file' => ':attribute non può pesare più di :max kilobyte.',
        'numeric' => ':attribute non può essere maggiore di :max.',
        'string' => ':attribute non può superare :max caratteri.',
    ],
    'max_digits' => ':attribute non può avere più di :max cifre.',
    'mimes' => ':attribute deve essere un file di tipo :values.',
    'mimetypes' => ':attribute deve essere un file di tipo :values.',
    'min' => [
        'array' => ':attribute deve contenere almeno :min elementi.',
        'file' => ':attribute deve pesare almeno :min kilobyte.',
        'numeric' => ':attribute deve essere almeno :min.',
        'string' => ':attribute deve contenere almeno :min caratteri.',
    ],
    'min_digits' => ':attribute deve avere almeno :min cifre.',
    'missing' => ':attribute non deve essere presente.',
    'missing_if' => ':attribute non deve essere presente quando :other è :value.',
    'missing_unless' => ':attribute non deve essere presente a meno che :other non sia :value.',
    'missing_with' => ':attribute non deve essere presente quando c\'è :values.',
    'missing_with_all' => ':attribute non deve essere presente quando ci sono :values.',
    'multiple_of' => ':attribute deve essere un multiplo di :value.',
    'not_in' => 'Il valore di :attribute non è valido.',
    'not_regex' => 'Il formato di :attribute non è valido.',
    'numeric' => ':attribute deve essere un numero.',
    'password' => [
        'letters' => ':attribute deve contenere almeno una lettera.',
        'mixed' => ':attribute deve contenere almeno una lettera maiuscola e una minuscola.',
        'numbers' => ':attribute deve contenere almeno un numero.',
        'symbols' => ':attribute deve contenere almeno un simbolo.',
        'uncompromised' => 'Questa password compare in archivi di dati rubati. Scegline un\'altra.',
    ],
    'present' => ':attribute deve essere presente.',
    'present_if' => ':attribute deve essere presente quando :other è :value.',
    'present_unless' => ':attribute deve essere presente a meno che :other non sia :value.',
    'present_with' => ':attribute deve essere presente insieme a :values.',
    'present_with_all' => ':attribute deve essere presente insieme a :values.',
    'prohibited' => ':attribute non è ammesso.',
    'prohibited_if' => ':attribute non è ammesso quando :other è :value.',
    'prohibited_if_accepted' => ':attribute non è ammesso quando :other è accettato.',
    'prohibited_if_declined' => ':attribute non è ammesso quando :other è rifiutato.',
    'prohibited_unless' => ':attribute non è ammesso a meno che :other non sia tra :values.',
    'prohibits' => ':attribute impedisce la presenza di :other.',
    'regex' => 'Il formato di :attribute non è valido.',
    'required' => ':attribute è obbligatorio.',
    'required_array_keys' => ':attribute deve contenere le voci: :values.',
    'required_if' => ':attribute è obbligatorio quando :other è :value.',
    'required_if_accepted' => ':attribute è obbligatorio quando :other è accettato.',
    'required_if_declined' => ':attribute è obbligatorio quando :other è rifiutato.',
    'required_unless' => ':attribute è obbligatorio a meno che :other non sia tra :values.',
    'required_with' => ':attribute è obbligatorio quando è presente :values.',
    'required_with_all' => ':attribute è obbligatorio quando sono presenti :values.',
    'required_without' => ':attribute è obbligatorio quando manca :values.',
    'required_without_all' => ':attribute è obbligatorio quando mancano tutti i campi :values.',
    'same' => ':attribute e :other devono coincidere.',
    'size' => [
        'array' => ':attribute deve contenere :size elementi.',
        'file' => ':attribute deve pesare :size kilobyte.',
        'numeric' => ':attribute deve valere :size.',
        'string' => ':attribute deve contenere :size caratteri.',
    ],
    'starts_with' => ':attribute deve iniziare con uno dei valori indicati.',
    'string' => ':attribute deve essere un testo.',
    'timezone' => ':attribute deve essere un fuso orario valido.',
    'unique' => ':attribute è già in uso.',
    'uploaded' => 'Il caricamento di :attribute non è riuscito.',
    'uppercase' => ':attribute deve essere tutto in maiuscolo.',
    'url' => ':attribute non è un URL valido.',
    'ulid' => ':attribute deve essere un ULID valido.',
    'uuid' => ':attribute deve essere un UUID valido.',

    /*
    |--------------------------------------------------------------------------
    | Messaggi personalizzati
    |--------------------------------------------------------------------------
    */

    'custom' => [
        'password' => [
            'confirmed' => 'Le due password non coincidono.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Nomi leggibili dei campi
    |--------------------------------------------------------------------------
    */

    'attributes' => [
        'name' => 'il nome',
        'email' => 'l\'indirizzo email',
        'password' => 'la password',
        'password_confirmation' => 'la conferma della password',
        'current_password' => 'la password attuale',
        'telefono' => 'il telefono',
        'ragione_sociale' => 'la ragione sociale',
        'partita_iva' => 'la partita IVA',
        'codice_fiscale' => 'il codice fiscale',
        'indirizzo' => 'l\'indirizzo',
        'cap' => 'il CAP',
        'citta' => 'la città',
        'provincia' => 'la provincia',
    ],

];
