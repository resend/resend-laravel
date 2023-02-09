# Resend for Laravel

[![Tests](https://img.shields.io/github/actions/workflow/status/jayanratna/resend-laravel/tests.yml?label=tests&style=for-the-badge&labelColor=000000)](https://github.com/jayanratna/resend-laravel/actions/workflows/tests.yml)
[![Packagist Downloads](https://img.shields.io/packagist/dt/resend/laravel?style=for-the-badge&labelColor=000000)](https://packagist.org/packages/resend/laravel)
[![Packagist Version](https://img.shields.io/packagist/v/resend/laravel?style=for-the-badge&labelColor=000000)](https://packagist.org/packages/resend/laravel)
[![License](https://img.shields.io/github/license/jayanratna/resend-laravel?color=9cf&style=for-the-badge&labelColor=000000)](https://github.com/jayanratna/resend-laravel/blob/main/LICENSE)

---

Provides Resend integration for Laravel and Symfony Mailer.

## Getting started

> **Requires [PHP 8.1+](https://php.net/releases/)**

First install Resend for Laravel via the [Composer](https://getcomposer.org/) package manager:

```bash
composer require resend/laravel
```

Next, you should configure your [Resend API key](https://resend.com/api-keys) in your application's `.env` file:

```ini
RESEND_API_KEY=re_123456789
```

Finally, you may use the `Resend` facade to access the Resend API:

```php
use Resend\Laravel\Facades\Resend;

Resend::sendEmail([
    'from' => 'onboarding@resend.dev',
    'to' => 'user@gmail.com',
    'subject' => 'hello world',
    'text' => 'it works!',
]);
```
