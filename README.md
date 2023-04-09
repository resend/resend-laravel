# Resend for Laravel

[![Tests](https://img.shields.io/github/actions/workflow/status/resendlabs/resend-laravel/tests.yml?label=tests&style=for-the-badge&labelColor=000000)](https://github.com/resendlabs/resend-laravel/actions/workflows/tests.yml)
[![Packagist Downloads](https://img.shields.io/packagist/dt/resend/resend-laravel?style=for-the-badge&labelColor=000000)](https://packagist.org/packages/resend/resend-laravel)
[![Packagist Version](https://img.shields.io/packagist/v/resend/resend-laravel?style=for-the-badge&labelColor=000000)](https://packagist.org/packages/resend/resend-laravel)
[![License](https://img.shields.io/github/license/resendlabs/resend-laravel?color=9cf&style=for-the-badge&labelColor=000000)](https://github.com/resendlabs/resend-laravel/blob/main/LICENSE)

---

Provides Resend integration for Laravel and Symfony Mailer.

> **Requires [PHP 8.1+](https://php.net/releases/)**

## Contents

- [Getting started](#getting-started)
  - [Using Resend's Laravel mailer](#using-resends-laravel-mailer)

## Getting started

First install Resend for Laravel via the [Composer](https://getcomposer.org/) package manager:

```bash
composer require resend/resend-laravel
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

### Using Resend's Laravel mailer

Resend for Laravel comes bundled with a Laravel mailer to make it easier to send emails. To start using the Resend mail transport, first create a new mailer definition within your application's `config/mail.php` configuration file:

```php
'resend' => [
    'transport' => 'resend',
],
```

> **Note**
> The Resend mailer will use the `RESEND_API_KEY` in your application's `.env` file.

Finally, update the `MAIL_MAILER` environment variable to use `resend`:

```ini
MAIL_MAILER=resend
```
