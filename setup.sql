-- KASITRADE DATABASE SETUP
-- Run this in phpMyAdmin to create all tables and sample data

--CREATE DATABASE IF NOT EXISTS kasitrade CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
--USE kasitrade;

-- ROLES
CREATE TABLE roles (
    role_id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE,
    permissions JSON NOT NULL,
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO roles (role_name, permissions, description) VALUES
('admin', '["manage_users", "moderate_listings", "handle_disputes", "view_analytics", "manage_roles", "manage_pickup_points"]', 'Full admin access'),
('seller', '["post_listings", "manage_own_listings", "view_messages", "respond_to_messages", "view_sales", "confirm_delivery"]', 'Can sell goods'),
('buyer', '["browse_listings", "purchase", "send_messages", "rate_review", "report", "view_purchase_history"]', 'Can buy goods'),
('moderator', '["moderate_listings", "view_reports", "handle_disputes", "view_analytics"]', 'Limited admin access');

-- USERS
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone_number VARCHAR(20),
    sa_id_number VARCHAR(13),
    id_verified BOOLEAN DEFAULT FALSE,
    profile_image VARCHAR(255),
    township VARCHAR(100) NOT NULL,
    role_id INT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(role_id) ON DELETE RESTRICT
) ENGINE=InnoDB;

