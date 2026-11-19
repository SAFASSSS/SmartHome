-- SmartNest database schema (MySQL 5.7+ / MariaDB 10.3+)
CREATE DATABASE IF NOT EXISTS smartnest_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE smartnest_app;

CREATE TABLE IF NOT EXISTS buildings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  address VARCHAR(255) NOT NULL,
  floors INT NOT NULL DEFAULT 1,
  manager_id INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  phone VARCHAR(30) NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','manager','technician','resident') NOT NULL,
  status ENUM('pending','active','rejected') NOT NULL DEFAULT 'pending',
  building_id INT NULL,
  unit VARCHAR(20) NULL,
  specialty VARCHAR(50) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (building_id) REFERENCES buildings(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS flats (
  id INT AUTO_INCREMENT PRIMARY KEY,
  building_id INT NOT NULL,
  unit VARCHAR(20) NOT NULL,
  bedrooms TINYINT NOT NULL DEFAULT 1,
  area_sqft INT NOT NULL DEFAULT 700,
  listing_type ENUM('rent','sale','occupied') NOT NULL DEFAULT 'rent',
  price DECIMAL(12,2) NOT NULL DEFAULT 0,
  amenities VARCHAR(255) NULL,
  photo VARCHAR(255) NULL,
  FOREIGN KEY (building_id) REFERENCES buildings(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS complaints (
  id INT AUTO_INCREMENT PRIMARY KEY,
  resident_id INT NOT NULL,
  category ENUM('Plumbing','Electrical','HVAC','Security','Cleaning','Other') NOT NULL,
  description TEXT NOT NULL,
  priority ENUM('low','normal','urgent') NOT NULL DEFAULT 'normal',
  status ENUM('pending','assigned','in_progress','resolved') NOT NULL DEFAULT 'pending',
  technician_id INT NULL,
  repair_notes TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (resident_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (technician_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS utility_rates (
  item VARCHAR(30) PRIMARY KEY,
  label VARCHAR(50) NOT NULL,
  rate DECIMAL(10,2) NOT NULL,
  unit VARCHAR(20) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS bills (
  id INT AUTO_INCREMENT PRIMARY KEY,
  resident_id INT NOT NULL,
  month CHAR(7) NOT NULL,
  electricity DECIMAL(10,2) DEFAULT 0,
  water DECIMAL(10,2) DEFAULT 0,
  gas DECIMAL(10,2) DEFAULT 0,
  service DECIMAL(10,2) DEFAULT 0,
  maintenance DECIMAL(10,2) DEFAULT 0,
  other DECIMAL(10,2) DEFAULT 0,
  total DECIMAL(10,2) NOT NULL,
  status ENUM('pending','paid') NOT NULL DEFAULT 'pending',
  paid_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (resident_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS notices (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  body TEXT NOT NULL,
  type ENUM('Notice','Maintenance','Event','Emergency') NOT NULL DEFAULT 'Notice',
  created_by INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  message VARCHAR(255) NOT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sender_id INT NOT NULL,
  receiver_id INT NOT NULL,
  body TEXT NOT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (sender_id, receiver_id),
  FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS inquiries (
  id INT AUTO_INCREMENT PRIMARY KEY,
  flat_id INT NULL,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL,
  phone VARCHAR(30) NULL,
  message TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT IGNORE INTO utility_rates (item,label,rate,unit) VALUES
 ('electricity','Electricity',18.00,'per unit'),
 ('water','Water',4.50,'per m³'),
 ('gas','Gas',12.00,'per m³');
