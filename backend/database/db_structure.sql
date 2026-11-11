-- =====================================================================
--  Coral Bay Villa, Mirissa — Room Booking System
--  IT1208 Web Technologies — CA2
--  Database structure + sample records (MySQL 5.7+ / MariaDB 10.3+)
--
--  Import with phpMyAdmin (Import tab) or:
--      mysql -u root -p < db_structure.sql
-- =====================================================================

DROP DATABASE IF EXISTS coral_bay_villa;
CREATE DATABASE coral_bay_villa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE coral_bay_villa;

-- ---------------------------------------------------------------------
-- 1. users — guests and administrators
-- ---------------------------------------------------------------------
CREATE TABLE users (
    user_id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(100)  NOT NULL,
    email           VARCHAR(150)  NOT NULL UNIQUE,
    contact_number  VARCHAR(20)   NOT NULL,
    password        VARCHAR(255)  NOT NULL,              -- password_hash() output
    role            ENUM('guest','admin') NOT NULL DEFAULT 'guest',
    created_at      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 2. rooms — room catalogue managed by the administrator
-- ---------------------------------------------------------------------
CREATE TABLE rooms (
    room_id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    room_name        VARCHAR(100)  NOT NULL,
    room_type        ENUM('Standard','Deluxe','Suite','Family') NOT NULL,
    description      TEXT          NOT NULL,
    facilities       VARCHAR(255)  NOT NULL DEFAULT '',   -- comma-separated list
    price_per_night  DECIMAL(10,2) NOT NULL CHECK (price_per_night > 0),
    max_guests       TINYINT UNSIGNED NOT NULL CHECK (max_guests BETWEEN 1 AND 10),
    image            VARCHAR(255)  NOT NULL DEFAULT 'default.svg',
    room_status      ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 3. bookings — links a registered guest to a room for a date range
-- ---------------------------------------------------------------------
CREATE TABLE bookings (
    booking_id      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED  NOT NULL,
    room_id         INT UNSIGNED  NOT NULL,
    check_in        DATE          NOT NULL,
    check_out       DATE          NOT NULL,
    num_guests      TINYINT UNSIGNED NOT NULL DEFAULT 1,
    nights          SMALLINT UNSIGNED NOT NULL,
    total_amount    DECIMAL(10,2) NOT NULL,
    special_request VARCHAR(500)  NULL,
    booking_status  ENUM('Pending','Confirmed','Cancelled') NOT NULL DEFAULT 'Pending',
    booking_date    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_booking_user FOREIGN KEY (user_id) REFERENCES users(user_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_booking_room FOREIGN KEY (room_id) REFERENCES rooms(room_id)
        ON DELETE RESTRICT ON UPDATE CASCADE,          -- rooms with bookings are deactivated, not deleted
    CONSTRAINT chk_dates CHECK (check_out > check_in),
    INDEX idx_room_dates (room_id, booking_status, check_in, check_out)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 4. room_images — extra gallery images per room (extension)
-- ---------------------------------------------------------------------
CREATE TABLE room_images (
    image_id    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    room_id     INT UNSIGNED NOT NULL,
    file_name   VARCHAR(255) NOT NULL,
    caption     VARCHAR(150) NULL,
    CONSTRAINT fk_image_room FOREIGN KEY (room_id) REFERENCES rooms(room_id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 5. reviews — guest ratings, only for confirmed stays (extension)
-- ---------------------------------------------------------------------
CREATE TABLE reviews (
    review_id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id  INT UNSIGNED NOT NULL UNIQUE,              -- one review per booking
    user_id     INT UNSIGNED NOT NULL,
    room_id     INT UNSIGNED NOT NULL,
    rating      TINYINT UNSIGNED NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment     VARCHAR(1000) NOT NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_review_booking FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE,
    CONSTRAINT fk_review_user    FOREIGN KEY (user_id)    REFERENCES users(user_id)       ON DELETE CASCADE,
    CONSTRAINT fk_review_room    FOREIGN KEY (room_id)    REFERENCES rooms(room_id)       ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 6. seasonal_rates — price multipliers for date ranges (extension)
-- ---------------------------------------------------------------------
CREATE TABLE seasonal_rates (
    season_id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    season_name      VARCHAR(60)  NOT NULL,
    start_date       DATE         NOT NULL,
    end_date         DATE         NOT NULL,                -- inclusive
    rate_multiplier  DECIMAL(4,2) NOT NULL CHECK (rate_multiplier > 0),
    CONSTRAINT chk_season_dates CHECK (end_date >= start_date)
) ENGINE=InnoDB;

-- =====================================================================
--  SAMPLE DATA (fictional)
-- =====================================================================

-- Passwords:  admin@coralbay.lk -> Admin@123 | all guests -> Guest@123
INSERT INTO users (name, email, contact_number, password, role) VALUES
('Villa Administrator', 'admin@coralbay.lk',  '0771234567', '$2y$10$hoZcXWeECaVCzvexbELkN.ZlKEjao2SO0FMaX0kOd0TXf6H5BflFO', 'admin'),
('Nimal Perera',        'nimal@example.com',  '0712345678', '$2y$10$bdaeHT6tJHUJgLXYLXewWurDBJ1y1lI2zXNS51/2DJkNJAco3gZce', 'guest'),
('Emma Schmidt',        'emma@example.com',   '0765432109', '$2y$10$bdaeHT6tJHUJgLXYLXewWurDBJ1y1lI2zXNS51/2DJkNJAco3gZce', 'guest'),
('Kasun Fernando',      'kasun@example.com',  '0759876543', '$2y$10$bdaeHT6tJHUJgLXYLXewWurDBJ1y1lI2zXNS51/2DJkNJAco3gZce', 'guest');

INSERT INTO rooms (room_name, room_type, description, facilities, price_per_night, max_guests, image, room_status) VALUES
('Ocean Breeze Room', 'Standard',
 'A bright, airy room a short stroll from the sand. Ideal for couples who want a calm base for whale watching and beach days in Mirissa.',
 'Air conditioning,Free Wi-Fi,Hot water,Garden view,Mini fridge', 6500.00, 2, 'room-ocean-breeze.jpg', 'active'),
('Coconut Grove Deluxe', 'Deluxe',
 'Spacious deluxe room opening onto the coconut grove with a private sit-out. Listen to the waves while you enjoy breakfast outside.',
 'Air conditioning,Free Wi-Fi,Private balcony,Smart TV,Mini fridge,Breakfast included', 10000.00, 3, 'room-coconut-grove.jpg', 'active'),
('Sunset Sea-View Suite', 'Suite',
 'Our signature suite with floor-to-ceiling windows facing the Indian Ocean. Watch the sun set over Parrot Rock from your private terrace.',
 'Sea view,Air conditioning,Free Wi-Fi,Private terrace,Bathtub,Smart TV,Breakfast included', 15000.00, 2, 'room-sunset-suite.jpg', 'active'),
('Lagoon Family Room', 'Family',
 'Two connected bedrooms with a shared lounge, perfect for families. Close to the pool and the shallow end of the beach.',
 'Air conditioning,Free Wi-Fi,Two bedrooms,Pool access,Kitchenette,Smart TV', 17500.00, 5, 'room-lagoon-family.jpg', 'active'),
('Palm Shade Room', 'Standard',
 'Cosy budget-friendly room tucked under the palms, with everything you need for a relaxed coastal getaway.',
 'Fan,Free Wi-Fi,Hot water,Garden view', 7500.00, 2, 'room-palm-shade.jpg', 'active'),
('Coral Reef Deluxe', 'Deluxe',
 'Elegant deluxe room inspired by the colours of the reef, with a rain shower and a partial ocean view.',
 'Partial sea view,Air conditioning,Free Wi-Fi,Rain shower,Mini fridge', 16000.00, 3, 'room-coral-reef.jpg', 'inactive');

INSERT INTO room_images (room_id, file_name, caption) VALUES
(1, 'room-ocean-breeze.jpg', 'Ocean Breeze Room'),
(1, 'villa-beach.jpg',       'Two minutes to the beach'),
(2, 'room-coconut-grove.jpg','Coconut Grove Deluxe'),
(2, 'villa-pool.jpg',        'Infinity pool'),
(3, 'room-sunset-suite.jpg', 'Sunset Sea-View Suite'),
(3, 'villa-beach.jpg',       'Sunset over Mirissa bay'),
(4, 'room-lagoon-family.jpg','Lagoon Family Room'),
(4, 'villa-pool.jpg',        'Family pool area'),
(5, 'room-palm-shade.jpg',   'Palm Shade Room');

-- Seasons for the south coast (peak Dec–Apr)
INSERT INTO seasonal_rates (season_name, start_date, end_date, rate_multiplier) VALUES
('Festive Peak',  '2026-12-20', '2027-01-05', 1.25),
('High Season',   '2027-01-06', '2027-04-15', 1.15),
('Green Season',  '2027-05-01', '2027-10-31', 0.85);

-- Bookings (mix of statuses, including past stays used for reviews)
INSERT INTO bookings (user_id, room_id, check_in, check_out, num_guests, nights, total_amount, booking_status, booking_date) VALUES
(2, 1, '2026-10-10', '2026-10-13', 2, 3, 19500.00, 'Confirmed', '2026-09-28 10:15:00'),
(3, 3, '2026-10-20', '2026-10-24', 2, 4, 60000.00, 'Confirmed', '2026-10-01 18:40:00'),
(2, 2, '2026-11-20', '2026-11-23', 2, 3, 30000.00, 'Confirmed', '2026-11-01 09:05:00'),
(3, 4, '2026-11-25', '2026-11-28', 4, 3, 52500.00, 'Pending',   '2026-11-05 14:22:00'),
(4, 3, '2026-12-01', '2026-12-04', 2, 3, 45000.00, 'Pending',   '2026-11-06 11:30:00'),
(4, 5, '2026-11-15', '2026-11-17', 1, 2, 15000.00, 'Cancelled', '2026-11-02 16:45:00');

INSERT INTO reviews (booking_id, user_id, room_id, rating, comment) VALUES
(1, 2, 1, 5, 'Lovely clean room and very friendly staff. The beach is literally a two-minute walk.'),
(2, 3, 3, 4, 'The sunset view from the terrace was unforgettable. Breakfast could have more variety.');
