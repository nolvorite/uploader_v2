# Laravel 5.5 based system for storing files, registration and Stripe payments

In simple words, it's a mini Dropbox or Google Drive, built with Laravel.

It is a demo project for demonstrating what can be generated with [QuickAdminPanel](https://quickadminpanel.com) tool.

Features:
- Multi-tenancy system: Users can register and upload files/folders, they only see their own entries (admin sees everything)
- Real URLs of the files are hidden under UUID-based URLs for download
- Users can upload up to 5 files, after that they can subscribe to premium plan (payment via Stripe)

![File_storage screenshot](http://webcoderpro.com/file-storage-demo-01.png)

![File_storage_screenshot_02](http://webcoderpro.com/file-storage-demo-02.png)

## How to use

- Clone the repository with __git clone__
- Copy __.env.example__ file to __.env__ and edit database/Stripe credentials there
- Run __composer install__
- Run __php artisan key:generate__
- Run __php artisan migrate --seed__ (it has some seeded data for your testing)
- That's it: launch the main URL and login with default credentials __admin@admin.com__ - __password__

## License

Basically, feel free to use and re-use any way you want.