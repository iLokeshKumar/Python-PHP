CREATE DATABASE IF NOT EXISTS restaurant_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE restaurant_db;

CREATE TABLE menus (
    id   INT UNSIGNED NOT NULL,
    name VARCHAR(50)  NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO menus (id, name) VALUES
(1, 'Food'),
(2, 'Drinks');

CREATE TABLE categories (
    id      INT UNSIGNED NOT NULL,
    name    VARCHAR(100) NOT NULL,
    menu_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    CONSTRAINT fk_cat_menu FOREIGN KEY (menu_id) REFERENCES menus(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO categories (id, name, menu_id) VALUES
(1, 'Starters',    1),
(2, 'Soft Drinks', 2),
(3, 'Mains',       1),
(4, 'Desserts',    2),
(5, 'Hot Drinks',  2);

CREATE TABLE menu_items (
    id          INT UNSIGNED NOT NULL,
    name        VARCHAR(100) NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    menu_id     INT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    CONSTRAINT fk_item_cat  FOREIGN KEY (category_id) REFERENCES categories(id),
    CONSTRAINT fk_item_menu FOREIGN KEY (menu_id)     REFERENCES menus(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO menu_items (id, name, category_id, menu_id) VALUES
(1,  'Item1',  1, 1),
(2,  'Item2',  1, 1),
(3,  'Item3',  2, 2),
(4,  'Item4',  2, 2),
(5,  'Item5',  2, 1),
(6,  'Item6',  3, 1),
(7,  'Item7',  3, 1),
(8,  'Item8',  4, 2),
(9,  'Item9',  4, 2),
(10, 'Item10', 5, 2);

CREATE TABLE item_prices (
    id      INT UNSIGNED NOT NULL AUTO_INCREMENT,
    item_id INT UNSIGNED NOT NULL,
    size    VARCHAR(20)      NULL COMMENT 'NULL = no size variant',
    price   DECIMAL(10,4) NOT NULL,
    PRIMARY KEY (id),
    CONSTRAINT fk_price_item FOREIGN KEY (item_id) REFERENCES menu_items(id),
    INDEX idx_item (item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO item_prices (item_id, size, price) VALUES
(1,  'Small', 1.50),
(1,  'Large', 2.50),
(2,  NULL,    3.00),
(3,  NULL,    2.50),
(4,  NULL,    1.50),
(5,  NULL,    1.00),
(6,  'Small', 2.50),
(6,  'Large', 3.60),
(7,  NULL,    2.50),
(8,  'Small', 3.75),
(8,  'Large', 6.50),
(9,  NULL,    1.50),
(10, NULL,    2.00);

CREATE TABLE orders (
    id         INT UNSIGNED NOT NULL COMMENT 'Order ID from source data',
    order_date DATE         NOT NULL,
    PRIMARY KEY (id),
    INDEX idx_order_date (order_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO orders (id, order_date) VALUES
(10, '2025-10-01'),
(11, '2025-10-01'),
(12, '2025-10-01'),
(13, '2025-10-01'),
(14, '2025-10-01'),
(15, '2025-10-02'),
(16, '2025-10-03'),
(17, '2025-10-01'),
(18, '2025-10-05'),
(19, '2025-10-01'),
(20, '2025-10-01');

CREATE TABLE order_items (
    id           INT UNSIGNED  NOT NULL COMMENT 'Source ID column',
    order_id     INT UNSIGNED  NOT NULL,
    item_id      INT UNSIGNED  NOT NULL,
    size         VARCHAR(20)       NULL,
    price        DECIMAL(12,5) NOT NULL,
    qty          SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    order_status VARCHAR(20)   NOT NULL DEFAULT 'Pending',
    total        DECIMAL(12,5) NOT NULL,
    PRIMARY KEY (id),
    CONSTRAINT fk_oi_order FOREIGN KEY (order_id) REFERENCES orders(id),
    CONSTRAINT fk_oi_item  FOREIGN KEY (item_id)  REFERENCES menu_items(id),
    INDEX idx_oi_order (order_id),
    INDEX idx_oi_item  (item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO order_items (id, order_id, item_id, size, price, qty, order_status, total) VALUES
(1,  10, 2,  NULL,    2.50000,  1, 'Completed', 2.50000),
(2,  10, 3,  NULL,    1.50000,  2, 'Completed', 3.00000),
(3,  10, 1,  'Small', 3.75000,  1, 'Completed', 3.75000),
(4,  11, 5,  NULL,    2.75000,  1, 'Completed', 2.75000),
(5,  11, 6,  NULL,    1.75000,  2, 'Completed', 3.50000),
(6,  11, 2,  NULL,    2.50000,  1, 'Completed', 2.50000),
(7,  11, 3,  NULL,    3.50000,  1, 'Completed', 3.50000),
(8,  11, 4,  NULL,    3.75000,  2, 'Completed', 7.50000),
(9,  11, 5,  NULL,    1.50000,  1, 'Completed', 1.50000),
(10, 12, 6,  'Large', 5.50000,  2, 'Completed', 11.00000),
(11, 12, 7,  NULL,    2.50000,  1, 'Completed', 2.50000),
(12, 12, 1,  'Large', 3.50000,  1, 'Completed', 3.50000),
(13, 13, 1,  'Small', 2.75000,  2, 'Completed', 5.50000),
(14, 13, 6,  'Small', 1.50000,  1, 'Completed', 1.50000),
(15, 13, 8,  'Small', 3.50000,  1, 'Completed', 3.50000),
(16, 13, 1,  'Small', 2.50000,  2, 'Completed', 5.00000),
(17, 14, 6,  'Large', 2.75000,  1, 'Completed', 2.75000),
(18, 14, 1,  'Large', 2.75655,  2, 'Completed', 5.51310),
(19, 14, 8,  'Large', 2.75000,  2, 'Completed', 5.50000),
(20, 14, 1,  'Large', 2.75560,  2, 'Completed', 5.51120),
(21, 14, 4,  NULL,    5.50000,  1, 'Completed', 5.50000),
(22, 14, 3,  NULL,    2.75000,  2, 'Completed', 5.50000),
(23, 14, 2,  NULL,    3.50000,  1, 'Completed', 3.50000),
(24, 14, 6,  'Large', 3.01500,  3, 'Completed', 9.04500),
(25, 15, 2,  NULL,    2.56800,  2, 'Completed', 5.13600),
(26, 16, 6,  'Large', 6.58600,  3, 'Completed', 19.75800),
(27, 17, 10, NULL,    2.50000,  1, 'Completed', 2.50000),
(28, 17, 9,  NULL,    2.75636,  1, 'Completed', 2.75636),
(29, 17, 7,  NULL,    5.63982,  1, 'Completed', 5.63982),
(30, 18, 1,  'Small', 2.56980,  2, 'Completed', 5.13960),
(31, 18, 6,  'Small', 5.36245,  2, 'Completed', 10.72490),
(32, 18, 8,  'Small', 5.23569,  2, 'Completed', 10.47138),
(33, 19, 2,  NULL,    2.75698,  1, 'Completed', 2.75698),
(34, 19, 4,  NULL,    2.35600,  1, 'Completed', 2.35600),
(35, 19, 5,  NULL,    2.45700,  2, 'Completed', 4.91400),
(36, 19, 7,  NULL,    2.63590,  1, 'Completed', 2.63590),
(37, 19, 9,  NULL,    6.52300,  1, 'Completed', 6.52300),
(38, 19, 10, NULL,    8.54120,  3, 'Completed', 25.62360),
(39, 19, 6,  'Large', 5.68300,  2, 'Completed', 11.36600),
(41, 19, 2,  NULL,    6.35640,  1, 'Completed', 6.35640),
(42, 19, 5,  NULL,    7.23500,  1, 'Completed', 7.23500),
(43, 19, 7,  NULL,    2.36500,  1, 'Completed', 2.36500),
(44, 20, 1,  'Large', 2.36580,  1, 'Completed', 2.36580),
(45, 20, 3,  NULL,    2.35600,  1, 'Completed', 2.35600),
(46, 20, 6,  'Large', 1.25600,  1, 'Completed', 1.25600),
(47, 20, 4,  NULL,    2.63500,  1, 'Completed', 2.63500),
(48, 20, 5,  NULL,    5.21000,  1, 'Completed', 5.21000),
(49, 20, 7,  NULL,    6.32500,  2, 'Completed', 12.65000),
(50, 20, 8,  'Small', 7.25140,  1, 'Completed', 7.25140),
(51, 20, 9,  NULL,    2.39990,  1, 'Completed', 2.39990),
(52, 20, 4,  NULL,    2.35600,  3, 'Completed', 7.06800),
(53, 20, 6,  'Small', 4.53260,  2, 'Completed', 9.06520);

CREATE TABLE payments (
    id             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    payment_id     INT UNSIGNED  NOT NULL,
    order_id       INT UNSIGNED  NOT NULL,
    payment_date   DATE          NOT NULL,
    amount_due     DECIMAL(12,5) NOT NULL,
    tips           DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    discount       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total_paid     DECIMAL(10,4) NOT NULL,
    payment_type   VARCHAR(20)   NOT NULL,
    payment_status VARCHAR(20)   NOT NULL DEFAULT 'Pending',
    PRIMARY KEY (id),
    UNIQUE KEY uq_payment_id (payment_id),
    CONSTRAINT fk_pay_order FOREIGN KEY (order_id) REFERENCES orders(id),
    INDEX idx_pay_order (order_id),
    INDEX idx_pay_status (payment_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO payments (payment_id, order_id, payment_date, amount_due, tips, discount, total_paid, payment_type, payment_status) VALUES
(100, 10, '2025-10-01', 9.25000,  0.00, 0.00, 9.25,   'Card', 'Completed'),
(101, 11, '2025-10-01', 21.25000, 0.00, 0.00, 10.00,  'Cash', 'Completed'),
(102, 11, '2025-10-01', 21.25000, 0.00, 0.00, 11.25,  'Card', 'Completed'),
(103, 12, '2025-10-02', 17.00000, 3.00, 4.00, 16.00,  'Card', 'Completed'),
(104, 13, '2025-10-03', 15.50000, 0.00, 2.00, 13.50,  'Card', 'Completed'),
(105, 14, '2025-10-01', 42.81930, 0.00, 0.00, 20.00,  'Cash', 'Completed'),
(106, 14, '2025-10-01', 42.81930, 0.00, 0.00, 22.82,  'Card', 'Completed'),
(107, 15, '2025-10-02', 5.13600,  0.00, 0.00, 5.14,   'Card', 'Refunded'),
(108, 16, '2025-10-03', 19.75800, 0.00, 0.00, 10.00,  'Cash', 'Completed'),
(109, 16, '2025-10-03', 19.75800, 0.00, 0.00, 9.76,   'Card', 'Completed'),
(110, 17, '2025-10-01', 10.89180, 0.00, 0.00, 10.90,  'Card', 'Completed'),
(111, 18, '2025-10-05', 26.33588, 2.00, 0.00, 25.00,  'Cash', 'Completed'),
(115, 18, '2025-10-05', 26.33588, 0.00, 0.00, 3.34,   'Card', 'Completed'),
(116, 19, '2025-10-01', 72.13188, 0.00, 0.00, 50.00,  'Cash', 'Completed'),
(119, 19, '2025-10-01', 72.13188, 0.00, 0.00, 22.13,  'Card', 'Completed'),
(120, 20, '2025-10-01', 52.25730, 0.00, 0.00, 25.00,  'Cash', 'Completed'),
(121, 20, '2025-10-01', 52.25730, 0.00, 0.00, 27.28,  'Card', 'Completed');