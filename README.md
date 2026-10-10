# LIGTriage

Walk-in triage queue for the CHO. A companion system of LIGTAS: same branding, separate app, separate database, no connection to LIGTAS.

Two sides:
- **Patient (no login):** scans the entrance QR, gets a queue number, fills a short symptom form, watches their status.
- **CHO staff (login):** live queue board, confirm or change priority, call next, add patients without a phone, daily summary, staff accounts (admin).

## 1. Set up

Needs PHP 8.1+, Composer, MySQL (XAMPP is fine).

1. Create a fresh Laravel project (10 or 11):
   `composer create-project laravel/laravel ligtriage`
2. Inside it:
   `composer require simplesoftwareio/simple-qrcode`
3. Unzip this package over the project folder. Say yes to replacing `routes/web.php` and `database/seeders/DatabaseSeeder.php`.
4. In phpMyAdmin create an empty database called `ligtriage`.
5. Edit `.env`:
   ```
   APP_NAME=LIGTriage
   DB_CONNECTION=mysql
   DB_DATABASE=ligtriage
   DB_USERNAME=root
   DB_PASSWORD=
   ```
6. Set the timezone (ticket numbers restart every day, so this matters):
   - Laravel 11: add `APP_TIMEZONE=Asia/Manila` to `.env`
   - Laravel 10: in `config/app.php` set `'timezone' => 'Asia/Manila'`
7. `php artisan migrate --seed`
8. Optional sample data for demos: `php artisan db:seed --class=DemoTicketsSeeder`
9. Start it so phones can reach it:
   `php artisan serve --host=0.0.0.0 --port=8000`

## 2. Open it with your LAN address, not localhost

Run `ipconfig` and note your IPv4 address (example 192.168.1.20). Open `http://192.168.1.20:8000` on the laptop too. The QR code is built from the address you used, so a phone cannot scan a `localhost` link.
Phones must be on the same WiFi. If they cannot connect, allow PHP through Windows Firewall (private network).

## 3. Accounts (change these before real use)

| Role | Email | Password |
|---|---|---|
| Admin | admin@ligtriage.test | password |
| Triage staff | staff@ligtriage.test | password |

Admins can add or remove accounts under Staff accounts.

## 4. How a visit works

1. Staff log in and open **Entrance screen** (new tab, full screen on the monitor).
2. Patient scans the QR with the phone camera and gets a number.
3. Patient answers the form (danger signs, symptoms, how long). The system suggests Emergency, Urgent or Routine.
4. Staff see the patient on **Queue**, open View, then confirm or change the priority (a reason is needed when changing it).
5. Staff press **Call next patient** (or Call on a row). The patient's phone shows "It is your turn".
6. Staff press Done, No show or Back to queue.
7. **Daily summary** shows totals, waits, priority counts, hourly arrivals and how often staff kept the suggestion.

Patients without a phone: **Add patient** on the staff menu.

## 5. QR behaviour

- One QR works for one scan and lasts 2 minutes. The screen swaps it 5 seconds early.
- After a scan the screen flashes "Ticket issued" and shows a new QR.
- A used or old QR shows an "expired" page.
- The same phone scanning again reopens its ticket instead of making a new one.

## 6. Priority rules (edit in app/Support/TriageRules.php)

- **Emergency:** any danger sign ticked.
- **Urgent:** fever in an infant or senior, fever for 3 days or more, vomiting or diarrhea for 3 days or more, fever with rash, pregnant with stomach pain, fever or vomiting, or a wound or injury.
- **Routine:** everything else.

These rules are an example screening aid. Have a CHO nurse or doctor review them before real use. Staff always make the final decision.

Questions and options live in `config/triage.php`.

## 7. Where things are

- `app/Http/Controllers` AuthController, EntranceController, ScanController, TicketController (patient), StaffQueueController, StaffTicketController, SummaryController, UserController
- `app/Models` QueueTicket, QrToken
- `app/Support/TriageRules.php` priority rules
- `resources/views` layout, entrance, patient/ticket, staff/*, auth/login
- `public/css/ligtriage.css` colors and styles (LIGTAS blue and amber)
- `public/images` LIGTAS logo

## 8. Troubleshooting

- Phone cannot open the QR link: you opened the entrance with `localhost`. Use the LAN IP.
- "Class QrCode not found": run `composer require simplesoftwareio/simple-qrcode`, then `php artisan optimize:clear`.
- Column not found `role`: run `php artisan migrate`.
- Numbers or dates look wrong: check the timezone step.
- Board looks stale after code changes: `php artisan view:clear`.

## 9. Privacy notes for the defense

- Patients need no account. Name is optional. The ticket link uses a long random token.
- Patients tick a consent box before sending answers.
- Only logged-in staff can see answers. Old unfinished tickets are cancelled automatically.
