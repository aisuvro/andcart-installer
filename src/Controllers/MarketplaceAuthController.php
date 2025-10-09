<?php

namespace Aisuvro\AndcartInstaller\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Services\MarketplaceService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class MarketplaceAuthController extends Controller
{
    protected $marketplaceService;

    public function __construct(MarketplaceService $marketplaceService)
    {
        $this->marketplaceService = $marketplaceService;
    }

    /**
     * Show marketplace authentication page
     */
    public function showAuth()
    {
        // Check if already authenticated
        if ($this->marketplaceService->isAuthenticated()) {
            return redirect()->route('LaravelInstaller::server-requirements');
        }

        return view('vendor.installer.marketplace-auth');
    }

    /**
     * Handle marketplace login during installation
     */
    public function handleLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        try {
            $result = $this->marketplaceService->login(
                $request->input('email'),
                $request->input('password')
            );

            if ($result['success']) {
                // Create verification file for installer
                $this->createAuthVerifiedFile();
                
                return redirect()->route('LaravelInstaller::server-requirements')
                    ->with('success', 'Successfully authenticated with marketplace');
            }

            return redirect()->back()
                ->withInput()
                ->with('error', $result['message']);

        } catch (\Exception $e) {
            Log::error('Marketplace login error during installation: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred during authentication');
        }
    }

    /**
     * Handle marketplace signup during installation
     */
    public function handleSignup(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:8',
            'password_confirmation' => 'required|same:password',
        ]);

        try {
            $result = $this->marketplaceService->signup(
                $request->input('name'),
                $request->input('email'),
                $request->input('password'),
                $request->input('password_confirmation')
            );

            if ($result['success']) {
                return redirect()->back()
                    ->with('success', 'Account created successfully. Please login with your credentials.');
            }

            return redirect()->back()
                ->withInput()
                ->with('error', $result['message']);

        } catch (\Exception $e) {
            Log::error('Marketplace signup error during installation: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred during account creation');
        }
    }

    /**
     * Skip authentication if remote is not available
     */
    public function skipAuth()
    {
        if (!$this->marketplaceService->isRemoteAvailable()) {
            // Create verification file to allow installation to continue
            $this->createAuthVerifiedFile('offline_mode');
            
            return redirect()->route('LaravelInstaller::server-requirements')
                ->with('info', 'Continuing in offline mode - marketplace features will be limited');
        }

        return redirect()->back()
            ->with('error', 'Marketplace is available. Please authenticate to continue.');
    }

    /**
     * Create authentication verified file
     */
    private function createAuthVerifiedFile($mode = 'authenticated')
    {
        $filePath = base_path('.marketplace-verified');
        $data = [
            'mode' => $mode,
            'timestamp' => now()->toISOString(),
            'user' => $this->marketplaceService->getUser()
        ];
        
        File::put($filePath, json_encode($data, JSON_PRETTY_PRINT));
    }

    /**
     * Check if marketplace authentication is verified
     */
    public static function isVerified(): bool
    {
        $filePath = base_path('.marketplace-verified');
        
        if (!File::exists($filePath)) {
            return false;
        }

        try {
            $data = json_decode(File::get($filePath), true);
            return isset($data['mode']) && in_array($data['mode'], ['authenticated', 'offline_mode']);
        } catch (\Exception $e) {
            return false;
        }
    }
}