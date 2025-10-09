<?php

namespace Aisuvro\AndcartInstaller\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\MarketplaceService;
use Aisuvro\AndcartInstaller\Controllers\MarketplaceAuthController;

class MarketplaceAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if marketplace authentication is enabled in config
        if (!config('installer.marketplace.enabled', true)) {
            return $next($request);
        }

        // Skip authentication check for welcome, auth routes, and finish route
        $skipRoutes = [
            'LaravelInstaller::welcome',
            'LaravelInstaller::marketplace-auth',
            'LaravelInstaller::marketplace-login',
            'LaravelInstaller::marketplace-signup',
            'LaravelInstaller::marketplace-skip',
            'LaravelInstaller::final',
            'LaravelInstaller::installation-finished',
        ];

        if (in_array($request->route()->getName(), $skipRoutes)) {
            return $next($request);
        }

        try {
            $marketplaceService = app(MarketplaceService::class);
            
            // Check if marketplace auth is verified (either authenticated or offline mode)
            if (MarketplaceAuthController::isVerified()) {
                return $next($request);
            }

            // If marketplace is required and user is not authenticated, redirect to auth
            if (config('installer.marketplace.required', false)) {
                return redirect()->route('LaravelInstaller::marketplace-auth')
                    ->with('error', 'Marketplace authentication is required to continue installation.');
            }

            // If not verified and remote is available, redirect to auth
            if ($marketplaceService->isRemoteAvailable()) {
                return redirect()->route('LaravelInstaller::marketplace-auth')
                    ->with('error', 'Please authenticate with the marketplace to continue installation.');
            }

            // If remote is not available and skip is allowed, auto-create verification
            if (config('installer.marketplace.skip_if_offline', true)) {
                $filePath = base_path('.marketplace-verified');
                $data = [
                    'mode' => 'offline_mode',
                    'timestamp' => now()->toISOString(),
                    'user' => null
                ];
                
                file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));
                return $next($request);
            }

            // Fallback to auth page
            return redirect()->route('LaravelInstaller::marketplace-auth')
                ->with('info', 'Marketplace authentication is recommended.');

        } catch (\Exception $e) {
            // If there's an error with the service, allow installation to continue
            return $next($request);
        }
    }
}