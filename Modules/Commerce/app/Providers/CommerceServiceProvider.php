<?php

namespace Modules\Commerce\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

class CommerceServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Commerce';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'commerce';

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    protected array $commands = [
        \Modules\Commerce\Console\PromuoviStaff::class,
    ];

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    /**
     * Il driver d'incasso: "simulato" (carta finta, sviluppo) o "stripe"
     * (Checkout ospitata, vedi PagamentoStripe) — checkout, ordine e
     * tracciamento non si accorgono di quale sia attivo.
     */
    public function register(): void
    {
        parent::register();

        $this->app->bind(
            \Modules\Commerce\Pagamenti\Pagamento::class,
            fn () => match (config('commerce.pagamento', 'simulato')) {
                'stripe' => new \Modules\Commerce\Pagamenti\PagamentoStripe(
                    new \Stripe\StripeClient(config('commerce.stripe.secret')),
                ),
                default => new \Modules\Commerce\Pagamenti\PagamentoSimulato,
            },
        );
    }

    /**
     * Define module schedules.
     * 
     * @param $schedule
     */
    // protected function configureSchedules(Schedule $schedule): void
    // {
    //     $schedule->command('inspire')->hourly();
    // }
}
