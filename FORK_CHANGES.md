# Fork Changes - AndCart Installer

This fork of `jmrashed/laravel-installer` has been updated for AndCart compatibility and Laravel 12 support.

## Changes Made

### 1. Package Information Updates
- **Package name**: Changed from `jmrashed/laravel-installer` to `aisuvro/andcart-installer`
- **Description**: Updated to "AndCart applications" instead of "Laravel applications"
- **Keywords**: Added "andcart" and "ecommerce installer"
- **URLs**: Updated homepage and support URLs to point to this fork

### 2. Laravel 12 Compatibility
- **PHP requirement**: Updated from `>=7.0.0` to `>=8.1.0`
- **Laravel dependencies**: Added explicit dependencies for:
  - `illuminate/support`: `^9.0|^10.0|^11.0|^12.0`
  - `illuminate/console`: `^9.0|^10.0|^11.0|^12.0`
  - `illuminate/http`: `^9.0|^10.0|^11.0|^12.0`

### 3. Namespace Updates
- **Root namespace**: Changed from `Jmrashed\\LaravelInstaller\\` to `Aisuvro\\AndcartInstaller\\`
- **Service provider**: Updated to `Aisuvro\\AndcartInstaller\\Providers\\LaravelInstallerServiceProvider`
- **All PHP files**: Updated namespace declarations and import statements throughout the codebase

### 4. Files Modified
- `composer.json` - Package metadata and dependencies
- All PHP files in `src/` directory - Namespace updates
- All test files - Namespace imports

## Usage in AndCart Project

Add to your `composer.json`:

```json
{
    "require": {
        "aisuvro/andcart-installer": "dev-main"
    },
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/aisuvro/andcart-installer"
        }
    ]
}
```

Then run:
```bash
composer install
```

## Original Credits
This package is a fork of [jmrashed/laravel-installer](https://github.com/jmrashed/laravel-installer) by Rashed Zaman.