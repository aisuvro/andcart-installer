# Laravel Installer

[![Latest Stable Version](https://poser.pugx.org/jmrashed/laravel-installer/v/stable)](https://packagist.org/packages/jmrashed/laravel-installer) [![Total Downloads](https://poser.pugx.org/jmrashed/laravel-installer/downloads)](https://packagist.org/packages/jmrashed/laravel-installer) [![Monthly Downloads](https://poser.pugx.org/jmrashed/laravel-installer/d/monthly)](https://packagist.org/packages/jmrashed/laravel-installer) [![License](https://poser.pugx.org/jmrashed/laravel-installer/license)](https://packagist.org/packages/jmrashed/laravel-installer) [![Stars](https://img.shields.io/github/stars/jmrashed/laravel-installer.svg?style=social&label=Stars)](https://github.com/jmrashed/laravel-installer) [![Forks](https://img.shields.io/github/forks/jmrashed/laravel-installer.svg?style=social&label=Forks)](https://github.com/jmrashed/laravel-installer)

**Laravel Installer** is a complete package designed to simplify the installation process for Laravel projects. This installer handles system requirement checks, environment configuration, database setup, and purchase code validation.

---

## 📊 Statistics

- **Total Downloads**: ![Total Downloads](https://poser.pugx.org/jmrashed/laravel-installer/downloads)
- **Monthly Downloads**: ![Monthly Downloads](https://poser.pugx.org/jmrashed/laravel-installer/d/monthly)
- **GitHub Stars**: ![Stars](https://img.shields.io/github/stars/jmrashed/laravel-installer.svg?style=social)
- **GitHub Forks**: ![Forks](https://img.shields.io/github/forks/jmrashed/laravel-installer.svg?style=social)

---

## 🌟 Features

- **System Requirements Check**: Automatically verifies PHP version and required extensions.
- **Environment File Setup**: Helps create and configure the `.env` file.
- **Database Configuration**: Offers the option to run migrations, seeders, or import an SQL dump.
- **Purchase Code Validation**: Built-in validation for purchase codes.
- **User-Friendly Interface**: A guided step-by-step installation process with a simple interface.

---

## 🛠️ Installation

To install the package, run the following command:

```bash
composer require jmrashed/laravel-installer
```

Then, publish the configuration file:

```bash
php artisan vendor:publish --provider="Jmrashed\LaravelInstaller\Providers\LaravelInstallerServiceProvider"
php artisan vendor:publish --tag=installer-config
```

---

## 🚀 How to Use

After installation, run the installer using:

```bash
php artisan installer:run
```

The installer will guide you through these steps:

1. **System Requirements Check**: Ensures the necessary PHP version and extensions are installed.
2. **Environment File Setup**: Prompts for database credentials and generates the `.env` file.
3. **Database Setup**: Choose to run migrations and seeders or import a SQL dump.
4. **Purchase Code Validation**: If enabled, the user is required to enter their purchase code.
5. **Completion**: Confirms successful installation.

---


## envato validation 

sample api response from your existing website
```json
{
  "message": "Welcome to the Envato Purchase Validation API",
  "account1": {
    "token": "fsHuTBwXZTlEqZYQacniBeNZFCrT01eZ"
  },
  "account2": {
    "token": "aGPUug8SeQagLaSDZ3LXdKQ8x0hHNtlc"
  },
  "validation": {
    "url": "https://api.envato.com/v3/market/author/sale"
  }
}
```

### Update your api endpoint

To get barrier token update the API endpoint
```php
   $envatoApiTokenUrl = 'http://your-domain.com/api/get-envato-barrier-token';
```
To Store verification data to your application update the API endpoint
```php
   $envatoApiStoreUrl = 'http://your-domain.com/api/store-envato-verification-response';
```

Sample response
```json
{
  "amount": "32.06",
  "sold_at": "2024-01-28T15:21:32+11:00",
  "license": "Regular License",
  "support_amount": "0.00",
  "supported_until": null,
  "item": {
    "id": 1234567890,
    "name": "product Title",
    "number_of_sales": 169,
    "author_username": "author_username",
    "author_url": "https://codecanyon.net/user/author_username",
    "url": "https://codecanyon.net/item/product/34567890",
    "site": "codecanyon.net",
    "classification": "mobile/flutter",
    "classification_url": "https://codecanyon.net/category/mobile/flutter",
    "price_cents": 11900,
    "author_image": "https://s3.envato.com/files/396614067/80x80.jpg",
    "summary": "Software Version: Flutter 2.x, Flutter 1.x, Other",
    "published_at": "2022-06-14T01:38:25+10:00",
    "trending": false
  },
  "buyer": "keosovannboravann",
  "purchase_count": 1
}

```

## 📂 Directory Structure

Here’s a simplified structure of the project directories and key files:

```text
- 📁 src/
   - 📁 Config/
      - 📄 installer.php
   - 📁 Controllers/
      - 📄 DatabaseController.php
      - 📄 EnvironmentController.php
   - 📁 Events/
      - 📄 EnvironmentSaved.php
      - 📄 LaravelInstallerFinished.php
   - 📁 Helpers/
      - 📄 DatabaseManager.php
      - 📄 EnvironmentManager.php
   - 📁 Middleware/
      - 📄 canInstall.php
      - 📄 canUpdate.php
   - 📁 Providers/
      - 📄 LaravelInstallerServiceProvider.php
   - 📁 Routes/
      - 📄 web.php
   - 📁 Views/
      - 📁 layouts/
         - 📄 master-update.blade.php
         - 📄 master.blade.php
      - 📁 update/
         - 📄 finished.blade.php
         - 📄 overview.blade.php
      - 📄 environment-classic.blade.php
      - 📄 environment-wizard.blade.php
   - 📁 assets/
      - 📁 css/
         - 📄 style.css
         - 📄 style.css.map
      - 📁 fonts/
         - 📄 FontAwesome.otf
         - 📄 fontawesome-webfont.eot
      - 📁 img/
         - 📁 favicon/
            - 📄 favicon-16x16.png
            - 📄 favicon-32x32.png
         - 📄 background.png
         - 📄 pattern.png
- 📄 LICENSE
- 📄 README.md
```

---

## ⚙️ Configuration

The published configuration file can be found at:

```
config/installer.php
```

This allows you to customize checks and paths, such as setting the SQL dump path for import during installation.

---

## 📥 Purchase Code Validation

If your system requires purchase code validation, you can customize the validation logic in the `validatePurchaseCode` function. Here’s an example:

```php
if (!$this->validatePurchaseCode($code)) {
    throw new Exception('Invalid purchase code.');
}
```

---

## 📷 Screenshots

| **Welcome Screen**         | **Database Setup**           | **Purchase Code Validation** |
|----------------------------|------------------------------|------------------------------|
| ![Welcome](path_to_screenshot) | ![DB Setup](path_to_screenshot) | ![Purchase Code](path_to_screenshot) |

---

## 🔧 System Requirements

To ensure the Laravel Installer works as expected, your environment must meet the following requirements:

- **PHP**: 8.0 or higher
- **Laravel**: 9.0 or higher
- **PHP Extensions**:
  - `mbstring`
  - `openssl`
  - `pdo`
  - `tokenizer`
  - `xml`
  - `ctype`
  - `json`

---
## Force Publishing 
```bash
php artisan vendor:publish --tag=laravelinstaller --force
```
## 🤝 Contributing

Contributions are welcome! If you want to contribute:

1. Fork the repository.
2. Create a new feature branch: `git checkout -b feature-branch`.
3. Commit your changes: `git commit -m 'Add new feature'`.
4. Push to the branch: `git push origin feature-branch`.
5. Open a pull request on GitHub.

---

## 📝 License

This package is licensed under the [MIT license](LICENSE.md).

---

## 📬 Support

For support, feel free to open an issue on the [GitHub repository](https://github.com/jmrashed/laravel-installer/issues) or contact us directly.
 