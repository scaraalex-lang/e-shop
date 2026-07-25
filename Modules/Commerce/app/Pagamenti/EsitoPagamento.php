<?php

namespace Modules\Commerce\Pagamenti;

readonly class EsitoPagamento
{
    private function __construct(
        public bool $riuscito,
        public string $riferimento,
        public ?string $messaggio = null,
    ) {}

    public static function riuscito(string $riferimento): self
    {
        return new self(true, $riferimento);
    }

    public static function fallito(string $messaggio, string $riferimento = ''): self
    {
        return new self(false, $riferimento, $messaggio);
    }
}
