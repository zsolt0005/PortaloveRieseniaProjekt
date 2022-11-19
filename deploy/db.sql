-- Create database
CREATE DATABASE IF NOT EXISTS product_admin;
USE product_admin;

-- Entities
CREATE TABLE IF NOT EXISTS product_categories (
    id int unsigned primary key auto_increment,
    code char(36) not null unique comment 'category code',
    name varchar(32) unique not null comment 'Category name',
    date_created timestamp not null default CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP comment 'Category created',
    date_updated timestamp not null default CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP comment 'Category last modified'
    ) comment 'Product categories';

CREATE TABLE IF NOT EXISTS products (
    id int unsigned primary key auto_increment,
    code char(36) not null unique comment 'Product code',
    name varchar(32) unique not null comment 'Product name',
    units_sold int unsigned not null default 0 comment 'Units sold',
    units_in_stock int unsigned not null default 0 comment 'Units available in stock',
    date_created timestamp not null default CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP comment 'Product created',
    date_updated timestamp not null default CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP comment 'Product last modified'
    ) COMMENT 'Available products';

-- Relations
CREATE TABLE IF NOT EXISTS products_to_categories (
    product_id int unsigned not null,
    category_id int unsigned not null,

    PRIMARY KEY (product_id, category_id),
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (category_id) REFERENCES product_categories(id)
    );

-- Triggers

DELIMITER ;;
-- DROP TRIGGER before_insert_product_categories;
CREATE TRIGGER before_insert_product_categories BEFORE INSERT ON product_categories
    FOR EACH ROW
BEGIN
    IF new.code IS NULL THEN SET new.code = uuid(); END IF;
END
;;

DELIMITER ;;
-- DROP TRIGGER before_insert_products;
CREATE TRIGGER before_insert_products BEFORE INSERT ON products
    FOR EACH ROW
BEGIN
    IF new.code IS NULL THEN SET new.code = uuid(); END IF;
END
;;