INSERT INTO users (username, email, password_hash, first_name, last_name, phone_number, sa_id_number, id_verified, township, role_id, is_active) VALUES
('admin_jean', 'jean@kasitrade.co.za', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jean', 'Labuschagne', '0821234567', '9001015001087', TRUE, 'Soweto', 1, TRUE),
('seller_thabo', 'thabo@email.co.za', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Thabo', 'Mokoena', '0832345678', '8802155001088', TRUE, 'Alexandra', 2, TRUE),
('seller_lerato', 'lerato@email.co.za', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Lerato', 'Dlamini', '0843456789', '9503305001089', TRUE, 'Khayelitsha', 2, TRUE),
('buyer_sipho', 'sipho@email.co.za', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sipho', 'Ngcobo', '0854567890', NULL, FALSE, 'Soweto', 3, TRUE),
('buyer_nomsa', 'nomsa@email.co.za', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nomsa', 'Buthelezi', '0865678901', NULL, FALSE, 'Tembisa', 3, TRUE);

-- CATEGORIES
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL,
    description VARCHAR(255),
    parent_id INT NULL,
    icon VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES categories(category_id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO categories (category_name, description, parent_id, icon) VALUES
('Electronics', 'Phones, laptops, accessories', NULL, 'electronics.png'),
('Clothing', 'Men, women, children fashion', NULL, 'clothing.png'),
('Home & Garden', 'Furniture, appliances, decor', NULL, 'home.png'),
('Beauty & Health', 'Cosmetics, hair products, wellness', NULL, 'beauty.png'),
('Food & Beverages', 'Fresh produce, packaged goods', NULL, 'food.png'),
('Services', 'Hair styling, repairs, tutoring', NULL, 'services.png'),
('Mobile Phones', 'Smartphones and feature phones', 1, 'phones.png'),
('Laptops & Computers', 'New and refurbished computers', 1, 'laptops.png'),
('Men Clothing', 'Shirts, pants, shoes', 2, 'mens.png'),
('Women Clothing', 'Dresses, tops, accessories', 2, 'womens.png');

-- PICKUP POINTS
CREATE TABLE pickup_points (
    pickup_point_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    address VARCHAR(255) NOT NULL,
    township VARCHAR(100) NOT NULL,
    contact_person VARCHAR(100),
    contact_phone VARCHAR(20),
    qr_code VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO pickup_points (name, address, township, contact_person, contact_phone, qr_code, is_active) VALUES
('Soweto Spaza Central', '123 Vilakazi Street, Orlando West', 'Soweto', 'Mr. Khumalo', '0821112222', 'QR_SOW_001', TRUE),
('Alexandra Taxi Rank Hub', '45 3rd Avenue, Alex Mall', 'Alexandra', 'Mrs. Molefe', '0832223333', 'QR_ALX_001', TRUE),
('Khayelitsha Church Point', '78 Site C, Methodist Church', 'Khayelitsha', 'Pastor Dube', '0843334444', 'QR_KHY_001', TRUE),
('Tembisa Boxer Store', 'Boxer Superstore, Main Road', 'Tembisa', 'Manager Nkosi', '0854445555', 'QR_TEM_001', TRUE),
('Mamelodi Church Depot', '456 Tsamaya Road, Baptist Church', 'Mamelodi', 'Elder Mabena', '0865556666', 'QR_MAM_001', TRUE);

-- LISTINGS
CREATE TABLE listings (
    listing_id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    category_id INT NOT NULL,
    condition_status VARCHAR(20) DEFAULT 'new',
    image_paths JSON,
    status ENUM('active', 'pending', 'sold', 'suspended', 'deleted') DEFAULT 'pending',
    view_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE RESTRICT
) ENGINE=InnoDB;

INSERT INTO listings (seller_id, title, description, price, category_id, condition_status, image_paths, status, view_count) VALUES
(2, 'Samsung Galaxy A14 - Like New', 'Used for 3 months, still under warranty. Includes charger and case.', 2499.99, 7, 'used', '["uploads/listings/phone1_1.jpg", "uploads/listings/phone1_2.jpg"]', 'active', 45),
(2, 'HP Laptop 15s - Refurbished', 'Intel i5, 8GB RAM, 256GB SSD. Perfect for students and small business.', 4999.00, 8, 'refurbished', '["uploads/listings/laptop1_1.jpg"]', 'active', 32),
(3, 'Traditional Zulu Beaded Necklace', 'Handmade by local artisans. Perfect for cultural events and gifts.', 350.00, 10, 'new', '["uploads/listings/necklace1_1.jpg", "uploads/listings/necklace1_2.jpg"]', 'active', 78),
(3, 'Braiding Hair Extensions - 3 Packs', 'X-pression hair, 82 inches, color 1B. Great for box braids.', 180.00, 4, 'new', '["uploads/listings/hair1_1.jpg"]', 'active', 56),
(2, '2-Plate Gas Stove - Good Condition', 'Perfect for spaza shop or small kitchen. Includes gas pipe.', 450.00, 3, 'used', '["uploads/listings/stove1_1.jpg"]', 'pending', 12);

-- TRANSACTIONS
CREATE TABLE transactions (
    transaction_id INT AUTO_INCREMENT PRIMARY KEY,
    buyer_id INT NOT NULL,
    seller_id INT NOT NULL,
    listing_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    escrow_status ENUM('pending', 'held', 'released', 'refunded', 'disputed') DEFAULT 'pending',
    payment_method ENUM('payshap', 'cash', 'ozow', 'card') DEFAULT 'payshap',
    pickup_point_id INT,
    qr_code VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (buyer_id) REFERENCES users(user_id) ON DELETE RESTRICT,
    FOREIGN KEY (seller_id) REFERENCES users(user_id) ON DELETE RESTRICT,
    FOREIGN KEY (listing_id) REFERENCES listings(listing_id) ON DELETE RESTRICT,
    FOREIGN KEY (pickup_point_id) REFERENCES pickup_points(pickup_point_id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO transactions (buyer_id, seller_id, listing_id, amount, escrow_status, payment_method, pickup_point_id, qr_code, completed_at) VALUES
(4, 2, 1, 2499.99, 'released', 'payshap', 1, 'TXN_QR_001', '2026-05-20 14:30:00'),
(5, 3, 3, 350.00, 'held', 'payshap', 3, 'TXN_QR_002', NULL),
(4, 3, 4, 180.00, 'pending', 'cash', 1, 'TXN_QR_003', NULL),
(5, 2, 2, 4999.00, 'disputed', 'payshap', 2, 'TXN_QR_004', NULL);

-- MESSAGES
CREATE TABLE messages (
    message_id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    listing_id INT,
    content TEXT NOT NULL,
    is_voice_note BOOLEAN DEFAULT FALSE,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (listing_id) REFERENCES listings(listing_id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO messages (sender_id, receiver_id, listing_id, content, is_voice_note, is_read) VALUES
(4, 2, 1, 'Hi Thabo, is the phone still available? Can I get a discount?', FALSE, TRUE),
(2, 4, 1, 'Yes it is available! I can do R2200 final price. Meet at Soweto Spaza?', FALSE, TRUE),
(4, 2, 1, 'Deal! I will pay via PayShap. When can we meet?', FALSE, FALSE),
(5, 3, 3, 'Lerato, does the necklace come in other colors?', FALSE, TRUE),
(3, 5, 3, 'Yes! I have red, blue, and green. Which do you prefer?', FALSE, FALSE);

-- REVIEWS
CREATE TABLE reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    reviewer_id INT NOT NULL,
    reviewee_id INT NOT NULL,
    listing_id INT,
    transaction_id INT,
    rating INT NOT NULL,
    comment TEXT,
    is_moderated BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_rating CHECK (rating >= 1 AND rating <= 5),
    FOREIGN KEY (reviewer_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (reviewee_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (listing_id) REFERENCES listings(listing_id) ON DELETE SET NULL,
    FOREIGN KEY (transaction_id) REFERENCES transactions(transaction_id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO reviews (reviewer_id, reviewee_id, listing_id, transaction_id, rating, comment, is_moderated) VALUES
(4, 2, 1, 1, 5, 'Excellent seller! Phone was exactly as described. Quick pickup at Soweto Spaza.', TRUE),
(5, 3, 3, 2, 4, 'Beautiful necklace, great quality. Delivery took a day longer than expected.', TRUE),
(4, 3, 4, 3, 5, 'Lerato is amazing! Hair extensions are top quality. Will buy again!', FALSE);

-- REPORTS
CREATE TABLE reports (
    report_id INT AUTO_INCREMENT PRIMARY KEY,
    reporter_id INT NOT NULL,
    reported_user_id INT,
    listing_id INT,
    report_type ENUM('fraud', 'fake_listing', 'harassment', 'no_show', 'counterfeit', 'other') NOT NULL,
    reason TEXT NOT NULL,
    status ENUM('pending', 'investigating', 'resolved', 'dismissed') DEFAULT 'pending',
    admin_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL,
    FOREIGN KEY (reporter_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (reported_user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (listing_id) REFERENCES listings(listing_id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO reports (reporter_id, reported_user_id, listing_id, report_type, reason, status, admin_notes) VALUES
(4, 2, 2, 'fake_listing', 'Seller claims laptop is i5 but it is actually i3. False advertising.', 'investigating', 'Contacted seller for proof of specs. Awaiting response.'),
(5, NULL, 5, 'counterfeit', 'These hair extensions are fake X-pression brand. Poor quality.', 'pending', NULL);

-- DISPUTES
CREATE TABLE disputes (
    dispute_id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id INT NOT NULL,
    dispute_type ENUM('no_delivery', 'failed_meetup', 'fraud', 'damaged_goods', 'wrong_item', 'other') NOT NULL,
    status ENUM('open', 'mediating', 'resolved_buyer', 'resolved_seller', 'refunded', 'closed') DEFAULT 'open',
    description TEXT NOT NULL,
    resolution TEXT,
    admin_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL,
    FOREIGN KEY (transaction_id) REFERENCES transactions(transaction_id) ON DELETE RESTRICT,
    FOREIGN KEY (admin_id) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO disputes (transaction_id, dispute_type, status, description, resolution, admin_id, resolved_at) VALUES
(4, 'failed_meetup', 'mediating', 'Seller did not show up at agreed pickup point. Buyer waited 2 hours.', NULL, 1, NULL),
(2, 'damaged_goods', 'resolved_buyer', 'Necklace arrived with broken clasp. Seller agreed to repair.', 'Seller will repair and redeliver. Escrow released to seller after confirmation.', 1, '2026-05-22 10:00:00');

-- INDEXES
CREATE INDEX idx_listings_seller ON listings(seller_id);
CREATE INDEX idx_listings_category ON listings(category_id);
CREATE INDEX idx_listings_status ON listings(status);
CREATE INDEX idx_transactions_buyer ON transactions(buyer_id);
CREATE INDEX idx_transactions_seller ON transactions(seller_id);
CREATE INDEX idx_transactions_status ON transactions(escrow_status);
CREATE INDEX idx_messages_sender ON messages(sender_id);
CREATE INDEX idx_messages_receiver ON messages(receiver_id);
CREATE INDEX idx_reviews_reviewee ON reviews(reviewee_id);
CREATE INDEX idx_reports_status ON reports(status);
CREATE INDEX idx_disputes_status ON disputes(status);

-- VIEWS
CREATE VIEW vw_seller_ratings AS
SELECT u.user_id, u.username, u.first_name, u.last_name,
       COUNT(r.review_id) as total_reviews,
       AVG(r.rating) as average_rating,
       COUNT(CASE WHEN r.rating = 5 THEN 1 END) as five_star_count
FROM users u
LEFT JOIN reviews r ON u.user_id = r.reviewee_id
WHERE u.role_id = 2
GROUP BY u.user_id;

CREATE VIEW vw_platform_analytics AS
SELECT 
    (SELECT COUNT(*) FROM users WHERE role_id = 3) as total_buyers,
    (SELECT COUNT(*) FROM users WHERE role_id = 2) as total_sellers,
    (SELECT COUNT(*) FROM listings WHERE status = 'active') as active_listings,
    (SELECT COUNT(*) FROM transactions WHERE escrow_status = 'released') as completed_transactions,
    (SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE escrow_status = 'released') as total_revenue,
    (SELECT COUNT(*) FROM disputes WHERE status = 'open') as open_disputes;
