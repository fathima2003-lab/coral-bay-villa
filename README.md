# Coral Bay Villa – Room Booking System

A room booking website for **Coral Bay Villa**, a small (fictional) beachside villa on Beach Road, Mirissa.
Guests can look around the villa, check which rooms are free for their dates and send a booking request.
The villa's administrator then confirms or cancels each request from an admin panel.

I built this for **[Web Technologies]** my personal project.
 
**Repository:** https://github.com/fathima2003-lab/coral-bay-villa
**The site:** http://localhost/coral-bay-villa/

---

## Project overview

Small guest houses in places like Mirissa often take bookings over phone calls and WhatsApp, which
makes it easy to double-book a room. This project puts the whole process in one place:

- Guests browse rooms, pick dates and see the total price before they book.
- Every booking starts as **Pending**. Only the administrator can mark it **Confirmed** or **Cancelled**.
- A room can't be confirmed twice for the same nights. The system checks this both when the guest
  books and again when the admin confirms.

The site is built with plain PHP and MySQL (no framework), with Bootstrap for the layout and
vanilla JavaScript for validation and live updates.

---

## Features

### For guests
- **Home page** – introduction to the villa, facilities, featured rooms, photo gallery, guest reviews,
  location map and a quick availability search.
- **Rooms page** – all available rooms with photos, facilities, maximum guests and price per night.
  Rooms can be filtered by type, price, facility and number of guests.
- **Room details** – photo slideshow, full description, reviews and a live "is this room free?" check
  that updates as you change the dates.
- **Availability search** – enter check-in, check-out and number of guests to see only the rooms that are
  free for the whole stay, with the total price for each.
- **Booking form** – shows the number of nights and a price breakdown while you fill it in.
- **My Bookings** – a guest sees only their own bookings and their status, and can cancel a booking
  while it's still Pending.
- **Register / log in / log out.**

### For the administrator
- **Dashboard** – total bookings, pending requests, confirmed revenue, this month's occupancy,
  a chart of bookings per month and a list of arrivals in the next two weeks.
- **Bookings** – view all reservations, filter them, and confirm or cancel requests. A "Clash" label
  warns when a pending request overlaps a booking that is already confirmed.
- **Rooms** – add, edit, deactivate and delete rooms, including uploading a room photo.
  Rooms that already have bookings can only be deactivated, so old booking records stay intact.

### Extra features
- Live availability check using AJAX (`backend/api/availability.php` returns JSON).
- Search and filters for rooms.
- Photo gallery with a pop-up viewer.
- Admin dashboard with statistics and a Chart.js chart.
- **Seasonal pricing:** Festive Peak +25%, High Season +15%, Green Season −15%.
- **Long-stay discount:** 10% off stays of 7 nights or more.
- Guest reviews and star ratings, allowed only after a completed, confirmed stay.
- Embedded location map (OpenStreetMap).

---

## Technologies used

| Part | Technology |
|---|---|
| Front-end | HTML5, CSS3, Bootstrap 5.3, Bootstrap Icons, vanilla JavaScript, Chart.js |
| Back-end | PHP 8 using PDO |
| Database | MySQL / MariaDB |
| Local server | XAMPP (Apache + MySQL) |

---

## Software requirements

- **XAMPP 8.x** or similar, with Apache, **PHP 8.0 or newer** and MySQL/MariaDB.
- A modern browser such as Chrome, Edge or Firefox.
- Nothing else needs installing. Bootstrap, the icons and Chart.js are already included in
  `frontend/assets/vendor/`, so the site works without internet. Only the Google Fonts and the map need a connection.

---

## Installation and configuration

1. **Copy the project** into your web folder:
   - Default XAMPP: `C:\xampp\htdocs\coral-bay-villa`
   - If you've changed Apache's `DocumentRoot`, copy it into that folder instead.

2. **Start Apache and MySQL** from the XAMPP Control Panel.

3. **Create the database:**
   - Open http://localhost/phpmyadmin and go to the **Import** tab.
   - Choose `backend/database/db_structure.sql` and click **Import**.
   - This creates the `coral_bay_villa` database with its tables and sample data.

4. **Open the site:** http://localhost/coral-bay-villa/

