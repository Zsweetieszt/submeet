<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use App\Services\EmailService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(EmailService::class, function () {
            return new EmailService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            return (new MailMessage)
                ->view('auth.verify-custom', [
                    'url' => $url,
                    'user' => $notifiable
                ]);
        });
        
        $app_env = env('APP_ENV');
        if ($app_env !== 'local') {
            URL::forceRootUrl(env('APP_URL'));
            URL::forceScheme('https');
        }
    }
}
