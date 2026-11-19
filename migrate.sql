-- Run this ONLY if you already imported the earlier schema.sql (keeps your data)
USE smartnest_app;
ALTER TABLE flats ADD COLUMN photo VARCHAR(255) NULL;
ALTER TABLE complaints MODIFY priority ENUM('low','normal','urgent') NOT NULL DEFAULT 'normal';
