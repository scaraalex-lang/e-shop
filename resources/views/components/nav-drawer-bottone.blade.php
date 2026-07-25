{{-- Apre la navigazione laterale. Vive dentro la barra sticky; il pannello no:
     un antenato con backdrop-filter diventa il containing block dei figli
     "fixed", e il drawer resterebbe incastrato nella barra. --}}
<button type="button"
        data-drawer-apri
        aria-controls="nav-laterale"
        aria-expanded="false"
        class="lg:hidden absolute left-6 inline-flex items-center gap-2 p-1 -ml-1
               text-caffe hover:text-oro-scuro transition-colors duration-300">
    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
         stroke-width="2" stroke-linecap="round" aria-hidden="true">
        <line x1="3" y1="6" x2="21" y2="6" />
        <line x1="3" y1="12" x2="21" y2="12" />
        <line x1="3" y1="18" x2="21" y2="18" />
    </svg>
    <span class="sr-only">Apri il menu</span>
</button>
