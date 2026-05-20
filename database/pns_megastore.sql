-- PNS Mega Store — Inventory System Database

CREATE DATABASE IF NOT EXISTS `pns_megastore`
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_general_ci;

USE `pns_megastore`;

-- ============================================
-- Single table: pns_inventory
-- ============================================

DROP TABLE IF EXISTS `pns_inventory`;

CREATE TABLE `pns_inventory` (
  `id`               INT(11)        NOT NULL AUTO_INCREMENT,
  `product_name`     VARCHAR(255)   NOT NULL,
  `category`         ENUM(
    'Concession',
    'Mini',
    'In-house Reuse',
    'Vintages',
    'Reduced',
    'Original & Classic by PNS'
  ) NOT NULL,
  `current_stock`    INT(11)        NOT NULL DEFAULT 0,
  `shelf_qty`        INT(11)        NOT NULL DEFAULT 0,
  `minimum_stock`    INT(11)        NOT NULL DEFAULT 0,
  `price`            DECIMAL(12,2)  NOT NULL DEFAULT 0.00,
  `supplier`         VARCHAR(255)   DEFAULT NULL,
  `storage_location` VARCHAR(255)   DEFAULT NULL,
  `created_at`       TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       TIMESTAMP      DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Seed data — diverse items across all 6 categories
-- ============================================

INSERT INTO `pns_inventory`
  (`product_name`, `category`, `current_stock`, `shelf_qty`, `minimum_stock`, `price`, `supplier`, `storage_location`)
VALUES
  -- Concession Items
  ('Bottled Water 75cl',       'Concession',              120,  25,  20,   250.00,  'AquaPure Ltd',    'Aisle A - Shelf 1'),
  ('Popcorn Pack',             'Concession',               45,   8,  10,  1500.00,  'SnackWorld',      'Aisle A - Shelf 2'),
  ('Candy Bar Assorted',       'Concession',               60,  12,  15,   800.00,  'SweetTreats Co',  'Aisle A - Shelf 3'),

  -- Mini Items (price ≤ 5,000)
  ('USB Cable Type-C',         'Mini',                     80,  15,  10,  2500.00,  'TechBits',        'Aisle B - Shelf 1'),
  ('Pocket Notebook A6',       'Mini',                    200,  30,  25,   500.00,  'PaperHouse',      'Aisle B - Shelf 2'),
  ('Phone Screen Protector',   'Mini',                     90,   5,  15,  1800.00,  'GadgetShield',    'Aisle B - Shelf 3'),

  -- In-house Reuse Items
  ('Reusable Shopping Bag',    'In-house Reuse',          150,  20,  30,  3500.00,  'PNS Internal',    'Warehouse - Bay 1'),
  ('Branded Tote Bag',         'In-house Reuse',           35,   4,  10,  5500.00,  'PNS Internal',    'Warehouse - Bay 2'),

  -- Vintages
  ('Vintage Desk Lamp 1970s',  'Vintages',                  5,   2,   3, 45000.00,  'RetroFinds',      'Display - Section V'),
  ('Classic Vinyl Record Player','Vintages',                3,   1,   2, 78000.00,  'RetroFinds',      'Display - Section V'),

  -- Reduced Items
  ('Clearance Headphones',     'Reduced',                  70,  18,  10,  6500.00,  'AudioMax',        'Aisle C - Shelf 1'),
  ('Season-end Jacket',        'Reduced',                  15,   3,   5, 12000.00,  'FashionEnd',      'Aisle C - Shelf 2'),
  ('Discounted Blender',       'Reduced',                   8,   0,   5, 18500.00,  'HomeApply',       'Aisle C - Shelf 3'),

  -- Original & Classic by PNS
  ('PNS Premium Coffee Blend', 'Original & Classic by PNS', 50, 10,  12,  8500.00,  'PNS Originals',   'Aisle D - Shelf 1'),
  ('PNS Signature Perfume',    'Original & Classic by PNS', 25,  6,   8, 22000.00,  'PNS Originals',   'Aisle D - Shelf 2');
