<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('send-mail', function () {
    $to = 'hansmadume@gmail.com';

    try {
        Mail::raw('Congrats for sending test email with Mailtrap!', function ($message) use ($to) {
            $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME', 'UMS'))
                ->to($to)
                ->subject('You are awesome!');
        });

        $this->info("Test email sent successfully to {$to}");
    } catch (\Throwable $e) {
        $this->error('Failed to send email: ' . $e->getMessage());
    }
})->purpose('Send test email via configured SMTP');
