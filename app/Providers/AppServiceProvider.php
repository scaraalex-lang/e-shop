<?php

namespace App\Providers;

use App\Enums\ZonaMenu;
use App\Models\VoceMenu;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /**
         * Menu di navigazione e footer: il layout condiviso non ha un
         * controller proprio (ogni pagina lo estende), quindi queste voci
         * arrivano da un composer invece che essere passate da ogni route.
         */
        View::composer('layouts.app', function ($view) {
            $view->with([
                'naveVoci' => VoceMenu::inZona(ZonaMenu::Principale)->attive()->get(),
                'footerCollezioni' => VoceMenu::inZona(ZonaMenu::FooterCollezioni)->attive()->get(),
                'footerServizi' => VoceMenu::inZona(ZonaMenu::FooterServizi)->attive()->get(),
                'footerAssistenza' => VoceMenu::inZona(ZonaMenu::FooterAssistenza)->attive()->get(),
                'legaleVoci' => VoceMenu::inZona(ZonaMenu::Legale)->attive()->get(),
            ]);
        });
    }
}