**Troubleshooting**
- *"Database connection failed"* – MySQL isn't running, or the SQL file hasn't been imported yet.
- *500 Internal Server Error* – make sure Apache allows `.htaccess` files, then restart Apache.
- *Room photos don't upload* – check that `frontend/assets/img/` isn't read-only.

---

## Login details (sample data)

| Role | Email | Password |
|---|---|---|
| Administrator | admin@coralbay.lk | Admin@123 |
| Guest | nimal@example.com | Guest@123 |
| Guest | emma@example.com | Guest@123 |
| Guest | kasun@example.com | Guest@123 |

The sample data includes bookings in all three states (Pending, Confirmed, Cancelled), a few reviews
and three pricing seasons. \For example, Nimal already has a confirmed booking for the Coconut Grove Deluxe
on 20–23 November 2026. If you search those dates, that room won't be shown.

---

## Project structure

The code is split into a **front-end** (what the browser sees) and a **back-end** (logic and database).
Each page has a small entry file in the main folder that runs its back-end controller first and then its front-end view.

```
coral-bay-villa/
├── index.php, rooms.php, room.php, search.php, book.php,   page entry points
│   my_bookings.php, register.php, login.php, logout.php
├── admin/                       admin page entry points
├── frontend/
│   ├── views/                   HTML templates (pages, layout, partials)
│   └── assets/                  CSS, JavaScript, images, Bootstrap, Chart.js
└── backend/
    ├── config/config.php        database settings and booking rules
    ├── core/                    database connection, sessions, login checks,
    │                            pricing and availability functions
    ├── controllers/             one file per page: handles forms and database queries
    ├── api/availability.php     JSON endpoint used by the live availability check
    └── database/db_structure.sql
```

### Database tables
`users`, `rooms`, `bookings`, `room_images`, `reviews`, `seasonal_rates`.
\Bookings link to a user and a room; reviews link to a booking.

---

## How some of the key parts work

- **Preventing double bookings:** two stays overlap when one starts before the other ends.
  In SQL: `existing.check_in < new.check_out AND existing.check_out > new.check_in`.
  Only confirmed bookings block a room. One guest can check out on the same day the next guest checks in.
  See `room_is_available()` in `backend/core/functions.php`.
- **Price:** the price is calculated night by night, using the seasonal rate for each night and then the
  long-stay discount. JavaScript shows an estimate on the booking form, but the final price is always
  recalculated in PHP when the booking is saved.
- **Validation:** forms are checked in the browser with JavaScript (dates, guest count, phone number,
  password match). Everything is checked again in PHP, so the site still works safely with JavaScript turned off.
- **Security:**
  - Passwords are stored with `password_hash()`.
  - All SQL uses prepared statements, and all output is escaped against XSS.
  - Every form carries a CSRF token, and the session ID changes on login and logout.
  - Guests can only see and cancel their own bookings, and the admin pages check the user's role.

---

## Known limitations

- No online payment. A booking is only a request until the villa confirms it.
- No email notifications. Guests check the status on the My Bookings page.
- Some of the room photos are low resolution, so they can look slightly soft on large screens.

---

## Acknowledgements

- [Bootstrap 5.3.3](https://getbootstrap.com) and [Bootstrap Icons 1.11.3](https://icons.getbootstrap.com) – MIT licence.
- [Chart.js 4.4.1](https://www.chartjs.org) – MIT licence.
- Google Fonts: Playfair Display and Inter – Open Font License.
- Map from [OpenStreetMap](https://www.openstreetmap.org) © OpenStreetMap contributors.
- Room and villa photos were taken from public hotel listings and are used only for this non-commercial
  university assignment. All rights belong to their owners: Booking.com (villa-pool, room-palm-shade,
  room-coral-reef), Agoda (room-coconut-grove), Expedia/Vrbo (room-ocean-breeze, villa-dining),
  Villa-Finder – The Beach House Mirissa (villa-beach), Saii Hotels (room-lagoon-family),
  and Google Images (villa-hero, room-sunset-suite).
- Coral Bay Villa, its address and all the guest details in the sample data are made up.

---

## Use of generative AI

I used Claude as a coding assistant. It helped me plan the folder structure, write parts of the
PHP, JavaScript and CSS, and draft this README. I tested the system myself on XAMPP.

**Done by:** MM Fathima Mina 
