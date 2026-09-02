<?php

namespace App\Providers;

use App\Models\Article;
use App\Models\Order;
use App\Models\PaymentIntention;
use App\Models\PaymentRefund;
use App\Models\PaymentTransaction;
use App\Models\PaymentWebhook;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Policies\ArticlePolicy;
use App\Policies\OrderPolicy;
use App\Policies\PaymentIntentionPolicy;
use App\Policies\PaymentRefundPolicy;
use App\Policies\PaymentTransactionPolicy;
use App\Policies\PaymentWebhookPolicy;
use App\Policies\ProductPolicy;
use App\Policies\SettingPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
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
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(Article::class, ArticlePolicy::class);
        Gate::policy(Setting::class, SettingPolicy::class);
        Gate::policy(Order::class, OrderPolicy::class);
        Gate::policy(PaymentIntention::class, PaymentIntentionPolicy::class);
        Gate::policy(PaymentTransaction::class, PaymentTransactionPolicy::class);
        Gate::policy(PaymentRefund::class, PaymentRefundPolicy::class);
        Gate::policy(PaymentWebhook::class, PaymentWebhookPolicy::class);

        Gate::before(function (User $user): ?bool {
            return $user->hasRole('super_admin') ? true : null;
        });
    }
}
