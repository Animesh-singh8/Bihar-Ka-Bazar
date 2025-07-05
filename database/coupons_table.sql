CREATE TABLE IF NOT EXISTS `coupons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `description` text,
  `discount_type` enum('percentage','fixed') NOT NULL DEFAULT 'percentage',
  `discount_value` decimal(10,2) NOT NULL,
  `min_order_amount` decimal(10,2) DEFAULT 0.00,
  `max_discount_amount` decimal(10,2) DEFAULT 0.00,
  `usage_limit` int(11) DEFAULT NULL,
  `used_count` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `expires_at` datetime DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample coupons
INSERT INTO `coupons` (`code`, `description`, `discount_type`, `discount_value`, `min_order_amount`, `max_discount_amount`, `expires_at`) VALUES
('WELCOME10', 'Welcome discount for new users', 'percentage', 10.00, 200.00, 100.00, '2025-12-31 23:59:59'),
('SAVE50', 'Flat ₹50 off on orders above ₹500', 'fixed', 50.00, 500.00, 0.00, '2025-12-31 23:59:59'),
('BIHAR20', 'Special Bihar discount', 'percentage', 20.00, 1000.00, 200.00, '2025-12-31 23:59:59');
