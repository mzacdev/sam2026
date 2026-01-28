-- Migration: add venue fields to table_match
ALTER TABLE `table_match`
  ADD COLUMN `venue_id` INT UNSIGNED NULL AFTER `tarikh`,
  ADD COLUMN `venue_detail` VARCHAR(100) NULL AFTER `venue_id`,
  ADD COLUMN `created_by` INT NULL AFTER `status`;

-- Add foreign key for venue_id if table exists
ALTER TABLE `table_match`
  ADD CONSTRAINT IF NOT EXISTS `table_match_ibfk_venue` FOREIGN KEY (`venue_id`) REFERENCES `table_ref_venues` (`id`) ON DELETE SET NULL;

-- Optionally add index
ALTER TABLE `table_match` ADD INDEX `idx_venue_id` (`venue_id`);
