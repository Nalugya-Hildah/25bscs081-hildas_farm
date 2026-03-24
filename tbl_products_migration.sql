-- ============================================================
--  HILDA'S POULTRY FARM — tbl_products migration
--  Milestone: From Static to Data-Driven
--  Run this in phpMyAdmin (or MySQL CLI) against
--  the `hildas_poultry_farm` database.
-- ============================================================

USE `hildas_poultry_farm`;

-- 1. Create the products table
CREATE TABLE IF NOT EXISTS `tbl_products` (
    `id`          INT(10) UNSIGNED     NOT NULL AUTO_INCREMENT,
    `title`       VARCHAR(150)         NOT NULL,
    `description` TEXT                 NOT NULL,
    `image_url`   VARCHAR(500)         NOT NULL,
    `category`    VARCHAR(60)          NOT NULL DEFAULT 'general',
    `price`       VARCHAR(60)          NOT NULL,
    `price_unit`  VARCHAR(30)          NOT NULL DEFAULT '/item',
    `badge`       VARCHAR(40)          DEFAULT NULL,
    `features`    TEXT                 DEFAULT NULL   COMMENT 'Comma-separated feature list',
    `sort_order`  TINYINT(3) UNSIGNED  NOT NULL DEFAULT 0,
    `is_active`   TINYINT(1)           NOT NULL DEFAULT 1,
    `created_at`  TIMESTAMP            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Seed with the eight products previously hard-coded in products.php
INSERT INTO `tbl_products`
    (`title`, `description`, `image_url`, `category`, `price`, `price_unit`, `badge`, `features`, `sort_order`)
VALUES
(
    'Fresh Eggs — Tray of 30',
    'Farm-fresh eggs collected daily from healthy layer hens. Rich in protein, natural yolk colour, and full flavour.',
    'https://images.unsplash.com/photo-1506976785307-8732e854ad03?w=600&q=80',
    'eggs',
    'UGX 18,000', '/tray',
    'Best Seller',
    'Collected daily,Unwashed & natural,Free-range hens',
    1
),
(
    'Bulk Eggs — Crate of 360',
    'Perfect for restaurants, hotels, and wholesalers. Consistent size and quality with bulk pricing.',
    'https://images.unsplash.com/photo-1582722872445-44dc5f7e3c8f?w=600&q=80',
    'eggs',
    'UGX 190,000', '/crate',
    'Popular',
    'Bulk discount,Consistent grading,Delivery available',
    2
),
(
    'Live Broiler Chickens',
    'Healthy, fast-growing Ross 308 broilers raised on quality feed. Available at various weights.',
    'https://images.unsplash.com/photo-1548550023-2bdb3c5beed7?w=600&q=80',
    'live-birds',
    'UGX 25,000', '/bird',
    'Fresh',
    'Fully vaccinated,6–8 weeks old,2–2.5 kg avg',
    3
),
(
    'Live Layer Hens',
    'Mature laying hens at peak production. Ideal for starting your own small egg-laying unit.',
    'https://images.unsplash.com/photo-1583125311428-3b67da5c9a68?w=600&q=80',
    'live-birds',
    'UGX 35,000', '/hen',
    'Available',
    'Peak production age,20–25 weeks,Rhode Island Red',
    4
),
(
    'Dressed Whole Chicken',
    'Freshly slaughtered, cleaned, and ready for cooking. Available fresh or chilled.',
    'https://images.unsplash.com/photo-1598103442097-8b74394b95c7?w=600&q=80',
    'dressed-chicken',
    'UGX 32,000', '/kg',
    'Ready to Cook',
    'Same-day fresh,Properly cleaned,Various sizes',
    5
),
(
    'Chicken Parts (Cut)',
    'Drumsticks, breasts, thighs, and wings — available separately for your specific cooking needs.',
    'https://images.unsplash.com/photo-1604503468506-a8da13d82791?w=600&q=80',
    'dressed-chicken',
    'UGX 28,000', '/kg',
    'New',
    'Cut to order,Fresh daily,Restaurant grade',
    6
),
(
    'Day-Old Broiler Chicks',
    'Healthy, vaccinated Ross 308 day-old chicks for your own broiler farm. High survival rate.',
    'https://images.unsplash.com/photo-1611866367069-e8fd2d0a1e6a?w=600&q=80',
    'chicks',
    'UGX 4,500', '/chick',
    'Day-Old',
    'Marek\'s vaccinated,High hatch rate,Minimum 50 chicks',
    7
),
(
    'Organic Poultry Manure',
    'Nutrient-rich poultry manure — excellent organic fertiliser for crops, gardens, and farms.',
    'https://images.unsplash.com/photo-1516467508483-a7212febe31a?w=600&q=80',
    'manure',
    'UGX 5,000', '/bag',
    'Organic',
    'High nitrogen content,Bulk available,Delivery in Kampala',
    8
);
