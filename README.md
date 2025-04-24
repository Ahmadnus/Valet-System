<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[WebReinvent](https://webreinvent.com/)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Jump24](https://jump24.co.uk)**
- **[Redberry](https://redberry.international/laravel/)**
- **[Active Logic](https://activelogic.com)**
- **[byte5](https://byte5.de)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).


🚗 Valet Parking System (Laravel Backend)
Built on top of the Laravel framework to manage smart valet operations.

📚 Table of Contents

Overview

Features

Tech Stack

Notifications

Getting Started

Contributing

License

Overview
The Valet Parking System is a Laravel-based backend solution that supports electronic and traditional valet parking services. It handles authentication, parking requests, guest user handling, role-based actions, payments, notifications, and real-time features. The platform supports six user roles, each with specific permissions.

Features
Authentication via phone number with confirmation through a Telegram gateway

User roles & permissions:

Valet Customer

Valet Guest

Valet Agent

Valet Host

Valet Provider

Platform Manager

Car management by users (create, update, delete, upload car images)

Parking request system (electronic & traditional)

Payment integration (Stripe & cash)

Loyalty points system (1 point per $1 spent; 10 points = $1 discount)

Fee waiver request system (approved by Host or Provider)

Real-time system updates:

Notifications via Firebase Cloud Messaging (FCM)

Live chat (Pusher)

Scheduled jobs for system automation:

Cancel pending requests after 30 minutes

Cancel unresponded fee waiver requests after 5 minutes

Multilingual support via Astrotomic Translatable

Media management via Spatie Media Library

Role-based access using Spatie Permission

Tech Stack
Laravel (PHP framework)

MySQL (Database)

Firebase Cloud Messaging (FCM)

Pusher (for real-time chat)

Telegram gateway (SMS replacement)

Stripe (payment)

Spatie Media Library & Spatie Permission

Astrotomic Translatable (multilingual support)

Notifications
Notifications are handled using Firebase Cloud Messaging (FCM), integrated with queued jobs for background delivery. The system sends notifications for events such as:

Parking service requests

Car retrieval requests

Rating notifications

Payment confirmations

Fee waiver requests and responses

Request status changes

Tokens are managed per user, stored in an fcm_token table. Enum classes like NotificationType and TopicType are used to structure and categorize notifications.

Getting Started
To get the project up and running locally, follow the steps below:

1-Clone the Repository:

git clone https://gitlab.com/blankdiff2/valet-system
cd valet-parking-system

2-Install Dependencies Install the necessary PHP dependencies using Composer:
composer install

3-Set Up the Environment Copy the .env.example file to .env:
cp .env.example .env

4-Generate Application Key Laravel requires an application key, which you can generate by running:

php artisan key:generate

5-Set Up the Database

Create a database in MySQL for the project.

Update the .env file with your database credentials.

6-Run Migrations After setting up the database, run the migrations to create the necessary tables:
php artisan migrate

7-Run the Application You can now serve the application locally using the following command:

php artisan serve
