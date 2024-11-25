-- Use the existing database
USE bus_booking;

-- Drop tables if they already exist for a fresh start
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS buses;
DROP TABLE IF EXISTS admins;
DROP TABLE IF EXISTS users;

-- Table for users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,  -- Fixed typo 'passwword' to 'password'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table for admins
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL  -- Fixed typo 'passwword' to 'password'
);

-- Table for buses (with price_per_seat and departure_date added)
CREATE TABLE IF NOT EXISTS buses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bus_name VARCHAR(100) NOT NULL,
    departure VARCHAR(100) NOT NULL,
    destination VARCHAR(100) NOT NULL,
    departure_date DATE NOT NULL,  -- Added departure_date column
    departure_time TIME NOT NULL,
    arrival_time TIME NOT NULL,
    seats_available INT NOT NULL,
    price_per_seat DECIMAL(10, 2) NOT NULL,  -- Added column for seat price
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table for bookings (with ticket_id column that will be generated after payment)
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    bus_id INT NOT NULL,
    seats_booked INT NOT NULL,
    payment_status ENUM('Pending', 'Paid') DEFAULT 'Pending',
    ticket_id VARCHAR(255) DEFAULT NULL,
    booking_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (bus_id) REFERENCES buses(id)
);

-- Sample data for users
INSERT INTO users (username, email, password) VALUES
('alianakamal', 'alianaahmadkamal@gmail.com', MD5('123')),
('johnsmith', 'john.smith@example.com', MD5('password123'));

-- Sample data for admins
INSERT INTO admins (username, password) VALUES
('admin1', MD5('adminpassword123')),
('admin2', MD5('adminpassword456'));

-- Sample data for buses (with price_per_seat and departure_date values added)
INSERT INTO buses (bus_name, departure, destination, departure_date, departure_time, arrival_time, seats_available, price_per_seat) VALUES
('Bus A', 'City A', 'City B', '2024-12-01', '08:00:00', '12:00:00', 50, 10.00),
('Bus B', 'City A', 'City C', '2024-12-02', '09:00:00', '14:00:00', 40, 15.50),
('Bus C', 'City B', 'City D', '2024-12-03', '10:00:00', '15:00:00', 30, 12.00);

-- Sample data for bookings
INSERT INTO bookings (user_id, bus_id, seats_booked, payment_status, ticket_id) VALUES
(1, 1, 2, 'Paid', 'TICKET-ABC123'),
(1, 2, 1, 'Pending', NULL),
(2, 2, 2, 'Paid', 'TICKET-XYZ456');

-- View for tickets (including price_per_seat and amount calculation)
CREATE OR REPLACE VIEW view_ticket AS
SELECT 
    b.id AS booking_id,
    u.username AS user_name,
    bu.bus_name,
    bu.departure,
    bu.destination,
    bu.departure_date,  -- Added departure_date to the view
    bu.departure_time,
    bu.arrival_time,
    b.seats_booked,
    b.payment_status,
    b.booking_time,
    bu.price_per_seat, -- Include price per seat
    (bu.price_per_seat * b.seats_booked) AS amount, -- Calculate total amount
    b.ticket_id  
FROM bookings b
JOIN users u ON b.user_id = u.id
JOIN buses bu ON b.bus_id = bu.id;

