<?php

namespace Aisuvro\AndcartInstaller\Controllers;

use Illuminate\Routing\Controller;
use Aisuvro\AndcartInstaller\Events\LaravelInstallerFinished;
use Aisuvro\AndcartInstaller\Helpers\EnvironmentManager;
use Aisuvro\AndcartInstaller\Helpers\FinalInstallManager;
use Aisuvro\AndcartInstaller\Helpers\InstalledFileManager;

class FinalController extends Controller
{
    /**
     * Update installed file and display finished view.
     *
     * @param  \Jmrashed\LaravelInstaller\Helpers\InstalledFileManager  $fileManager
     * @param  \Jmrashed\LaravelInstaller\Helpers\FinalInstallManager  $finalInstall
     * @param  \Jmrashed\LaravelInstaller\Helpers\EnvironmentManager  $environment
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function finish(InstalledFileManager $fileManager, FinalInstallManager $finalInstall, EnvironmentManager $environment)
    {
        $finalMessages = $finalInstall->runFinal();
        $finalStatusMessage = $fileManager->update();
        $finalEnvFile = $environment->getEnvContent();

        event(new LaravelInstallerFinished);

        return redirect('/');
    }
}
