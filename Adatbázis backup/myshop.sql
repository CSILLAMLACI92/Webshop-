-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- GĂ©p: localhost:8889
-- LĂ©trehozĂˇs ideje: 2026. Feb 11. 10:33
-- KiszolgĂˇlĂł verziĂłja: 5.7.24
-- PHP verziĂł: 8.3.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- AdatbĂˇzis: `myshop`
--

DELIMITER $$
--
-- EljĂˇrĂˇsok
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `ADD_PRODUCT_VARIANT` (IN `p_product_id` INT, IN `p_variant_name` VARCHAR(255), IN `p_stock` INT)   BEGIN
    INSERT INTO product_variants (product_id, variant_name, stock)
    VALUES (p_product_id, p_variant_name, p_stock);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ADD_REVIEW` (IN `p_user_id` INT, IN `p_product_id` INT, IN `p_rating` INT, IN `p_comment` TEXT)   BEGIN
    INSERT INTO reviews(user_id, product_id, rating, comment)
    VALUES(p_user_id, p_product_id, p_rating, p_comment);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ADD_TO_CART` (IN `p_user_id` INT, IN `p_product_id` INT, IN `p_quantity` INT)   BEGIN
    -- 1. MegprĂłbĂˇljuk frissĂ­teni a mennyisĂ©get, ha a tĂ©tel mĂˇr lĂ©tezik
    UPDATE cart
    SET quantity = quantity + p_quantity
    WHERE user_id = p_user_id AND product_id = p_product_id;

    -- 2. EllenĹ‘rizzĂĽk, tĂ¶rtĂ©nt-e frissĂ­tĂ©s
    -- ROW_COUNT() megmondja, hĂˇny sor frissĂĽlt. Ha 0, akkor a tĂ©tel Ăşj.
    IF ROW_COUNT() = 0 THEN
        -- 3. Ha a frissĂ­tĂ©s sikertelen (a tĂ©tel nem lĂ©tezett), beszĂşrunk egy Ăşj sort
        INSERT INTO cart (user_id, product_id, quantity)
        VALUES (p_user_id, p_product_id, p_quantity);
    END IF;

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ADD_USER_ADDRESS` (IN `p_user_id` INT, IN `p_city` VARCHAR(255), IN `p_street` VARCHAR(255), IN `p_zip` VARCHAR(20))   BEGIN
    INSERT INTO user_addresses(user_id, city, street, zip)
    VALUES(p_user_id, p_city, p_street, p_zip);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `CHANGE_USER_ROLE` (IN `p_user_id` INT, IN `p_role` VARCHAR(50))   BEGIN
    UPDATE users SET role = p_role WHERE id = p_user_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `CLEAR_USER_ACTIVITY` (IN `p_user_id` INT)   BEGIN
    DELETE FROM user_activity WHERE user_id = p_user_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `CREATE_PRODUCT` (IN `p_name` VARCHAR(255), IN `p_price` DECIMAL(10,2), IN `p_desc` TEXT)   BEGIN
    INSERT INTO products(name, price, description)
    VALUES(p_name, p_price, p_desc);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `CREATE_USER` (IN `p_username` VARCHAR(255), IN `p_email` VARCHAR(255), IN `p_password` VARCHAR(255))   BEGIN
    INSERT INTO users(username, email, password)
    VALUES(p_username, p_email, p_password);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `deleteUser` (IN `p_user_id` INT UNSIGNED)   BEGIN

    DELETE FROM users
    WHERE ID = p_user_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `DELETE_ACTIVITY` (IN `p_activity_id` INT)   BEGIN
    DELETE FROM user_activity WHERE id = p_activity_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `DELETE_PRODUCT` (IN `p_product_id` INT)   BEGIN
    DELETE FROM products WHERE id = p_product_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `DELETE_REVIEW` (IN `p_review_id` INT)   BEGIN
    DELETE FROM reviews WHERE id = p_review_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `DELETE_USER` (IN `p_user_id` INT)   BEGIN
    DELETE FROM users WHERE id = p_user_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `DELETE_USER_ADDRESS` (IN `p_address_id` INT)   BEGIN
    DELETE FROM user_addresses WHERE id = p_address_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `DELETE_VARIANT` (IN `p_variant_id` INT)   BEGIN
    DELETE FROM product_variants WHERE id = p_variant_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `LOG_ACTIVITY` (IN `p_user_id` INT, IN `p_action` VARCHAR(255))   BEGIN
    INSERT INTO user_activity(user_id, action)
    VALUES(p_user_id, p_action);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `REMOVE_ITEM_FROM_CART` (IN `p_cart_id` INT)   BEGIN
    DELETE FROM cart WHERE id = p_cart_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `updateUserPassword` (IN `p_user_id` INT, IN `p_new_password_hash` VARCHAR(255))   BEGIN
  UPDATE users
  SET password_hash = p_new_password_hash
  WHERE ID = p_user_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `UPDATE_CART_QUANTITY` (IN `p_cart_id` INT, IN `p_quantity` INT)   BEGIN
    UPDATE cart SET quantity = p_quantity WHERE id = p_cart_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `UPDATE_PRODUCT_PRICE` (IN `p_product_id` INT, IN `p_price` DECIMAL(10,2))   BEGIN
    UPDATE products SET price = p_price WHERE id = p_product_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `UPDATE_REVIEW_COMMENT` (IN `p_review_id` INT, IN `p_comment` TEXT)   BEGIN
    UPDATE reviews SET comment = p_comment WHERE id = p_review_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `UPDATE_USER_ADDRESS` (IN `p_address_id` INT, IN `p_city` VARCHAR(255), IN `p_street` VARCHAR(255), IN `p_zip` VARCHAR(20))   BEGIN
    UPDATE user_addresses
    SET city = p_city,
        street = p_street,
        zip = p_zip
    WHERE id = p_address_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `UPDATE_VARIANT_STOCK` (IN `p_variant_id` INT, IN `p_stock` INT)   BEGIN
    UPDATE product_variants SET stock = p_stock WHERE id = p_variant_id;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- TĂˇbla szerkezet ehhez a tĂˇblĂˇhoz `bands`
--

CREATE TABLE `bands` (
  `id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `genre` varchar(80) DEFAULT NULL,
  `city` varchar(80) DEFAULT NULL,
  `looking_for` varchar(120) DEFAULT NULL,
  `description` text,
  `contact` varchar(120) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- TĂˇbla szerkezet ehhez a tĂˇblĂˇhoz `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- TĂˇbla szerkezet ehhez a tĂˇblĂˇhoz `cart_coupons`
--

CREATE TABLE `cart_coupons` (
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `coupon_id` bigint(20) UNSIGNED NOT NULL,
  `applied_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- TĂˇbla szerkezet ehhez a tĂˇblĂˇhoz `coupons`
--

CREATE TABLE `coupons` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(50) NOT NULL,
  `discount_type` enum('PERCENT','FIX') NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `min_subtotal` decimal(10,2) NOT NULL DEFAULT '0.00',
  `starts_at` datetime DEFAULT NULL,
  `ends_at` datetime DEFAULT NULL,
  `max_uses` int(11) DEFAULT NULL,
  `used_count` int(11) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- TĂˇbla szerkezet ehhez a tĂˇblĂˇhoz `hang`
--

CREATE TABLE `hang` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `cim` varchar(255) NOT NULL,
  `leiras` text,
  `file_path` varchar(512) NOT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `duration_ms` int(10) UNSIGNED DEFAULT NULL,
  `size_bytes` bigint(20) UNSIGNED DEFAULT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- A tĂˇbla adatainak kiĂ­ratĂˇsa `hang`
--

INSERT INTO `hang` (`id`, `user_id`, `cim`, `leiras`, `file_path`, `mime_type`, `duration_ms`, `size_bytes`, `is_public`, `created_at`, `updated_at`) VALUES
(1, NULL, 'bass1', NULL, '/hangok/bass1.mp3', NULL, NULL, NULL, 0, '2026-01-28 10:47:13', NULL),
(2, NULL, 'bass2', NULL, '/hangok/bass2.mp3', NULL, NULL, NULL, 0, '2026-01-28 10:47:13', NULL),
(3, NULL, 'bass3', NULL, '/hangok/bass3.mp3', NULL, NULL, NULL, 0, '2026-01-28 10:47:13', NULL),
(4, NULL, 'bass4', NULL, '/hangok/bass4.mp3', NULL, NULL, NULL, 0, '2026-01-28 10:47:13', NULL),
(5, NULL, 'bass5', NULL, '/hangok/bass5.mp3', NULL, NULL, NULL, 0, '2026-01-28 10:47:13', NULL),
(6, NULL, 'bill1', NULL, '/hangok/bill1.mp3', NULL, NULL, NULL, 0, '2026-01-28 10:47:13', NULL),
(7, NULL, 'drum1', NULL, '/hangok/drum1.mp3', NULL, NULL, NULL, 0, '2026-01-28 10:47:13', NULL),
(8, NULL, 'drum2', NULL, '/hangok/drum2.mp3', NULL, NULL, NULL, 0, '2026-01-28 10:47:13', NULL),
(9, NULL, 'drum3', NULL, '/hangok/drum3.mp3', NULL, NULL, NULL, 0, '2026-01-28 10:47:13', NULL),
(10, NULL, 'drum4', NULL, '/hangok/drum4.mp3', NULL, NULL, NULL, 0, '2026-01-28 10:47:13', NULL),
(11, NULL, 'drum5', NULL, '/hangok/drum5.mp3', NULL, NULL, NULL, 0, '2026-01-28 10:47:13', NULL),
(12, NULL, 'drum6', NULL, '/hangok/drum6.mp3', NULL, NULL, NULL, 0, '2026-01-28 10:47:13', NULL),
(13, NULL, 'drum7', NULL, '/hangok/drum7.mp3', NULL, NULL, NULL, 0, '2026-01-28 10:47:13', NULL),
(14, NULL, 'drum8', NULL, '/hangok/drum8.mp3', NULL, NULL, NULL, 0, '2026-01-28 10:47:13', NULL),
(15, NULL, 'drum9', NULL, '/hangok/drum9.mp3', NULL, NULL, NULL, 0, '2026-01-28 10:47:13', NULL),
(16, NULL, 'git1', NULL, '/hangok/git1.mp3', NULL, NULL, NULL, 0, '2026-01-28 10:47:13', NULL),
(17, NULL, 'git2', NULL, '/hangok/git2.mp3', NULL, NULL, NULL, 0, '2026-01-28 10:47:13', NULL),
(18, NULL, 'git3', NULL, '/hangok/git3.mp3', NULL, NULL, NULL, 0, '2026-01-28 10:47:13', NULL),
(19, NULL, 'git4', NULL, '/hangok/git4.mp3', NULL, NULL, NULL, 0, '2026-01-28 10:47:13', NULL),
(20, NULL, 'git5', NULL, '/hangok/git5.mp3', NULL, NULL, NULL, 0, '2026-01-28 10:47:13', NULL),
(21, NULL, 'synth01', NULL, '/hangok/synth01.wav', NULL, NULL, NULL, 0, '2026-01-28 10:47:13', NULL),
(22, NULL, 'synth02', NULL, '/hangok/synth02.wav', NULL, NULL, NULL, 0, '2026-01-28 10:47:13', NULL),
(23, NULL, 'synth03', NULL, '/hangok/synth03.wav', NULL, NULL, NULL, 0, '2026-01-28 10:47:13', NULL),
(24, NULL, 'synth04', NULL, '/hangok/synth04.wav', NULL, NULL, NULL, 0, '2026-01-28 10:47:13', NULL),
(25, NULL, 'synth05', NULL, '/hangok/synth05.wav', NULL, NULL, NULL, 0, '2026-01-28 10:47:13', NULL),
(26, NULL, 'synth06', NULL, '/hangok/synth06.wav', NULL, NULL, NULL, 0, '2026-01-28 10:47:13', NULL),
(27, NULL, 'synth07', NULL, '/hangok/synth07.wav', NULL, NULL, NULL, 0, '2026-01-28 10:47:13', NULL),
(28, NULL, 'synth08', NULL, '/hangok/synth08.wav', NULL, NULL, NULL, 0, '2026-01-28 10:47:13', NULL),
(29, NULL, 'synth09', NULL, '/hangok/synth09.wav', NULL, NULL, NULL, 0, '2026-01-28 10:47:13', NULL),
(30, NULL, 'synth10', NULL, '/hangok/synth10.wav', NULL, NULL, NULL, 0, '2026-01-28 10:47:13', NULL);

-- --------------------------------------------------------

--
-- TĂˇbla szerkezet ehhez a tĂˇblĂˇhoz `kategoria`
--

CREATE TABLE `kategoria` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nev` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- A tĂˇbla adatainak kiĂ­ratĂˇsa `kategoria`
--

INSERT INTO `kategoria` (`id`, `nev`, `slug`) VALUES
(1, 'GitĂˇr', 'gitar'),
(2, 'Basszus gitĂˇr', 'basszus-gitar'),
(3, 'Dobszettek', 'dobszettek'),
(4, 'BillentyĹ±', 'billentyu'),
(5, 'Mikrofon', 'mikrofon'),
(6, 'TartozĂ©kok', 'tartozekok'),
(7, 'Hangfalak', 'hangfalak');

-- --------------------------------------------------------

--
-- TĂˇbla szerkezet ehhez a tĂˇblĂˇhoz `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total` int(11) NOT NULL,
  `status` varchar(32) NOT NULL DEFAULT 'created',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- TĂˇbla szerkezet ehhez a tĂˇblĂˇhoz `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `unit_price` int(11) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- TĂˇbla szerkezet ehhez a tĂˇblĂˇhoz `pictures`
--

CREATE TABLE `pictures` (
  `id` int(11) NOT NULL,
  `owner_type` enum('product','user','other') NOT NULL DEFAULT 'other',
  `owner_id` int(11) DEFAULT NULL,
  `filename` varchar(255) NOT NULL,
  `path` varchar(255) NOT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `mime_type` varchar(50) DEFAULT NULL,
  `size_bytes` int(11) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- A tĂˇbla adatainak kiĂ­ratĂˇsa `pictures`
--

INSERT INTO `pictures` (`id`, `owner_type`, `owner_id`, `filename`, `path`, `alt_text`, `mime_type`, `size_bytes`, `is_active`, `created_at`) VALUES
(1, 'other', NULL, 'bass1.jpg', '/Images/bass1.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(2, 'other', NULL, 'bass10.jpg', '/Images/bass10.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(3, 'other', NULL, 'bass2.jpg', '/Images/bass2.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(4, 'other', NULL, 'bass3.jpg', '/Images/bass3.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(5, 'other', NULL, 'bass4.jpg', '/Images/bass4.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(6, 'other', NULL, 'bass5.jpg', '/Images/bass5.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(7, 'other', NULL, 'bass6.jpg', '/Images/bass6.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(8, 'other', NULL, 'bass7.jpg', '/Images/bass7.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(9, 'other', NULL, 'bass8.jpg', '/Images/bass8.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(10, 'other', NULL, 'bass9.jpg', '/Images/bass9.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(11, 'other', NULL, 'bill1.jpg', '/Images/bill1.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(12, 'other', NULL, 'bill10.jpg', '/Images/bill10.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(13, 'other', NULL, 'bill2.jpg', '/Images/bill2.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(14, 'other', NULL, 'bill3.jpg', '/Images/bill3.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(15, 'other', NULL, 'bill4.jpg', '/Images/bill4.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(16, 'other', NULL, 'bill5.jpg', '/Images/bill5.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(17, 'other', NULL, 'bill6.jpg', '/Images/bill6.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(18, 'other', NULL, 'bill7.jpg', '/Images/bill7.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(19, 'other', NULL, 'bill8.jpg', '/Images/bill8.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(20, 'other', NULL, 'bill9.jpg', '/Images/bill9.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(21, 'other', NULL, 'billentyucske.jpg', '/Images/billentyucske.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(22, 'other', NULL, 'boti.jpg', '/Images/boti.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(23, 'other', NULL, 'Default.avatar.jpg', '/Images/Default.avatar.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(24, 'other', NULL, 'dob1.jpg', '/Images/dob1.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(25, 'other', NULL, 'dob10.jpg', '/Images/dob10.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(26, 'other', NULL, 'dob2.jpg', '/Images/dob2.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(27, 'other', NULL, 'dob3.jpg', '/Images/dob3.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(28, 'other', NULL, 'dob4.jpg', '/Images/dob4.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(29, 'other', NULL, 'dob5.jpg', '/Images/dob5.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(30, 'other', NULL, 'dob6.jpg', '/Images/dob6.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(31, 'other', NULL, 'dob7.jpg', '/Images/dob7.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(32, 'other', NULL, 'dob8.jpg', '/Images/dob8.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(33, 'other', NULL, 'dob9.jpg', '/Images/dob9.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(34, 'other', NULL, 'dobocska.jpg', '/Images/dobocska.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(35, 'other', NULL, 'dobok.jpg', '/Images/dobok.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(36, 'other', NULL, 'Erik.jpg', '/Images/Erik.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(37, 'other', NULL, 'Fender Stratocaster.jpg', '/Images/Fender Stratocaster.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(38, 'other', NULL, 'gitar.jfif', '/Images/gitar.jfif', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(39, 'other', NULL, 'gitar10(5).jpg', '/Images/gitar10(5).jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(40, 'other', NULL, 'gitar4.jpg', '/Images/gitar4.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(41, 'other', NULL, 'gitar5.jpg', '/Images/gitar5.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(42, 'other', NULL, 'gitar6 (1).jpg', '/Images/gitar6 (1).jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(43, 'other', NULL, 'gitar7(2).jpg', '/Images/gitar7(2).jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(44, 'other', NULL, 'gitar8 (3).jpg', '/Images/gitar8 (3).jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(45, 'other', NULL, 'gitar9(4).jpg', '/Images/gitar9(4).jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(46, 'other', NULL, 'gitarka.jpg', '/Images/gitarka.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(47, 'other', NULL, 'gitarka2.png', '/Images/gitarka2.png', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(48, 'other', NULL, 'gitarka3.png', '/Images/gitarka3.png', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(49, 'other', NULL, 'hang1.jpg', '/Images/hang1.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(50, 'other', NULL, 'hang10.jpg', '/Images/hang10.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(51, 'other', NULL, 'hang2.jpg', '/Images/hang2.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(52, 'other', NULL, 'hang3.jpg', '/Images/hang3.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(53, 'other', NULL, 'hang4.jpg', '/Images/hang4.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(54, 'other', NULL, 'hang5.jpg', '/Images/hang5.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(55, 'other', NULL, 'hang6.jpg', '/Images/hang6.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(56, 'other', NULL, 'hang7.jpg', '/Images/hang7.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(57, 'other', NULL, 'hang8.jpg', '/Images/hang8.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(58, 'other', NULL, 'hang9.jpg', '/Images/hang9.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(59, 'other', NULL, 'harph.jpeg', '/Images/harph.jpeg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(60, 'other', NULL, 'Ibanez RG421.jpg', '/Images/Ibanez RG421.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(61, 'other', NULL, 'lespaul.jpg', '/Images/lespaul.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(62, 'other', NULL, 'LOGO.png', '/Images/LOGO.png', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(63, 'other', NULL, 'mastercard.png', '/Images/mastercard.png', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(64, 'other', NULL, 'meno.jpg', '/Images/meno.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(65, 'other', NULL, 'mic.jpg', '/Images/mic.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(66, 'other', NULL, 'mik1.jpg', '/Images/mik1.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(67, 'other', NULL, 'mik10.jpg', '/Images/mik10.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(68, 'other', NULL, 'mik2.jpg', '/Images/mik2.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(69, 'other', NULL, 'mik3.jpg', '/Images/mik3.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(70, 'other', NULL, 'mik4.jpg', '/Images/mik4.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(71, 'other', NULL, 'mik5.jpg', '/Images/mik5.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(72, 'other', NULL, 'mik6.jpg', '/Images/mik6.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(73, 'other', NULL, 'mik7.jpg', '/Images/mik7.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(74, 'other', NULL, 'mik8.jpg', '/Images/mik8.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(75, 'other', NULL, 'mik9.jpg', '/Images/mik9.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(76, 'other', NULL, 'money.png', '/Images/money.png', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(77, 'other', NULL, 'Paypal.png', '/Images/Paypal.png', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(78, 'other', NULL, 'PRS.jpg', '/Images/PRS.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(79, 'other', NULL, 'tart1.jpg', '/Images/tart1.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(80, 'other', NULL, 'tart10.jpg', '/Images/tart10.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(81, 'other', NULL, 'tart2.jpg', '/Images/tart2.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(82, 'other', NULL, 'tart3.jpg', '/Images/tart3.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(83, 'other', NULL, 'tart4.jpg', '/Images/tart4.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(84, 'other', NULL, 'tart5.jpg', '/Images/tart5.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(85, 'other', NULL, 'tart6.jpg', '/Images/tart6.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(86, 'other', NULL, 'tart7.jpg', '/Images/tart7.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(87, 'other', NULL, 'tart8.jpg', '/Images/tart8.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(88, 'other', NULL, 'tart9.jpg', '/Images/tart9.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(89, 'other', NULL, 'Visa.png', '/Images/Visa.png', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(90, 'other', NULL, 'Yamaha.jpg', '/Images/Yamaha.jpg', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(91, 'other', NULL, 'zongora.jfif', '/Images/zongora.jfif', NULL, NULL, NULL, 1, '2026-01-28 11:31:29'),
(92, 'product', 1, 'Yamaha.jpg', '/Images/Yamaha.jpg', 'Yamaha Pacifica 112V', NULL, NULL, 1, '2026-01-28 13:17:33'),
(93, 'product', 2, 'Fender%20Stratocaster.jpg', '/Images/Fender%20Stratocaster.jpg', 'Fender Stratocaster', NULL, NULL, 1, '2026-01-28 13:17:33'),
(94, 'product', 3, 'Ibanez%20RG421.jpg', '/Images/Ibanez%20RG421.jpg', 'Ibanez RG421', NULL, NULL, 1, '2026-01-28 13:17:33'),
(95, 'product', 4, 'lespaul.jpg', '/Images/lespaul.jpg', 'Epiphone Les Paul', NULL, NULL, 1, '2026-01-28 13:17:33'),
(96, 'product', 5, 'PRS.jpg', '/Images/PRS.jpg', 'PRS SE Custom 24', NULL, NULL, 1, '2026-01-28 13:17:33'),
(97, 'product', 101, 'bass1.jpg', '/Images/bass1.jpg', 'Ibanez GSR200', NULL, NULL, 1, '2026-01-28 13:17:33'),
(98, 'product', 102, 'bass2.jpg', '/Images/bass2.jpg', 'Yamaha TRBX174', NULL, NULL, 1, '2026-01-28 13:17:33'),
(99, 'product', 103, 'bass3.jpg', '/Images/bass3.jpg', 'Fender Precision Bass', NULL, NULL, 1, '2026-01-28 13:17:33'),
(100, 'product', 104, 'bass4.jpg', '/Images/bass4.jpg', 'Jackson JS3 Spectra', NULL, NULL, 1, '2026-01-28 13:17:33'),
(101, 'product', 105, 'bass5.jpg', '/Images/bass5.jpg', 'Harley Benton JB-75', NULL, NULL, 1, '2026-01-28 13:17:33'),
(102, 'product', 201, 'dob1.jpg', '/Images/dob1.jpg', 'Tama Imperialstar', NULL, NULL, 1, '2026-01-28 13:17:33'),
(103, 'product', 302, 'dob1.jpg', '/Images/dob1.jpg', 'Tama Imperialstar', NULL, NULL, 1, '2026-01-28 13:17:33'),
(104, 'product', 306, 'dob1.jpg', '/Images/dob1.jpg', 'Tama Imperialstar', NULL, NULL, 1, '2026-01-28 13:17:33'),
(105, 'product', 202, 'dob2.jpg', '/Images/dob2.jpg', 'Pearl Export Series', NULL, NULL, 1, '2026-01-28 13:17:33'),
(106, 'product', 307, 'dob2.jpg', '/Images/dob2.jpg', 'Pearl Export Series', NULL, NULL, 1, '2026-01-28 13:17:33'),
(108, 'product', 203, 'dob3.jpg', '/Images/dob3.jpg', 'Mapex Tornado', NULL, NULL, 1, '2026-01-28 13:17:33'),
(109, 'product', 308, 'dob3.jpg', '/Images/dob3.jpg', 'Mapex Tornado', NULL, NULL, 1, '2026-01-28 13:17:33'),
(111, 'product', 204, 'dob4.jpg', '/Images/dob4.jpg', 'Ludwig Breakbeats', NULL, NULL, 1, '2026-01-28 13:17:33'),
(112, 'product', 309, 'dob4.jpg', '/Images/dob4.jpg', 'Ludwig Breakbeats', NULL, NULL, 1, '2026-01-28 13:17:33'),
(114, 'product', 205, 'dob5.jpg', '/Images/dob5.jpg', 'Sonor AQX', NULL, NULL, 1, '2026-01-28 13:17:33'),
(115, 'product', 310, 'dob5.jpg', '/Images/dob5.jpg', 'Sonor AQX', NULL, NULL, 1, '2026-01-28 13:17:33'),
(117, 'product', 206, 'dob6.jpg', '/Images/dob6.jpg', 'Gretsch Catalina Club', NULL, NULL, 1, '2026-01-28 13:17:33'),
(118, 'product', 311, 'dob6.jpg', '/Images/dob6.jpg', 'Gretsch Catalina Club', NULL, NULL, 1, '2026-01-28 13:17:33'),
(120, 'product', 207, 'dob7.jpg', '/Images/dob7.jpg', 'Alesis Nitro Mesh Kit (elektromos)', NULL, NULL, 1, '2026-01-28 13:17:33'),
(121, 'product', 312, 'dob7.jpg', '/Images/dob7.jpg', 'Alesis Nitro Mesh Kit (elektromos)', NULL, NULL, 1, '2026-01-28 13:17:33'),
(123, 'product', 208, 'dob8.jpg', '/Images/dob8.jpg', 'Roland TD-1DMK (elektromos)', NULL, NULL, 1, '2026-01-28 13:17:33'),
(124, 'product', 313, 'dob8.jpg', '/Images/dob8.jpg', 'Roland TD-1DMK (elektromos)', NULL, NULL, 1, '2026-01-28 13:17:33'),
(126, 'product', 209, 'dob9.jpg', '/Images/dob9.jpg', 'Millenium MX222BX Standard', NULL, NULL, 1, '2026-01-28 13:17:33'),
(127, 'product', 314, 'dob9.jpg', '/Images/dob9.jpg', 'Millenium MX222BX Standard', NULL, NULL, 1, '2026-01-28 13:17:33'),
(129, 'product', 210, 'dob10.jpg', '/Images/dob10.jpg', 'Mapex Mars Rock Set', NULL, NULL, 1, '2026-01-28 13:17:33'),
(130, 'product', 315, 'dob10.jpg', '/Images/dob10.jpg', 'Mapex Mars Rock Set', NULL, NULL, 1, '2026-01-28 13:17:33'),
(132, 'product', 316, 'bill1.jpg', '/Images/bill1.jpg', 'Yamaha PSR-E373', NULL, NULL, 1, '2026-01-28 13:17:33'),
(133, 'product', 317, 'bill2.jpg', '/Images/bill2.jpg', 'Casio CT-X700', NULL, NULL, 1, '2026-01-28 13:17:33'),
(134, 'product', 318, 'bill3.jpg', '/Images/bill3.jpg', 'Roland GO:Keys', NULL, NULL, 1, '2026-01-28 13:17:33'),
(135, 'product', 319, 'bill4.jpg', '/Images/bill4.jpg', 'Korg B2', NULL, NULL, 1, '2026-01-28 13:17:33'),
(136, 'product', 320, 'bill5.jpg', '/Images/bill5.jpg', 'Yamaha P-45', NULL, NULL, 1, '2026-01-28 13:17:33'),
(137, 'product', 321, 'bill6.jpg', '/Images/bill6.jpg', 'Kawai ES110', NULL, NULL, 1, '2026-01-28 13:17:33'),
(138, 'product', 322, 'bill7.jpg', '/Images/bill7.jpg', 'Alesis Recital Pro', NULL, NULL, 1, '2026-01-28 13:17:33'),
(139, 'product', 323, 'bill8.jpg', '/Images/bill8.jpg', 'Kurzweil KP100', NULL, NULL, 1, '2026-01-28 13:17:33'),
(140, 'product', 324, 'bill9.jpg', '/Images/bill9.jpg', 'Roland FP-10', NULL, NULL, 1, '2026-01-28 13:17:33'),
(141, 'product', 325, 'bill10.jpg', '/Images/bill10.jpg', 'Nord Electro 6D', NULL, NULL, 1, '2026-01-28 13:17:33'),
(142, 'product', 326, 'mik1.jpg', '/Images/mik1.jpg', 'Shure SM58', NULL, NULL, 1, '2026-01-28 13:17:33'),
(143, 'product', 327, 'mik2.jpg', '/Images/mik2.jpg', 'AKG P120', NULL, NULL, 1, '2026-01-28 13:17:33'),
(144, 'product', 328, 'mik3.jpg', '/Images/mik3.jpg', 'Audio-Technica AT2020', NULL, NULL, 1, '2026-01-28 13:17:33'),
(145, 'product', 329, 'mik4.jpg', '/Images/mik4.jpg', 'Rode NT1-A', NULL, NULL, 1, '2026-01-28 13:17:33'),
(146, 'product', 330, 'mik5.jpg', '/Images/mik5.jpg', 'Blue Yeti USB', NULL, NULL, 1, '2026-01-28 13:17:33'),
(147, 'product', 331, 'mik6.jpg', '/Images/mik6.jpg', 'HyperX QuadCast', NULL, NULL, 1, '2026-01-28 13:17:33'),
(148, 'product', 332, 'mik7.jpg', '/Images/mik7.jpg', 'Sennheiser E835', NULL, NULL, 1, '2026-01-28 13:17:33'),
(149, 'product', 333, 'mik8.jpg', '/Images/mik8.jpg', 'Behringer C-1', NULL, NULL, 1, '2026-01-28 13:17:33'),
(150, 'product', 334, 'mik9.jpg', '/Images/mik9.jpg', 'Samson C01U Pro', NULL, NULL, 1, '2026-01-28 13:17:33'),
(151, 'product', 335, 'mik10.jpg', '/Images/mik10.jpg', 'Rode PodMic', NULL, NULL, 1, '2026-01-28 13:17:33'),
(152, 'product', 347, 'hang2.jpg', '/Images/hang2.jpg', 'KRK Rokit 5 G4', NULL, NULL, 1, '2026-01-28 13:17:33'),
(153, 'product', 348, 'hang3.jpg', '/Images/hang3.jpg', 'JBL 305P MKII', NULL, NULL, 1, '2026-01-28 13:17:33'),
(154, 'product', 349, 'hang4.jpg', '/Images/hang4.jpg', 'Behringer Eurolive B112D', NULL, NULL, 1, '2026-01-28 13:17:33'),
(155, 'product', 350, 'hang5.jpg', '/Images/hang5.jpg', 'Mackie CR4-X', NULL, NULL, 1, '2026-01-28 13:17:33'),
(156, 'product', 351, 'hang6.jpg', '/Images/hang6.jpg', 'Sony Bluetooth Hangfal', NULL, NULL, 1, '2026-01-28 13:17:33'),
(157, 'product', 352, 'hang7.jpg', '/Images/hang7.jpg', 'Marshall Emberton II', NULL, NULL, 1, '2026-01-28 13:17:33'),
(158, 'product', 353, 'hang8.jpg', '/Images/hang8.jpg', 'Bose SoundLink Flex', NULL, NULL, 1, '2026-01-28 13:17:33'),
(159, 'product', 354, 'hang9.jpg', '/Images/hang9.jpg', 'Presonus Eris E3.5', NULL, NULL, 1, '2026-01-28 13:17:33'),
(160, 'product', 355, 'hang10.jpg', '/Images/hang10.jpg', 'Pioneer DJ DM-40', NULL, NULL, 1, '2026-01-28 13:17:33'),
(161, 'product', 386, 'gitar.jfif', '/Images/gitar.jfif', 'Squier Classic Vibe Telecaster', NULL, NULL, 1, '2026-01-30 09:52:00'),
(162, 'product', 387, 'gitarka.jpg', '/Images/gitarka.jpg', 'Jackson Dinky JS22', NULL, NULL, 1, '2026-01-30 09:52:00'),
(163, 'product', 388, 'gitarka2.png', '/Images/gitarka2.png', 'ESP LTD EC-256', NULL, NULL, 1, '2026-01-30 09:52:00'),
(164, 'product', 389, 'gitarka3.png', '/Images/gitarka3.png', 'Schecter C-6 Deluxe', NULL, NULL, 1, '2026-01-30 09:52:00'),
(165, 'product', 390, 'Yamaha.jpg', '/Images/Yamaha.jpg', 'Gretsch G2622 Streamliner', NULL, NULL, 1, '2026-01-30 09:52:00'),
(166, 'product', 391, 'PRS.jpg', '/Images/PRS.jpg', 'Yamaha Revstar RS320', NULL, NULL, 1, '2026-01-30 09:52:00'),
(167, 'product', 392, 'bass6.jpg', '/Images/bass6.jpg', 'Cort Action Bass Plus', NULL, NULL, 1, '2026-01-30 09:52:00'),
(168, 'product', 393, 'bass7.jpg', '/Images/bass7.jpg', 'Ibanez SR300E', NULL, NULL, 1, '2026-01-30 09:52:00'),
(169, 'product', 394, 'bass8.jpg', '/Images/bass8.jpg', 'Squier Affinity Jazz Bass', NULL, NULL, 1, '2026-01-30 09:52:00'),
(170, 'product', 395, 'bass9.jpg', '/Images/bass9.jpg', 'Warwick RockBass Corvette', NULL, NULL, 1, '2026-01-30 09:52:00'),
(171, 'product', 396, 'bass10.jpg', '/Images/bass10.jpg', 'Music Man SUB Ray4', NULL, NULL, 1, '2026-01-30 09:52:00'),
(172, 'product', 397, 'Fender Stratocaster.jpg', '/Images/Fender Stratocaster.jpg', 'Fender Telecaster Player', NULL, NULL, 1, '2026-01-30 09:56:40'),
(173, 'product', 398, 'Ibanez RG421.jpg', '/Images/Ibanez RG421.jpg', 'Ibanez AZES40', NULL, NULL, 1, '2026-01-30 09:56:40'),
(174, 'product', 399, 'gitar.jfif', '/Images/gitar.jfif', 'Gibson SG Tribute', NULL, NULL, 1, '2026-01-30 09:56:40'),
(175, 'product', 400, 'gitarka.jpg', '/Images/gitarka.jpg', 'Cort G250', NULL, NULL, 1, '2026-01-30 09:56:40'),
(176, 'product', 401, 'gitarka2.png', '/Images/gitarka2.png', 'Squier Classic Vibe Jazzmaster', NULL, NULL, 1, '2026-01-30 09:56:40'),
(177, 'product', 402, 'gitarka3.png', '/Images/gitarka3.png', 'ESP LTD Viper-256', NULL, NULL, 1, '2026-01-30 09:56:40'),
(178, 'product', 403, 'bass1.jpg', '/Images/bass1.jpg', 'Yamaha BB234', NULL, NULL, 1, '2026-01-30 09:56:40'),
(179, 'product', 404, 'bass2.jpg', '/Images/bass2.jpg', 'Squier Classic Vibe Precision', NULL, NULL, 1, '2026-01-30 09:56:40'),
(180, 'product', 405, 'bass3.jpg', '/Images/bass3.jpg', 'Ibanez TMB100', NULL, NULL, 1, '2026-01-30 09:56:40'),
(181, 'product', 406, 'bass4.jpg', '/Images/bass4.jpg', 'Cort Artisan B4', NULL, NULL, 1, '2026-01-30 09:56:40'),
(182, 'product', 407, 'bass5.jpg', '/Images/bass5.jpg', 'Schecter Stiletto Extreme-4', NULL, NULL, 1, '2026-01-30 09:56:40'),
(183, 'product', 408, 'bass6.jpg', '/Images/bass6.jpg', 'Fender Jazz Bass Player', NULL, NULL, 1, '2026-01-30 09:56:40'),
(184, 'product', 409, 'dob1.jpg', '/Images/dob1.jpg', 'Pearl Roadshow', NULL, NULL, 1, '2026-01-30 09:56:40'),
(185, 'product', 410, 'dob2.jpg', '/Images/dob2.jpg', 'Yamaha Stage Custom', NULL, NULL, 1, '2026-01-30 09:56:40'),
(186, 'product', 411, 'dob3.jpg', '/Images/dob3.jpg', 'Mapex Armory', NULL, NULL, 1, '2026-01-30 09:56:40'),
(187, 'product', 412, 'dob4.jpg', '/Images/dob4.jpg', 'Tama Superstar Classic', NULL, NULL, 1, '2026-01-30 09:56:40'),
(188, 'product', 413, 'dob5.jpg', '/Images/dob5.jpg', 'Ludwig Element Evolution', NULL, NULL, 1, '2026-01-30 09:56:40'),
(189, 'product', 414, 'dob6.jpg', '/Images/dob6.jpg', 'Gretsch Energy', NULL, NULL, 1, '2026-01-30 09:56:40'),
(190, 'product', 415, 'bill1.jpg', '/Images/bill1.jpg', 'Casio CDP-S110', NULL, NULL, 1, '2026-01-30 09:56:40'),
(191, 'product', 416, 'bill2.jpg', '/Images/bill2.jpg', 'Yamaha P-125', NULL, NULL, 1, '2026-01-30 09:56:40'),
(192, 'product', 417, 'bill3.jpg', '/Images/bill3.jpg', 'Korg B2N', NULL, NULL, 1, '2026-01-30 09:56:40'),
(193, 'product', 418, 'bill4.jpg', '/Images/bill4.jpg', 'Roland GO:Keys 3', NULL, NULL, 1, '2026-01-30 09:56:40'),
(194, 'product', 419, 'bill5.jpg', '/Images/bill5.jpg', 'Alesis Prestige', NULL, NULL, 1, '2026-01-30 09:56:40'),
(195, 'product', 420, 'bill6.jpg', '/Images/bill6.jpg', 'Kurzweil SP1', NULL, NULL, 1, '2026-01-30 09:56:40'),
(196, 'product', 421, 'mik1.jpg', '/Images/mik1.jpg', 'Shure SM7B', NULL, NULL, 1, '2026-01-30 09:56:40'),
(197, 'product', 422, 'mik2.jpg', '/Images/mik2.jpg', 'Rode NT-USB', NULL, NULL, 1, '2026-01-30 09:56:40'),
(198, 'product', 423, 'mik3.jpg', '/Images/mik3.jpg', 'AKG D5', NULL, NULL, 1, '2026-01-30 09:56:40'),
(199, 'product', 424, 'mik4.jpg', '/Images/mik4.jpg', 'Audio-Technica AT2035', NULL, NULL, 1, '2026-01-30 09:56:40'),
(200, 'product', 425, 'mik5.jpg', '/Images/mik5.jpg', 'Sennheiser e935', NULL, NULL, 1, '2026-01-30 09:56:40'),
(201, 'product', 426, 'mik6.jpg', '/Images/mik6.jpg', 'Behringer XM8500', NULL, NULL, 1, '2026-01-30 09:56:40'),
(202, 'product', 427, 'hang1.jpg', '/Images/hang1.jpg', 'Yamaha HS7', NULL, NULL, 1, '2026-01-30 09:56:40'),
(203, 'product', 428, 'hang2.jpg', '/Images/hang2.jpg', 'KRK Rokit 7 G4', NULL, NULL, 1, '2026-01-30 09:56:40'),
(204, 'product', 429, 'hang3.jpg', '/Images/hang3.jpg', 'JBL 306P MKII', NULL, NULL, 1, '2026-01-30 09:56:40'),
(205, 'product', 430, 'hang4.jpg', '/Images/hang4.jpg', 'Mackie CR5-X', NULL, NULL, 1, '2026-01-30 09:56:40'),
(206, 'product', 431, 'hang5.jpg', '/Images/hang5.jpg', 'Bose SoundLink Mini', NULL, NULL, 1, '2026-01-30 09:56:40'),
(207, 'product', 432, 'hang6.jpg', '/Images/hang6.jpg', 'Marshall Stanmore II', NULL, NULL, 1, '2026-01-30 09:56:40'),
(208, 'product', 338, 'tart1.jpg', '/Images/tart1.jpg', 'GitĂˇr ĂˇllvĂˇny', NULL, NULL, 1, '2026-01-30 09:56:40'),
(209, 'product', 433, 'tart1.jpg', '/Images/tart1.jpg', 'Gitar allvany', NULL, NULL, 1, '2026-01-30 09:56:40'),
(211, 'product', 434, 'tart2.jpg', '/Images/tart2.jpg', 'Billentyu pad', NULL, NULL, 1, '2026-01-30 09:56:40'),
(212, 'product', 435, 'tart3.jpg', '/Images/tart3.jpg', 'Kapodaszter', NULL, NULL, 1, '2026-01-30 09:56:40'),
(213, 'product', 436, 'tart4.jpg', '/Images/tart4.jpg', 'Hangszer tisztito keszlet', NULL, NULL, 1, '2026-01-30 09:56:40'),
(214, 'product', 437, 'tart5.jpg', '/Images/tart5.jpg', 'Metronom', NULL, NULL, 1, '2026-01-30 09:56:40'),
(215, 'product', 438, 'tart6.jpg', '/Images/tart6.jpg', 'Pot hurok szett', NULL, NULL, 1, '2026-01-30 09:56:40'),
(216, 'product', 439, 'Yamaha.jpg', '/Images/Yamaha.jpg', 'Harley Benton ST-20', NULL, NULL, 1, '2026-01-30 09:58:12'),
(217, 'product', 440, 'Fender Stratocaster.jpg', '/Images/Fender Stratocaster.jpg', 'Squier Bullet Strat', NULL, NULL, 1, '2026-01-30 09:58:12'),
(218, 'product', 441, 'Ibanez RG421.jpg', '/Images/Ibanez RG421.jpg', 'Yamaha Pacifica 012', NULL, NULL, 1, '2026-01-30 09:58:12'),
(219, 'product', 442, 'lespaul.jpg', '/Images/lespaul.jpg', 'Epiphone SG Standard', NULL, NULL, 1, '2026-01-30 09:58:12'),
(220, 'product', 443, 'bass7.jpg', '/Images/bass7.jpg', 'Harley Benton PJ-4', NULL, NULL, 1, '2026-01-30 09:58:12'),
(221, 'product', 444, 'bass8.jpg', '/Images/bass8.jpg', 'Yamaha TRBX305', NULL, NULL, 1, '2026-01-30 09:58:12'),
(222, 'product', 445, 'bass9.jpg', '/Images/bass9.jpg', 'Ibanez SRMD200', NULL, NULL, 1, '2026-01-30 09:58:12'),
(223, 'product', 446, 'bass10.jpg', '/Images/bass10.jpg', 'Squier Affinity PJ Bass', NULL, NULL, 1, '2026-01-30 09:58:12'),
(224, 'product', 447, 'dob7.jpg', '/Images/dob7.jpg', 'Alesis Nitro Max (elektromos)', NULL, NULL, 1, '2026-01-30 09:58:12'),
(225, 'product', 448, 'dob8.jpg', '/Images/dob8.jpg', 'Roland TD-07KV (elektromos)', NULL, NULL, 1, '2026-01-30 09:58:12'),
(226, 'product', 449, 'dob9.jpg', '/Images/dob9.jpg', 'Millenium MPS-850 (elektromos)', NULL, NULL, 1, '2026-01-30 09:58:12'),
(227, 'product', 450, 'dob10.jpg', '/Images/dob10.jpg', 'Yamaha Rydeen', NULL, NULL, 1, '2026-01-30 09:58:12'),
(228, 'product', 451, 'bill7.jpg', '/Images/bill7.jpg', 'Kawai ES120', NULL, NULL, 1, '2026-01-30 09:58:12'),
(229, 'product', 452, 'bill8.jpg', '/Images/bill8.jpg', 'Casio CT-S1', NULL, NULL, 1, '2026-01-30 09:58:12'),
(230, 'product', 453, 'bill9.jpg', '/Images/bill9.jpg', 'Roland FP-30X', NULL, NULL, 1, '2026-01-30 09:58:12'),
(231, 'product', 454, 'bill10.jpg', '/Images/bill10.jpg', 'Nord Piano 5', NULL, NULL, 1, '2026-01-30 09:58:12'),
(232, 'product', 335, 'mik7.jpg', '/Images/mik7.jpg', 'Rode PodMic', NULL, NULL, 1, '2026-01-30 09:58:12'),
(233, 'product', 455, 'mik7.jpg', '/Images/mik7.jpg', 'Rode PodMic', NULL, NULL, 1, '2026-01-30 09:58:12'),
(235, 'product', 456, 'mik8.jpg', '/Images/mik8.jpg', 'Blue Yeti X', NULL, NULL, 1, '2026-01-30 09:58:12'),
(236, 'product', 457, 'mik9.jpg', '/Images/mik9.jpg', 'AKG P220', NULL, NULL, 1, '2026-01-30 09:58:12'),
(237, 'product', 458, 'mik10.jpg', '/Images/mik10.jpg', 'Shure MV7', NULL, NULL, 1, '2026-01-30 09:58:12'),
(238, 'product', 459, 'hang7.jpg', '/Images/hang7.jpg', 'Presonus Eris E5', NULL, NULL, 1, '2026-01-30 09:58:12'),
(239, 'product', 460, 'hang8.jpg', '/Images/hang8.jpg', 'Pioneer DJ DM-50D', NULL, NULL, 1, '2026-01-30 09:58:12'),
(240, 'product', 461, 'hang9.jpg', '/Images/hang9.jpg', 'Sony SRS-XB33', NULL, NULL, 1, '2026-01-30 09:58:12'),
(241, 'product', 462, 'hang10.jpg', '/Images/hang10.jpg', 'JBL Charge 5', NULL, NULL, 1, '2026-01-30 09:58:12'),
(242, 'product', 463, 'tart7.jpg', '/Images/tart7.jpg', 'Gitar pengeto szett 20db', NULL, NULL, 1, '2026-01-30 09:58:12'),
(243, 'product', 464, 'tart8.jpg', '/Images/tart8.jpg', 'Mikrofon allvany', NULL, NULL, 1, '2026-01-30 09:58:12'),
(244, 'product', 465, 'tart9.jpg', '/Images/tart9.jpg', 'Hangfalkabel 5m', NULL, NULL, 1, '2026-01-30 09:58:12'),
(245, 'product', 466, 'tart10.jpg', '/Images/tart10.jpg', 'USB audio interface', NULL, NULL, 1, '2026-01-30 09:58:12'),
(246, 'other', NULL, 'Track 1 - A eerie unsettling song, with electronic dark ambient style, for a industrial finance.mp4', '/uploads/Track 1 - A eerie unsettling song, with electronic dark ambient style, for a industrial finance.mp4', 'Promo video', 'video/mp4', 35923919, 1, '2026-02-11 12:00:00'),
(247, 'other', NULL, 'Erik.jpg', '/uploads/Erik.jpg', 'Carousel kep 1', 'image/jpeg', NULL, 1, '2026-02-11 12:05:00'),
(248, 'other', NULL, 'boti.jpg', '/uploads/boti.jpg', 'Carousel kep 2', 'image/jpeg', NULL, 1, '2026-02-11 12:05:00'),
(249, 'other', NULL, 'dobok.jpg', '/uploads/dobok.jpg', 'Carousel kep 3', 'image/jpeg', NULL, 1, '2026-02-11 12:05:00'),
(250, 'other', NULL, 'mic.jpg', '/uploads/mic.jpg', 'Carousel kep 4', 'image/jpeg', NULL, 1, '2026-02-11 12:05:00'),
(251, 'other', NULL, 'harph.jpeg', '/uploads/harph.jpeg', 'Carousel kep 5', 'image/jpeg', NULL, 1, '2026-02-11 12:05:00'),
(252, 'other', NULL, 'dobocska.jpg', '/uploads/dobocska.jpg', 'Carousel kep 6', 'image/jpeg', NULL, 1, '2026-02-11 12:05:00'),
(253, 'other', NULL, 'bass2.jpg', '/uploads/bass2.jpg', 'Carousel kep 7', 'image/jpeg', NULL, 1, '2026-02-11 12:05:00'),
(254, 'other', NULL, 'meno.jpg', '/uploads/meno.jpg', 'Carousel kep 8', 'image/jpeg', NULL, 1, '2026-02-11 12:05:00');

-- --------------------------------------------------------

--
-- TĂˇbla szerkezet ehhez a tĂˇblĂˇhoz `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `hang` varchar(255) DEFAULT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `image` longblob,
  `image_type` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- A tĂˇbla adatainak kiĂ­ratĂˇsa `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `created_at`, `hang`, `category_id`, `image`, `image_type`) VALUES
(1, 'Yamaha Pacifica 112V', 'Yamaha Pacifica 112V elektromos gitar, stabil hangolassal es sokoldalu hangzassal.', '135000.00', '2025-12-01 09:10:15', 'hangok/git1.mp3', 1, NULL, NULL),
(2, 'Fender Stratocaster', 'Fender Stratocaster elektromos gitar, stabil hangolassal es sokoldalu hangzassal.', '420000.00', '2025-12-01 09:10:15', 'hangok/git2.mp3', 1, NULL, NULL),
(3, 'Ibanez RG421', 'Ibanez RG421 elektromos gitar, stabil hangolassal es sokoldalu hangzassal.', '180000.00', '2025-12-01 09:10:15', 'hangok/git3.mp3', 1, NULL, NULL),
(4, 'Epiphone Les Paul', 'Epiphone Les Paul elektromos gitar, stabil hangolassal es sokoldalu hangzassal.', '190000.00', '2025-12-01 09:10:15', 'hangok/git4.mp3', 1, NULL, NULL),
(5, 'PRS SE Custom 24', 'PRS SE Custom 24 elektromos gitar, stabil hangolassal es sokoldalu hangzassal.', '320000.00', '2025-12-01 09:10:15', 'hangok/git5.mp3', 1, NULL, NULL),
(101, 'Ibanez GSR200', '', '120000.00', '2025-12-08 08:57:27', 'hangok/bass1.mp3', 2, NULL, NULL),
(102, 'Yamaha TRBX174', '', '150000.00', '2025-12-08 08:57:27', 'hangok/bass2.mp3', 2, NULL, NULL),
(103, 'Fender Precision Bass', '', '380000.00', '2025-12-08 08:57:27', 'hangok/bass3.mp3', 2, NULL, NULL),
(104, 'Jackson JS3 Spectra', '', '245000.00', '2025-12-08 08:57:27', 'hangok/bass4.mp3', 2, NULL, NULL),
(105, 'Harley Benton JB-75', '', '110000.00', '2025-12-08 08:57:27', 'hangok/bass5.mp3', 2, NULL, NULL),
(201, 'Tama Imperialstar', '', '320000.00', '2025-12-08 09:21:56', 'hangok/drum1.mp3', 3, NULL, NULL),
(202, 'Pearl Export Series', '', '350000.00', '2025-12-08 09:21:56', 'hangok/drum2.mp3', 3, NULL, NULL),
(203, 'Mapex Tornado', '', '180000.00', '2025-12-08 09:21:56', 'hangok/drum3.mp3', 3, NULL, NULL),
(204, 'Ludwig Breakbeats', '', '160000.00', '2025-12-08 09:21:56', 'hangok/drum4.mp3', 3, NULL, NULL),
(205, 'Sonor AQX', '', '190000.00', '2025-12-08 09:21:56', 'hangok/drum5.mp3', 3, NULL, NULL),
(206, 'Gretsch Catalina Club', '', '280000.00', '2025-12-08 09:21:56', 'hangok/drum6.mp3', 3, NULL, NULL),
(207, 'Alesis Nitro Mesh Kit (elektromos)', '', '170000.00', '2025-12-08 09:21:56', 'hangok/drum7.mp3', 3, NULL, NULL),
(208, 'Roland TD-1DMK (elektromos)', '', '280000.00', '2025-12-08 09:21:56', 'hangok/drum8.mp3', 3, NULL, NULL),
(209, 'Millenium MX222BX Standard', '', '85000.00', '2025-12-08 09:21:56', 'hangok/drum9.mp3', 3, NULL, NULL),
(210, 'Mapex Mars Rock Set', '', '240000.00', '2025-12-08 09:21:56', 'hangok/drum1.mp3', 3, NULL, NULL),
(301, 'Pearl Roadshow 5-PC', 'Dob szett #1', '245000.00', '2025-12-08 09:16:30', NULL, NULL, NULL, NULL),
(302, 'Tama Imperialstar', 'Dob szett #2', '320000.00', '2025-12-08 09:16:30', 'hangok/drum1.mp3', 3, NULL, NULL),
(303, 'Mapex Tornado 22', 'Dob szett #3', '210000.00', '2025-12-08 09:16:30', NULL, NULL, NULL, NULL),
(304, 'Ludwig Accent Drive', 'Dob szett #4', '195000.00', '2025-12-08 09:16:30', NULL, NULL, NULL, NULL),
(305, 'Sonor AQX Jazz Set', 'Dob szett #5', '165000.00', '2025-12-08 09:16:30', NULL, NULL, NULL, NULL),
(306, 'Tama Imperialstar', 'MinĹ‘sĂ©gi kezdĹ‘/kĂ¶zĂ©phaladĂł dobfelszerelĂ©s', '320000.00', '2025-12-08 09:19:20', 'hangok/drum1.mp3', 3, NULL, NULL),
(307, 'Pearl Export Series', 'A vilĂˇg egyik legnĂ©pszerĹ±bb dobszettje', '350000.00', '2025-12-08 09:19:20', 'hangok/drum2.mp3', 3, NULL, NULL),
(308, 'Mapex Tornado', 'KezdĹ‘knek ideĂˇlis alap szett', '180000.00', '2025-12-08 09:19:20', 'hangok/drum3.mp3', 3, NULL, NULL),
(309, 'Ludwig Breakbeats', 'Kis mĂ©retĹ±, hordozhatĂł dobfelszerelĂ©s', '160000.00', '2025-12-08 09:19:20', 'hangok/drum4.mp3', 3, NULL, NULL),
(310, 'Sonor AQX', 'TartĂłs, kompakt kialakĂ­tĂˇs', '190000.00', '2025-12-08 09:19:20', 'hangok/drum5.mp3', 3, NULL, NULL),
(311, 'Gretsch Catalina Club', 'Vintage hangzĂˇs, prĂ©mium anyagok', '280000.00', '2025-12-08 09:19:20', 'hangok/drum6.mp3', 3, NULL, NULL),
(312, 'Alesis Nitro Mesh Kit (elektromos)', 'Mesh bĹ‘rĂ¶k, elektronikus modul', '170000.00', '2025-12-08 09:19:20', 'hangok/drum7.mp3', 3, NULL, NULL),
(313, 'Roland TD-1DMK (elektromos)', 'Dupla mesh kialakĂ­tĂˇs, profi elektronikus dob', '280000.00', '2025-12-08 09:19:20', 'hangok/drum8.mp3', 3, NULL, NULL),
(314, 'Millenium MX222BX Standard', 'MegfizethetĹ‘ belĂ©pĹ‘ szintĹ± dobszett', '85000.00', '2025-12-08 09:19:20', 'hangok/drum9.mp3', 3, NULL, NULL),
(315, 'Mapex Mars Rock Set', 'ErĹ‘s, rockra optimalizĂˇlt dobfelszerelĂ©s', '240000.00', '2025-12-08 09:19:20', 'hangok/drum1.mp3', 3, NULL, NULL),
(316, 'Yamaha PSR-E373', '', '85000.00', '2025-12-08 09:28:26', 'hangok/synth01.wav', 4, NULL, NULL),
(317, 'Casio CT-X700', '', '78000.00', '2025-12-08 09:28:26', 'hangok/synth02.wav', 4, NULL, NULL),
(318, 'Roland GO:Keys', '', '130000.00', '2025-12-08 09:28:26', 'hangok/synth03.wav', 4, NULL, NULL),
(319, 'Korg B2', '', '160000.00', '2025-12-08 09:28:26', 'hangok/synth04.wav', 4, NULL, NULL),
(320, 'Yamaha P-45', '', '180000.00', '2025-12-08 09:28:26', 'hangok/synth05.wav', 4, NULL, NULL),
(321, 'Kawai ES110', '', '245000.00', '2025-12-08 09:28:26', 'hangok/synth06.wav', 4, NULL, NULL),
(322, 'Alesis Recital Pro', '', '120000.00', '2025-12-08 09:28:26', 'hangok/synth07.wav', 4, NULL, NULL),
(323, 'Kurzweil KP100', '', '70000.00', '2025-12-08 09:28:26', 'hangok/synth08.wav', 4, NULL, NULL),
(324, 'Roland FP-10', '', '210000.00', '2025-12-08 09:28:26', 'hangok/synth09.wav', 4, NULL, NULL),
(325, 'Nord Electro 6D', '', '780000.00', '2025-12-08 09:28:26', 'hangok/synth10.wav', 4, NULL, NULL),
(326, 'Shure SM58', '', '42000.00', '2025-12-08 09:33:22', NULL, 5, NULL, NULL),
(327, 'AKG P120', '', '32000.00', '2025-12-08 09:33:22', NULL, 5, NULL, NULL),
(328, 'Audio-Technica AT2020', '', '55000.00', '2025-12-08 09:33:22', NULL, 5, NULL, NULL),
(329, 'Rode NT1-A', '', '85000.00', '2025-12-08 09:33:22', NULL, 5, NULL, NULL),
(330, 'Blue Yeti USB', '', '45000.00', '2025-12-08 09:33:22', NULL, 5, NULL, NULL),
(331, 'HyperX QuadCast', '', '60000.00', '2025-12-08 09:33:22', NULL, 5, NULL, NULL),
(332, 'Sennheiser E835', '', '35000.00', '2025-12-08 09:33:22', NULL, 5, NULL, NULL),
(333, 'Behringer C-1', '', '18000.00', '2025-12-08 09:33:22', NULL, 5, NULL, NULL),
(334, 'Samson C01U Pro', '', '38000.00', '2025-12-08 09:33:22', NULL, 5, NULL, NULL),
(335, 'Rode PodMic', '', '45000.00', '2025-12-08 09:33:22', NULL, 5, NULL, NULL),
(336, 'GitĂˇrpengetĹ‘ kĂ©szlet', '', '1500.00', '2025-12-08 09:37:47', NULL, NULL, NULL, NULL),
(337, 'Jack-Jack kĂˇbel 3m', '', '3500.00', '2025-12-08 09:37:47', NULL, NULL, NULL, NULL),
(338, 'GitĂˇr ĂˇllvĂˇny', '', '6500.00', '2025-12-08 09:37:47', NULL, NULL, NULL, NULL),
(339, 'Hangszer tisztĂ­tĂł spray', '', '2500.00', '2025-12-08 09:37:47', NULL, NULL, NULL, NULL),
(340, 'Mikrofon tartĂł ĂˇllvĂˇny', '', '8000.00', '2025-12-08 09:37:47', NULL, NULL, NULL, NULL),
(341, 'FejhallgatĂł (Basic Studio)', '', '9000.00', '2025-12-08 09:37:47', NULL, NULL, NULL, NULL),
(342, 'GitĂˇr hĂşrkĂ©szlet (Ernie Ball)', '', '3500.00', '2025-12-08 09:37:47', NULL, NULL, NULL, NULL),
(343, 'DobverĹ‘ pĂˇr (5A)', '', '2500.00', '2025-12-08 09:37:47', NULL, NULL, NULL, NULL),
(344, 'BillentyĹ±zet ĂˇllvĂˇny', '', '12000.00', '2025-12-08 09:37:47', NULL, NULL, NULL, NULL),
(345, 'KottaĂˇllvĂˇny', '', '5500.00', '2025-12-08 09:37:47', NULL, NULL, NULL, NULL),
(346, 'Yamaha HS5 stĂşdiĂłmonitor', 'Tiszta Ă©s rĂ©szletes hangkĂ©p, stĂşdiĂłkra tervezve. IdeĂˇlis kisebb helyisĂ©gekbe.', '68000.00', '2025-12-09 08:57:26', NULL, NULL, NULL, NULL),
(347, 'KRK Rokit 5 G4', 'Modern DSP vezĂ©rlĂ©ssel, erĹ‘s mĂ©lyekkel Ă©s kivĂˇlĂł hangnyomĂˇssal rendelkezĹ‘ stĂşdiĂłmonitor.', '85000.00', '2025-12-09 08:57:26', NULL, 7, NULL, NULL),
(348, 'JBL 305P MKII', 'Nagyon szĂ©les sweet spot, tiszta magasak Ă©s kiegyensĂşlyozott hangzĂˇs. Nagyon nĂ©pszerĹ± model.', '62000.00', '2025-12-09 08:57:26', NULL, 7, NULL, NULL),
(349, 'Behringer Eurolive B112D', 'ErĹ‘s, 1000W aktĂ­v PA hangfal Ă©lĹ‘ zenĂ©szeknek, rendezvĂ©nyekhez Ă©s prĂłbatermekbe.', '78000.00', '2025-12-09 08:57:26', NULL, 7, NULL, NULL),
(350, 'Mackie CR4-X', 'StĂşdiĂłmonitor tiszta kĂ¶zĂ©ptartomĂˇnnyal Ă©s erĹ‘s sztereĂłkĂ©ppel. IdeĂˇlis kisebb home stĂşdiĂłkhoz.', '42000.00', '2025-12-09 08:59:00', NULL, 7, NULL, NULL),
(351, 'Sony Bluetooth Hangfal', 'Kompakt mĂ©ret, erĹ‘teljes basszus, megbĂ­zhatĂł Bluetooth kapcsolat. KĂĽltĂ©rre Ă©s beltĂ©rre is tĂ¶kĂ©letes.', '35000.00', '2025-12-09 08:59:00', NULL, 7, NULL, NULL),
(352, 'Marshall Emberton II', 'PrĂ©mium hordozhatĂł hangfal ikonikus Marshall designnal. Nagyon tiszta hangzĂˇs Ă©s masszĂ­v felĂ©pĂ­tĂ©s.', '52000.00', '2025-12-09 08:59:00', NULL, 7, NULL, NULL),
(353, 'Bose SoundLink Flex', 'Bose minĹ‘sĂ©g ĂştkĂ¶zben is. RĂ©szletes hangzĂˇs, vĂ­zĂˇllĂł hĂˇz Ă©s erĹ‘s mĂ©lyek egy kompakt formĂˇban.', '62000.00', '2025-12-09 08:59:00', NULL, 7, NULL, NULL),
(354, 'Presonus Eris E3.5', 'KivĂˇlĂł Ăˇr-Ă©rtĂ©k arĂˇnyĂş stĂşdiĂłmonitor, meglepĹ‘en pontos kĂ¶zĂ©p- Ă©s magas tartomĂˇnnyal.', '42000.00', '2025-12-09 08:59:00', NULL, 7, NULL, NULL),
(355, 'Pioneer DJ DM-40', 'DJ-ek Ă©s producerek kedvence: ĂĽtĹ‘s basszus, szĂ©les sztereĂłkĂ©p Ă©s kompakt mĂ©ret.', '58000.00', '2025-12-09 08:59:00', NULL, 7, NULL, NULL),
(376, 'GitĂˇr heveder', '', '3000.00', '2025-12-09 09:37:36', NULL, NULL, NULL, NULL),
(377, 'HangszerkĂˇbel', '', '2500.00', '2025-12-09 09:37:36', NULL, NULL, NULL, NULL),
(378, 'GitĂˇrhĂşr kĂ©szlet', '', '4500.00', '2025-12-09 09:37:36', NULL, NULL, NULL, NULL),
(379, 'HĂşzĂłkulcs', '', '800.00', '2025-12-09 09:37:36', NULL, NULL, NULL, NULL),
(380, 'Jackâ€“Jack kĂˇbel', '', '3500.00', '2025-12-09 09:37:36', NULL, NULL, NULL, NULL),
(381, 'Effekt pedĂˇl tĂˇp', '', '7000.00', '2025-12-09 09:37:36', NULL, NULL, NULL, NULL),
(382, 'GitĂˇrpengetĹ‘ 10db', '', '1200.00', '2025-12-09 09:37:36', NULL, NULL, NULL, NULL),
(383, 'DobverĹ‘ pĂˇr', '', '2500.00', '2025-12-09 09:37:36', NULL, NULL, NULL, NULL),
(384, 'Keyboard ĂˇllvĂˇny', '', '9000.00', '2025-12-09 09:37:36', NULL, NULL, NULL, NULL),
(385, 'FejhallgatĂł adapter', '', '1500.00', '2025-12-09 09:37:36', NULL, NULL, NULL, NULL),
(386, 'Squier Classic Vibe Telecaster', 'Klasszikus single-coil hangzas, vintage vibe es kenyelmes nyak.', '165000.00', '2026-01-30 08:52:00', 'hangok/git1.mp3', 1, NULL, NULL),
(387, 'Jackson Dinky JS22', 'Agressziv modern forma, gyors nyak es eros hang.', '155000.00', '2026-01-30 08:52:00', 'hangok/git2.mp3', 1, NULL, NULL),
(388, 'ESP LTD EC-256', 'Humbucker paros, meleg es vastag rock hang.', '210000.00', '2026-01-30 08:52:00', 'hangok/git3.mp3', 1, NULL, NULL),
(389, 'Schecter C-6 Deluxe', 'Sokoldalu, modern jatek elmeny tiszta es torzitott hanggal.', '175000.00', '2026-01-30 08:52:00', 'hangok/git4.mp3', 1, NULL, NULL),
(390, 'Gretsch G2622 Streamliner', 'Half-hollow karakter, duses, meleg hangzas.', '260000.00', '2026-01-30 08:52:00', 'hangok/git5.mp3', 1, NULL, NULL),
(391, 'Yamaha Revstar RS320', 'Letisztult dizajn, kiegyensulyozott, modern hang.', '185000.00', '2026-01-30 08:52:00', 'hangok/git1.mp3', 1, NULL, NULL),
(392, 'Cort Action Bass Plus', 'Aktiv elektronika, eros, hatarozott basszus.', '125000.00', '2026-01-30 08:52:00', 'hangok/bass1.mp3', 2, NULL, NULL),
(393, 'Ibanez SR300E', 'Konnyu test, gyors jatek es modern hang.', '185000.00', '2026-01-30 08:52:00', 'hangok/bass2.mp3', 2, NULL, NULL),
(394, 'Squier Affinity Jazz Bass', 'Klasszikus jazz bass karakter, tiszta hang.', '140000.00', '2026-01-30 08:52:00', 'hangok/bass3.mp3', 2, NULL, NULL),
(395, 'Warwick RockBass Corvette', 'Feszes kozep, vastag hangzas, massziv erzes.', '220000.00', '2026-01-30 08:52:00', 'hangok/bass4.mp3', 2, NULL, NULL),
(396, 'Music Man SUB Ray4', 'Punchy slap hangzas es eros attack.', '190000.00', '2026-01-30 08:52:00', 'hangok/bass5.mp3', 2, NULL, NULL),
(397, 'Fender Telecaster Player', 'Tiszta, csilingelo hang, klasszikus rock es blues karakter.', '235000.00', '2026-01-30 08:56:40', 'hangok/git1.mp3', 1, NULL, NULL),
(398, 'Ibanez AZES40', 'Modern forma, sima nyak, sokoldalu hangzas.', '195000.00', '2026-01-30 08:56:40', 'hangok/git2.mp3', 1, NULL, NULL),
(399, 'Gibson SG Tribute', 'Meleg, vastag rock hang, eros sustain.', '430000.00', '2026-01-30 08:56:40', 'hangok/git3.mp3', 1, NULL, NULL),
(400, 'Cort G250', 'Kiegyensulyozott, kenyelmes hangszer mindennapi jatekra.', '145000.00', '2026-01-30 08:56:40', 'hangok/git4.mp3', 1, NULL, NULL),
(401, 'Squier Classic Vibe Jazzmaster', 'Vintage hang, szeles dinamika, ikonikus forma.', '210000.00', '2026-01-30 08:56:40', 'hangok/git5.mp3', 1, NULL, NULL),
(402, 'ESP LTD Viper-256', 'Sotetebb hang, erosebb torzitasra is jo.', '240000.00', '2026-01-30 08:56:40', 'hangok/git1.mp3', 1, NULL, NULL),
(403, 'Yamaha BB234', 'Massziv basszus, feszes kozep, megbizhato konstrukcio.', '175000.00', '2026-01-30 08:56:40', 'hangok/bass1.mp3', 2, NULL, NULL),
(404, 'Squier Classic Vibe Precision', 'Klasszikus P-basszus karakter, punchy hang.', '195000.00', '2026-01-30 08:56:40', 'hangok/bass2.mp3', 2, NULL, NULL),
(405, 'Ibanez TMB100', 'Retro dizajn, meleg hang, kenyelmes jatek.', '125000.00', '2026-01-30 08:56:40', 'hangok/bass3.mp3', 2, NULL, NULL),
(406, 'Cort Artisan B4', 'Modern, aktiv hangzas, tiszta definicio.', '215000.00', '2026-01-30 08:56:40', 'hangok/bass4.mp3', 2, NULL, NULL),
(407, 'Schecter Stiletto Extreme-4', 'Eros low-end, modern karakter, stabil nyak.', '230000.00', '2026-01-30 08:56:40', 'hangok/bass5.mp3', 2, NULL, NULL),
(408, 'Fender Jazz Bass Player', 'Ikonikus jazz bass hang, reszletgazdag kozep.', '310000.00', '2026-01-30 08:56:40', 'hangok/bass1.mp3', 2, NULL, NULL),
(409, 'Pearl Roadshow', 'Megbizhato kezdo szett, jo ar-ertek.', '180000.00', '2026-01-30 08:56:40', 'hangok/drum1.mp3', 3, NULL, NULL),
(410, 'Yamaha Stage Custom', 'Birch test, tiszta attack, profi hang.', '420000.00', '2026-01-30 08:56:40', 'hangok/drum2.mp3', 3, NULL, NULL),
(411, 'Mapex Armory', 'Hibrid test, sokoldalu dobhang.', '360000.00', '2026-01-30 08:56:40', 'hangok/drum3.mp3', 3, NULL, NULL),
(412, 'Tama Superstar Classic', 'Classic maple karakter, eros attack.', '390000.00', '2026-01-30 08:56:40', 'hangok/drum4.mp3', 3, NULL, NULL),
(413, 'Ludwig Element Evolution', 'Stabil, meleg hang, konnyu hangolni.', '260000.00', '2026-01-30 08:56:40', 'hangok/drum5.mp3', 3, NULL, NULL),
(414, 'Gretsch Energy', 'Nagy test, erosebb hang, jo studio hasznalat.', '290000.00', '2026-01-30 08:56:40', 'hangok/drum6.mp3', 3, NULL, NULL),
(415, 'Casio CDP-S110', 'Kompakt, konnyu, alap zongora hang.', '98000.00', '2026-01-30 08:56:40', 'hangok/synth01.wav', 4, NULL, NULL),
(416, 'Yamaha P-125', 'Realisztikus billentyu erzet, tiszta hang.', '240000.00', '2026-01-30 08:56:40', 'hangok/synth02.wav', 4, NULL, NULL),
(417, 'Korg B2N', 'Kenyelmes jatekerzet, jo hangzas ar-ertekben.', '180000.00', '2026-01-30 08:56:40', 'hangok/synth03.wav', 4, NULL, NULL),
(418, 'Roland GO:Keys 3', 'Hordozhato, modern hangok, gyors inspiracio.', '210000.00', '2026-01-30 08:56:40', 'hangok/synth04.wav', 4, NULL, NULL),
(419, 'Alesis Prestige', 'Semi-weighted erzet, elegans design.', '185000.00', '2026-01-30 08:56:40', 'hangok/synth05.wav', 4, NULL, NULL),
(420, 'Kurzweil SP1', 'Professzionalis hangkeszlet, megbizhato.', '310000.00', '2026-01-30 08:56:40', 'hangok/synth06.wav', 4, NULL, NULL),
(421, 'Shure SM7B', 'Studio es podcast standard, meleg, zajmentes hang.', '165000.00', '2026-01-30 08:56:40', 'hangok/synth07.wav', 5, NULL, NULL),
(422, 'Rode NT-USB', 'USB kondenzator, tiszta reszletes hang.', '69000.00', '2026-01-30 08:56:40', 'hangok/synth08.wav', 5, NULL, NULL),
(423, 'AKG D5', 'Elo vokalra, tiszta, eros jelenlet.', '55000.00', '2026-01-30 08:56:40', 'hangok/synth09.wav', 5, NULL, NULL),
(424, 'Audio-Technica AT2035', 'Nagy membran, reszletgazdag felvetel.', '75000.00', '2026-01-30 08:56:40', 'hangok/synth10.wav', 5, NULL, NULL),
(425, 'Sennheiser e935', 'Szinpadra, eros es stabil hang.', '82000.00', '2026-01-30 08:56:40', 'hangok/synth01.wav', 5, NULL, NULL),
(426, 'Behringer XM8500', 'Budget, megbizhato, jo ar-ertek.', '17000.00', '2026-01-30 08:56:40', 'hangok/synth02.wav', 5, NULL, NULL),
(427, 'Yamaha HS7', 'Studio monitor, kiegyensulyozott referancia.', '98000.00', '2026-01-30 08:56:40', 'hangok/drum7.mp3', 7, NULL, NULL),
(428, 'KRK Rokit 7 G4', 'Melyebb basszus, modern karakter.', '115000.00', '2026-01-30 08:56:40', 'hangok/drum8.mp3', 7, NULL, NULL),
(429, 'JBL 306P MKII', 'Tiszta kozep, pontos stereo kep.', '92000.00', '2026-01-30 08:56:40', 'hangok/drum9.mp3', 7, NULL, NULL),
(430, 'Mackie CR5-X', 'Sokoldalu monitor, kompakt meret.', '58000.00', '2026-01-30 08:56:40', 'hangok/git2.mp3', 7, NULL, NULL),
(431, 'Bose SoundLink Mini', 'Hordozhato, meleg hang, eros basszus.', '82000.00', '2026-01-30 08:56:40', 'hangok/git3.mp3', 7, NULL, NULL),
(432, 'Marshall Stanmore II', 'Rockos karakter, eros hangnyomas.', '140000.00', '2026-01-30 08:56:40', 'hangok/git4.mp3', 7, NULL, NULL),
(433, 'Gitar allvany', 'Stabil tartas, osszecsukhato kialakitas.', '8500.00', '2026-01-30 08:56:40', 'hangok/git5.mp3', 6, NULL, NULL),
(434, 'Billentyu pad', 'Puha boritas, kenyelmes jatek.', '12000.00', '2026-01-30 08:56:40', 'hangok/synth03.wav', 6, NULL, NULL),
(435, 'Kapodaszter', 'Gyors felhelyezes, stabil hangolas.', '3500.00', '2026-01-30 08:56:40', 'hangok/git1.mp3', 6, NULL, NULL),
(436, 'Hangszer tisztito keszlet', 'Polir es vedo, tiszta felulet.', '6500.00', '2026-01-30 08:56:40', 'hangok/synth04.wav', 6, NULL, NULL),
(437, 'Metronom', 'Pontos tempo, konnyu beallitas.', '9800.00', '2026-01-30 08:56:40', 'hangok/drum2.mp3', 6, NULL, NULL),
(438, 'Pot hurok szett', 'Tartalek hurok, tiszta hangzas.', '4200.00', '2026-01-30 08:56:40', 'hangok/git2.mp3', 6, NULL, NULL),
(439, 'Harley Benton ST-20', 'Kezdo gitar, konnyu jatek, jo ar-ertek.', '59000.00', '2026-01-30 08:58:12', 'hangok/git2.mp3', 1, NULL, NULL),
(440, 'Squier Bullet Strat', 'Klasszikus strat forma, tiszta hang.', '75000.00', '2026-01-30 08:58:12', 'hangok/git3.mp3', 1, NULL, NULL),
(441, 'Yamaha Pacifica 012', 'Stabil hangolas, kenyelmes nyak.', '89000.00', '2026-01-30 08:58:12', 'hangok/git4.mp3', 1, NULL, NULL),
(442, 'Epiphone SG Standard', 'Rock hang, eros sustain, konnyu test.', '210000.00', '2026-01-30 08:58:12', 'hangok/git5.mp3', 1, NULL, NULL),
(443, 'Harley Benton PJ-4', 'PJ pickup paros, sokoldalu basszus.', '72000.00', '2026-01-30 08:58:12', 'hangok/bass2.mp3', 2, NULL, NULL),
(444, 'Yamaha TRBX305', 'Aktiv basszus, modern low-end.', '195000.00', '2026-01-30 08:58:12', 'hangok/bass3.mp3', 2, NULL, NULL),
(445, 'Ibanez SRMD200', 'Rovid menzura, konnyu jatek.', '125000.00', '2026-01-30 08:58:12', 'hangok/bass4.mp3', 2, NULL, NULL),
(446, 'Squier Affinity PJ Bass', 'Klasszikus PJ hang, stabil konstrukcio.', '120000.00', '2026-01-30 08:58:12', 'hangok/bass5.mp3', 2, NULL, NULL),
(447, 'Alesis Nitro Max (elektromos)', 'Elektronikus szett, csendes gyakorlashoz.', '170000.00', '2026-01-30 08:58:12', 'hangok/drum7.mp3', 3, NULL, NULL),
(448, 'Roland TD-07KV (elektromos)', 'Profi elektronikus dob, dinamikus hang.', '420000.00', '2026-01-30 08:58:12', 'hangok/drum8.mp3', 3, NULL, NULL),
(449, 'Millenium MPS-850 (elektromos)', 'Jol felszerelt, jo ar-ertek.', '220000.00', '2026-01-30 08:58:12', 'hangok/drum9.mp3', 3, NULL, NULL),
(450, 'Yamaha Rydeen', 'Megbizhato akusztikus szett, konnyu hangolas.', '240000.00', '2026-01-30 08:58:12', 'hangok/drum1.mp3', 3, NULL, NULL),
(451, 'Kawai ES120', 'Kompakt digitalis zongora, szep hang.', '280000.00', '2026-01-30 08:58:12', 'hangok/synth07.wav', 4, NULL, NULL),
(452, 'Casio CT-S1', 'Hordozhato, modern hangzas, gyors beallitas.', '135000.00', '2026-01-30 08:58:12', 'hangok/synth08.wav', 4, NULL, NULL),
(453, 'Roland FP-30X', 'Realisztikus billentyu erzet, eros hang.', '320000.00', '2026-01-30 08:58:12', 'hangok/synth09.wav', 4, NULL, NULL),
(454, 'Nord Piano 5', 'Professzionalis hangkeszlet, szinpadra.', '1280000.00', '2026-01-30 08:58:12', 'hangok/synth10.wav', 4, NULL, NULL),
(455, 'Rode PodMic', 'Podcast mikrofon, meleg hang.', '42000.00', '2026-01-30 08:58:12', 'hangok/synth03.wav', 5, NULL, NULL),
(456, 'Blue Yeti X', 'USB mikrofon, sokoldalu felvetel.', '69000.00', '2026-01-30 08:58:12', 'hangok/synth04.wav', 5, NULL, NULL),
(457, 'AKG P220', 'Nagy membran, studio hangzas.', '82000.00', '2026-01-30 08:58:12', 'hangok/synth05.wav', 5, NULL, NULL),
(458, 'Shure MV7', 'Hybrid USB/XLR, tiszta hang.', '105000.00', '2026-01-30 08:58:12', 'hangok/synth06.wav', 5, NULL, NULL),
(459, 'Presonus Eris E5', 'Studio monitor par, tiszta kozep.', '76000.00', '2026-01-30 08:58:12', 'hangok/git1.mp3', 7, NULL, NULL),
(460, 'Pioneer DJ DM-50D', 'DJ monitor, eros basszus.', '98000.00', '2026-01-30 08:58:12', 'hangok/git2.mp3', 7, NULL, NULL),
(461, 'Sony SRS-XB33', 'Bluetooth hangfal, extra bass.', '78000.00', '2026-01-30 08:58:12', 'hangok/git3.mp3', 7, NULL, NULL),
(462, 'JBL Charge 5', 'Hordozhato, eros hang, jo akkuidok.', '65000.00', '2026-01-30 08:58:12', 'hangok/git4.mp3', 7, NULL, NULL),
(463, 'Gitar pengeto szett 20db', 'Kulonbozo vastagsagok, praktikus.', '1500.00', '2026-01-30 08:58:12', 'hangok/git1.mp3', 6, NULL, NULL),
(464, 'Mikrofon allvany', 'Stabil allvany, allithato magassag.', '12000.00', '2026-01-30 08:58:12', 'hangok/synth01.wav', 6, NULL, NULL),
(465, 'Hangfalkabel 5m', 'Megbizhato kabel, tiszta jel.', '3900.00', '2026-01-30 08:58:12', 'hangok/drum3.mp3', 6, NULL, NULL),
(466, 'USB audio interface', 'Alap hangkartya felvetelhez.', '32000.00', '2026-01-30 08:58:12', 'hangok/synth02.wav', 6, NULL, NULL);

-- --------------------------------------------------------

--
-- TĂˇbla szerkezet ehhez a tĂˇblĂˇhoz `product_variants`
--

CREATE TABLE `product_variants` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_name` varchar(100) NOT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `stock` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- A tĂˇbla adatainak kiĂ­ratĂˇsa `product_variants`
--

INSERT INTO `product_variants` (`id`, `product_id`, `variant_name`, `price`, `stock`) VALUES
(1, 1, 'Test', NULL, 5);

-- --------------------------------------------------------

--
-- TĂˇbla szerkezet ehhez a tĂˇblĂˇhoz `profile_messages`
--

CREATE TABLE `profile_messages` (
  `id` int(11) NOT NULL,
  `target_user_id` int(11) NOT NULL,
  `author_user_id` int(11) DEFAULT NULL,
  `author_name` varchar(80) DEFAULT NULL,
  `message` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- TĂˇbla szerkezet ehhez a tĂˇblĂˇhoz `direct_messages`
--

CREATE TABLE `direct_messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `recipient_id` int(11) NOT NULL,
  `item_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- TĂˇbla szerkezet ehhez a tĂˇblĂˇhoz `profile_meta`
--

CREATE TABLE `profile_meta` (
  `user_id` int(11) NOT NULL,
  `bio` text,
  `country` varchar(80) DEFAULT NULL,
  `age` tinyint(4) DEFAULT NULL,
  `city` varchar(80) DEFAULT NULL,
  `instrument` varchar(80) DEFAULT NULL,
  `experience` varchar(40) DEFAULT NULL,
  `studio` varchar(120) DEFAULT NULL,
  `tags_json` text,
  `custom_tags_json` text,
  `cover_url` varchar(255) DEFAULT NULL,
  `background` varchar(20) DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- TĂˇbla szerkezet ehhez a tĂˇblĂˇhoz `profile_ratings`
--

CREATE TABLE `profile_ratings` (
  `id` int(11) NOT NULL,
  `target_user_id` int(11) NOT NULL,
  `reviewer_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL DEFAULT '0',
  `comment` text,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- TĂˇbla szerkezet ehhez a tĂˇblĂˇhoz `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL,
  `comment` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- A tĂˇbla adatainak kiĂ­ratĂˇsa `reviews`
--

INSERT INTO `reviews` (`id`, `user_id`, `product_id`, `rating`, `comment`, `created_at`) VALUES
(50, 27, 102, 3, 'asd', '2025-12-08 09:15:42'),
(62, 27, 1, 3, 'AAAAAAAAAAAAAAAAAA', '2025-12-08 09:29:11'),
(63, 27, 301, 3, 'asd', '2025-12-08 09:35:29'),
(81, 24, 2, 3, 'asd', '2025-12-09 09:23:49'),
(96, 24, 3, 4, 'asd', '2025-12-09 09:32:13'),
(116, 47, 376, 4, 'asd', '2025-12-09 09:56:32'),
(117, 47, 2, 3, 'asd', '2025-12-09 09:56:37'),
(118, 47, 377, 4, 'asd', '2025-12-09 09:56:50'),
(119, 47, 301, 4, 'asd', '2025-12-09 09:57:10'),
(120, 47, 201, 3, 'asd', '2025-12-09 09:57:18'),
(122, 48, 1, 3, 'asd', '2025-12-10 10:24:48'),
(123, 49, 1, 2, 'i did not like this', '2025-12-17 09:25:50'),
(124, 50, 2, 5, 'nem tetszett', '2026-01-06 10:15:12'),
(125, 51, 101, 4, 'asd', '2026-01-19 09:32:37'),
(126, 52, 101, 3, 'lol', '2026-01-19 10:07:29'),
(128, 53, 101, 3, 'er', '2026-01-20 09:40:05'),
(129, 53, 2, 2, 'asd', '2026-01-20 09:50:11');

-- --------------------------------------------------------

--
-- TĂˇbla szerkezet ehhez a tĂˇblĂˇhoz `used_items`
--

CREATE TABLE `used_items` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(120) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `category` varchar(50) DEFAULT NULL,
  `condition_label` varchar(50) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `status` enum('active','sold','inactive') NOT NULL DEFAULT 'active',
  `buyer_id` int(11) DEFAULT NULL,
  `accepted_offer_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- TĂˇbla szerkezet ehhez a tĂˇblĂˇhoz `used_offers`
--

CREATE TABLE `used_offers` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `offer_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` enum('pending','accepted','rejected') NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- TĂˇbla szerkezet ehhez a tĂˇblĂˇhoz `users`
--

CREATE TABLE `users` (
  `ID` int(11) NOT NULL COMMENT 'Identification',
  `username` varchar(30) NOT NULL,
  `email` varchar(70) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `profile_pic` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `deleted_at` datetime DEFAULT NULL,
  `email_verified` tinyint(1) NOT NULL DEFAULT '0',
  `email_verification_token` varchar(128) DEFAULT NULL,
  `email_verification_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='BejelentkezĂ©si adatok';

--
-- A tĂˇbla adatainak kiĂ­ratĂˇsa `users`
--

INSERT INTO `users` (`ID`, `username`, `email`, `password_hash`, `role`, `created_at`, `profile_pic`, `is_active`, `deleted_at`, `email_verified`, `email_verification_token`, `email_verification_expires`) VALUES
(24, 'erik', 'erik@erik', '$2y$10$uk.NpusviZXCN9nNdU6x4.s6yYdZ4VpwJPAiPriDbhSniGliqtyeC', 'user', '2025-11-21 08:12:12', 'pfp_24_1763719991.png', 1, NULL, 0, '934955959d973c5729f5053da551b0b04fe2cb1a36fa9693172d265bacd08709', '2026-02-12 10:18:10'),
(25, '6', '6@6', '$2y$10$GOTeooeg2mSHR6VCMCvze.Sd/Hm.rqDMjhEG.Qv9FQDq71F4l05Ea', 'user', '2025-11-21 09:48:57', 'pfp_25_1763718860.png', 1, NULL, 0, NULL, NULL),
(26, 'Er', 'er@er', '$2y$10$N2HDEaFsQ5mkgpdIGfjgd.l/Wpa9hZyTEXJfWngwZmLYOywiMepLW', 'user', '2025-12-01 08:39:11', 'pfp_26_1764578382.png', 1, NULL, 0, NULL, NULL),
(27, 'erik23', 'erik23@gmail.com', '$2y$10$lIVyT6U/QIvdz/SpBHaCT.RW2CqSPdMB39NlscqRKYaFueRZfXVN2', 'user', '2025-12-08 09:07:46', 'pfp_27_1765184888.jpg', 1, NULL, 0, NULL, NULL),
(47, 'erik34', 'er34@gmail.com', '$2y$10$CpmKCeTNNF99eQ6Wv0kVVORRlfFnnEdPTvB.r9N7qxOR4u5Qx8pZ6', 'user', '2025-12-09 09:49:58', 'pfp_47_1765273977.png', 1, NULL, 0, NULL, NULL),
(48, 'erik45', 'ERIK45@gmail.com', '$2y$10$YenTcT4TTcgqaXkhTUV2MunvLlCsTrJzr0he9UuzghCbiw.6cMSua', 'user', '2025-12-10 10:24:06', 'pfp_48_1765362271.png', 1, NULL, 0, NULL, NULL),
(49, 'erik234', 'Erik234@gmail', '$2y$10$4/86WRK9aRxQ6PFIUWD.WuU0.XHPYI2xNi2ku.uDl0glqFHcGccuK', 'user', '2025-12-17 09:24:40', 'pfp_49_1765963511.png', 1, NULL, 0, NULL, NULL),
(50, 'erik32', 'erik32@gmail.com', '$2y$10$f.hxnEGBKyhvaIQtG5SHRe1beiNTqavTAplJfSD6nMOcDs6VjPIM.', 'user', '2026-01-06 09:22:30', 'pfp_50_1767694493.jpg', 1, NULL, 0, NULL, NULL),
(51, 'eri', 'eri@gmail.com', '$2y$10$5e5TrBpqoXaUPBHllhRLLuna0eFlA.jMq.MRr9wt6YQf9JoxbtyES', 'user', '2026-01-19 09:32:00', 'pfp_51_1768815136.jpg', 1, NULL, 0, NULL, NULL),
(52, 'er3', 'er3@gmail.com', '$2y$10$hj2saljZ1y7iXdHdJKuLFuRBkxtBKfySlvGlls5v1TUFVaVEtPMPe', 'user', '2026-01-19 10:06:04', 'pfp_52_1768817234.jpg', 1, NULL, 0, NULL, NULL),
(53, '12345', '12345@gmail.com', '$2y$10$TmKdnyL8lJzq/G/jryX/MOCLZDgxB9DpgIg7zxoP0NXYZOBPSwmoi', 'user', '2026-01-20 09:16:37', 'pfp_53_1768900608.jpg', 1, NULL, 0, NULL, NULL),
(55, 'erik233', 'erik23toth@gmail.com', '$2y$10$LV2Kfrr2XXpi/2c9jBvXSuErHDt72TYuotsuYr.2kgDU3/WY8rPQO', 'user', '2026-02-03 10:14:39', '../uploads/default_avatar.png', 1, NULL, 0, '4dc97aca04b877b78ea72ff3e7009ec7f84da0e971e1460a86b4302f1dc54011', '2026-02-12 10:18:33'),
(56, 'erik2333', 'nukefart26@gmail.com', '$2y$10$Stg2f0OMa.CVol8ZU/zEw.rgH/yxsQs/HN95y4TeMsvZ31sYzb22e', 'user', '2026-02-03 10:15:20', '../uploads/default_avatar.png', 1, NULL, 0, 'b5ac9922aaf975284a06810b54f0b0facaa4559ffe8b2f827a34f89f7e5d50f6', '2026-02-05 12:37:51'),
(57, 'erik26', 'boti45toth@gmail.com', '$2y$10$IazarTG1ssJf5gbHLr0kpeTOLf.0oxnEKneGGI8oIq.Hc6pNYRURC', 'user', '2026-02-03 10:42:43', '../uploads/default_avatar.png', 1, NULL, 0, 'fffb1effd55d9c1b8a8337b5b214e229b2573c7defb8d06147f8b03ec2cd043c', '2026-02-04 10:56:36'),
(58, 'admin', 'boti23toth@gmail.com', '$2y$10$Hf3y8dK3NX.uq72AqSCs3eZ/DThegwSyUv4uCIF/iyV7HWE.QE5gu', 'user', '2026-02-03 10:54:34', '../uploads/default_avatar.png', 1, NULL, 0, '63415aea3a0d4bd47c6c22941ab5d9be060d467fe2c7854b5160ec0555de7480', '2026-02-04 10:54:35'),
(59, 'testuser123', 'test123@example.com', '$2y$10$0lHU1CcR/k5d.7HppJHEyOqqWcO/CTqzdvl05QeZO14muiGLL41cq', 'user', '2026-02-04 11:01:44', 'Default.avatar.jpg', 1, NULL, 0, '43f91305ee6fb65e2c9b66802ca38911e41310c56f52ef2a803b17c9bafb417e', '2026-02-05 11:17:43');

-- --------------------------------------------------------

--
-- TĂˇbla szerkezet ehhez a tĂˇblĂˇhoz `user_activity`
--

CREATE TABLE `user_activity` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- TĂˇbla szerkezet ehhez a tĂˇblĂˇhoz `user_addresses`
--

CREATE TABLE `user_addresses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `address_line1` varchar(255) NOT NULL,
  `city` varchar(100) DEFAULT NULL,
  `zip` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- A nĂ©zet helyettes szerkezete `v_active_pictures`
-- (LĂˇsd alĂˇbb az aktuĂˇlis nĂ©zetet)
--
CREATE TABLE `v_active_pictures` (
`id` int(11)
,`owner_type` enum('product','user','other')
,`owner_id` int(11)
,`filename` varchar(255)
,`path` varchar(255)
,`alt_text` varchar(255)
,`mime_type` varchar(50)
,`size_bytes` int(11)
,`is_active` tinyint(1)
,`created_at` datetime
);

-- --------------------------------------------------------

--
-- A nĂ©zet helyettes szerkezete `v_active_users`
-- (LĂˇsd alĂˇbb az aktuĂˇlis nĂ©zetet)
--
CREATE TABLE `v_active_users` (
`ID` int(11)
,`username` varchar(30)
,`email` varchar(70)
,`password_hash` varchar(255)
,`role` enum('user','admin')
,`created_at` timestamp
,`profile_pic` varchar(255)
,`is_active` tinyint(1)
,`deleted_at` datetime
);

-- --------------------------------------------------------

--
-- NĂ©zet szerkezete `v_active_pictures`
--
DROP TABLE IF EXISTS `v_active_pictures`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_active_pictures`  AS SELECT `pictures`.`id` AS `id`, `pictures`.`owner_type` AS `owner_type`, `pictures`.`owner_id` AS `owner_id`, `pictures`.`filename` AS `filename`, `pictures`.`path` AS `path`, `pictures`.`alt_text` AS `alt_text`, `pictures`.`mime_type` AS `mime_type`, `pictures`.`size_bytes` AS `size_bytes`, `pictures`.`is_active` AS `is_active`, `pictures`.`created_at` AS `created_at` FROM `pictures` WHERE (`pictures`.`is_active` = 1)  ;

-- --------------------------------------------------------

--
-- NĂ©zet szerkezete `v_active_users`
--
DROP TABLE IF EXISTS `v_active_users`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_active_users`  AS SELECT `users`.`ID` AS `ID`, `users`.`username` AS `username`, `users`.`email` AS `email`, `users`.`password_hash` AS `password_hash`, `users`.`role` AS `role`, `users`.`created_at` AS `created_at`, `users`.`profile_pic` AS `profile_pic`, `users`.`is_active` AS `is_active`, `users`.`deleted_at` AS `deleted_at` FROM `users` WHERE (`users`.`is_active` = 1)  ;

--
-- Indexek a kiĂ­rt tĂˇblĂˇkhoz
--

--
-- A tĂˇbla indexei `bands`
--
ALTER TABLE `bands`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_bands_owner` (`owner_id`),
  ADD KEY `idx_bands_genre` (`genre`),
  ADD KEY `idx_bands_city` (`city`);

--
-- A tĂˇbla indexei `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_product_unique` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- A tĂˇbla indexei `cart_coupons`
--
ALTER TABLE `cart_coupons`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `idx_cart_coupons_coupon` (`coupon_id`);

--
-- A tĂˇbla indexei `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_coupons_code` (`code`);

--
-- A tĂˇbla indexei `hang`
--
ALTER TABLE `hang`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_hang_file_path` (`file_path`),
  ADD KEY `idx_hang_user_id` (`user_id`),
  ADD KEY `idx_hang_public_created` (`is_public`,`created_at`);

--
-- A tĂˇbla indexei `kategoria`
--
ALTER TABLE `kategoria`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- A tĂˇbla indexei `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- A tĂˇbla indexei `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- A tĂˇbla indexei `pictures`
--
ALTER TABLE `pictures`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pictures_owner` (`owner_type`,`owner_id`),
  ADD KEY `idx_pictures_active` (`is_active`);

--
-- A tĂˇbla indexei `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_products_category_id` (`category_id`);

--
-- A tĂˇbla indexei `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- A tĂˇbla indexei `direct_messages`
--
ALTER TABLE `direct_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_dm_sender` (`sender_id`),
  ADD KEY `idx_dm_recipient` (`recipient_id`),
  ADD KEY `idx_dm_item` (`item_id`),
  ADD KEY `idx_dm_created` (`created_at`);

--
-- A tĂˇbla indexei `profile_messages`
--
ALTER TABLE `profile_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_profile_messages_target` (`target_user_id`);

--
-- A tĂˇbla indexei `profile_meta`
--
ALTER TABLE `profile_meta`
  ADD PRIMARY KEY (`user_id`);

--
-- A tĂˇbla indexei `profile_ratings`
--
ALTER TABLE `profile_ratings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_profile_rating` (`target_user_id`,`reviewer_id`),
  ADD KEY `idx_profile_rating_target` (`target_user_id`);

--
-- A tĂˇbla indexei `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_review` (`product_id`,`user_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- A tĂˇbla indexei `used_items`
--
ALTER TABLE `used_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_used_items_user` (`user_id`),
  ADD KEY `idx_used_items_status` (`status`);

--
-- A tĂˇbla indexei `used_offers`
--
ALTER TABLE `used_offers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_used_offers_item` (`item_id`),
  ADD KEY `idx_used_offers_buyer` (`buyer_id`),
  ADD KEY `idx_used_offers_status` (`status`);

--
-- A tĂˇbla indexei `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `uniq_username` (`username`),
  ADD UNIQUE KEY `uniq_email` (`email`);

--
-- A tĂˇbla indexei `user_activity`
--
ALTER TABLE `user_activity`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- A tĂˇbla indexei `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- A kiĂ­rt tĂˇblĂˇk AUTO_INCREMENT Ă©rtĂ©ke
--

--
-- AUTO_INCREMENT a tĂˇblĂˇhoz `bands`
--
ALTER TABLE `bands`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT a tĂˇblĂˇhoz `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT a tĂˇblĂˇhoz `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT a tĂˇblĂˇhoz `hang`
--
ALTER TABLE `hang`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT a tĂˇblĂˇhoz `kategoria`
--
ALTER TABLE `kategoria`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT a tĂˇblĂˇhoz `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT a tĂˇblĂˇhoz `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT a tĂˇblĂˇhoz `pictures`
--
ALTER TABLE `pictures`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=246;

--
-- AUTO_INCREMENT a tĂˇblĂˇhoz `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=467;

--
-- AUTO_INCREMENT a tĂˇblĂˇhoz `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT a tĂˇblĂˇhoz `direct_messages`
--
ALTER TABLE `direct_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT a tĂˇblĂˇhoz `profile_messages`
--
ALTER TABLE `profile_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT a tĂˇblĂˇhoz `profile_ratings`
--
ALTER TABLE `profile_ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT a tĂˇblĂˇhoz `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=130;

--
-- AUTO_INCREMENT a tĂˇblĂˇhoz `used_items`
--
ALTER TABLE `used_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT a tĂˇblĂˇhoz `used_offers`
--
ALTER TABLE `used_offers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT a tĂˇblĂˇhoz `users`
--
ALTER TABLE `users`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identification', AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT a tĂˇblĂˇhoz `user_activity`
--
ALTER TABLE `user_activity`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT a tĂˇblĂˇhoz `user_addresses`
--
ALTER TABLE `user_addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- MegkĂ¶tĂ©sek a kiĂ­rt tĂˇblĂˇkhoz
--

--
-- MegkĂ¶tĂ©sek a tĂˇblĂˇhoz `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- MegkĂ¶tĂ©sek a tĂˇblĂˇhoz `cart_coupons`
--
ALTER TABLE `cart_coupons`
  ADD CONSTRAINT `fk_cart_coupons_coupon` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`);

--
-- MegkĂ¶tĂ©sek a tĂˇblĂˇhoz `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`ID`) ON DELETE CASCADE;

--
-- MegkĂ¶tĂ©sek a tĂˇblĂˇhoz `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- MegkĂ¶tĂ©sek a tĂˇblĂˇhoz `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `kategoria` (`id`);

--
-- MegkĂ¶tĂ©sek a tĂˇblĂˇhoz `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `product_variants_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- MegkĂ¶tĂ©sek a tĂˇblĂˇhoz `direct_messages`
--
ALTER TABLE `direct_messages`
  ADD CONSTRAINT `fk_dm_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dm_recipient` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- MegkĂ¶tĂ©sek a tĂˇblĂˇhoz `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`ID`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- MegkĂ¶tĂ©sek a tĂˇblĂˇhoz `user_activity`
--
ALTER TABLE `user_activity`
  ADD CONSTRAINT `user_activity_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`ID`);

--
-- MegkĂ¶tĂ©sek a tĂˇblĂˇhoz `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD CONSTRAINT `user_addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`ID`);

-- --------------------------------------------------------
-- Import utani hangszer normalizalas (kategoria + hang + kep + leiras + review kapcsolat)
-- --------------------------------------------------------

-- Kategoriak az oldal szerinti hangszer tipusokkal.
UPDATE kategoria SET nev='Gitar', slug='gitar' WHERE id=1;
UPDATE kategoria SET nev='Basszus gitar', slug='basszus-gitar' WHERE id=2;
UPDATE kategoria SET nev='Dobszettek', slug='dobszettek' WHERE id=3;
UPDATE kategoria SET nev='Billentyu', slug='billentyu' WHERE id=4;
UPDATE kategoria SET nev='Mikrofon', slug='mikrofon' WHERE id=5;
UPDATE kategoria SET nev='Tartozekok', slug='tartozekok' WHERE id=6;
UPDATE kategoria SET nev='Hangfalak', slug='hangfalak' WHERE id=7;

-- Termek kategoriak normalizalasa nev alapjan.
UPDATE products
SET category_id = CASE
  WHEN LOWER(name) REGEXP 'bass|basszus|trbx|stingray|precision bass|jazz bass|rockbass|ray4|sr300|pj|bb234|stiletto' THEN 2
  WHEN LOWER(name) REGEXP 'dob|drum|snare|tama|pearl|mapex|ludwig|sonor|gretsch|td-1|nitro mesh|roadshow|imperialstar|breakbeats|aqx' THEN 3
  WHEN LOWER(name) REGEXP 'billentyu|keyboard|piano|zongora|synth|psr|ct-|fp-|es110|es120|korg b2|nord|kurzweil|recital|cdp' THEN 4
  WHEN LOWER(name) REGEXP 'hangfal|speaker|monitor|subwoofer|hs5|hs7|rokit|jbl|eurolive|emberton|soundlink|eris|dm-40|dm-50|charge' THEN 7
  WHEN LOWER(name) REGEXP 'penget|pick|kabel|allvany|adapter|heveder|hur|hurkeszlet|tisztito|pedal|kotta|gigbag|hardcase|interface' THEN 6
  WHEN LOWER(name) REGEXP 'mikrofon|microphone|mic|shure|rode|akg|audio-technica|sennheiser|podmic|yeti|mv7|sm58|sm7b|at2020|at2035|xm8500' THEN 5
  WHEN LOWER(name) REGEXP 'gitar|guitar|stratocaster|telecaster|les paul|jazzmaster|dinky|ibanez rg|revstar|ec-256|viper|prs|pacifica' THEN 1
  ELSE IF(category_id IN (1,2,3,4,5,6,7), category_id, 1)
END;

-- Minden termek kapjon hangot.
UPDATE products
SET hang = CASE
  WHEN category_id=1 THEN CONCAT('hangok/git', ((id - 1) % 5) + 1, '.mp3')
  WHEN category_id=2 THEN CONCAT('hangok/bass', ((id - 1) % 5) + 1, '.mp3')
  WHEN category_id=3 THEN CONCAT('hangok/drum', ((id - 1) % 9) + 1, '.mp3')
  WHEN category_id=4 THEN CONCAT('hangok/synth', LPAD((((id - 1) % 10) + 1), 2, '0'), '.wav')
  WHEN category_id=5 THEN CONCAT('hangok/synth', LPAD((((id - 1) % 10) + 1), 2, '0'), '.wav')
  WHEN category_id=6 THEN CONCAT('hangok/bass', ((id - 1) % 5) + 1, '.mp3')
  WHEN category_id=7 THEN CONCAT('hangok/drum', ((id - 1) % 9) + 1, '.mp3')
  ELSE 'hangok/git1.mp3'
END
WHERE hang IS NULL OR TRIM(hang)='';

-- Minden termek kapjon leirast, ha hianyzik.
UPDATE products
SET description = CASE category_id
  WHEN 1 THEN CONCAT(name, ' elektromos gitar, stabil hangolassal es sokoldalu hangzassal.')
  WHEN 2 THEN CONCAT(name, ' basszus gitar, feszes melyekkel es megbizhato jatekerzettel.')
  WHEN 3 THEN CONCAT(name, ' dobszett, dinamikus attackkal es stabil hardverrel.')
  WHEN 4 THEN CONCAT(name, ' billentyus hangszer, tiszta hangmintakkal es kenyelmes billentessel.')
  WHEN 5 THEN CONCAT(name, ' mikrofon, reszletgazdag es zajszegeny jelatvitellel.')
  WHEN 6 THEN CONCAT(name, ' hangszeres tartozek, praktikus hasznalatra probara es szinpadra.')
  WHEN 7 THEN CONCAT(name, ' hangfal, kiegyensulyozott megszolalassal es stabil teljesitmennyel.')
  ELSE CONCAT(name, ' hangszer a myshop kinalatabol.')
END
WHERE description IS NULL
   OR TRIM(description)=''
   OR description LIKE 'Git%r le%r%s'
   OR description LIKE 'Dob szett #%'
   OR description LIKE '%Ă%'
   OR description LIKE '%Â%';

-- Aktiv termekkep frissitese kategoria szerint (csak hangszeres kepek).
UPDATE pictures pic
JOIN products p ON p.id = pic.owner_id
SET
  pic.filename = CASE p.category_id
    WHEN 1 THEN ELT(((p.id - 1) % 10) + 1, 'Yamaha.jpg','Fender Stratocaster.jpg','Ibanez RG421.jpg','lespaul.jpg','PRS.jpg','gitar.jfif','gitarka.jpg','gitarka2.png','gitarka3.png','meno.jpg')
    WHEN 2 THEN CONCAT('bass', ((p.id - 1) % 10) + 1, '.jpg')
    WHEN 3 THEN CONCAT('dob', ((p.id - 1) % 10) + 1, '.jpg')
    WHEN 4 THEN CONCAT('bill', ((p.id - 1) % 10) + 1, '.jpg')
    WHEN 5 THEN CONCAT('mik', ((p.id - 1) % 10) + 1, '.jpg')
    WHEN 6 THEN CONCAT('tart', ((p.id - 1) % 10) + 1, '.jpg')
    WHEN 7 THEN CONCAT('hang', ((p.id - 1) % 10) + 1, '.jpg')
    ELSE 'Default.avatar.jpg'
  END,
  pic.path = CASE p.category_id
    WHEN 1 THEN CONCAT('/uploads/', ELT(((p.id - 1) % 10) + 1, 'Yamaha.jpg','Fender Stratocaster.jpg','Ibanez RG421.jpg','lespaul.jpg','PRS.jpg','gitar.jfif','gitarka.jpg','gitarka2.png','gitarka3.png','meno.jpg'))
    WHEN 2 THEN CONCAT('/uploads/bass', ((p.id - 1) % 10) + 1, '.jpg')
    WHEN 3 THEN CONCAT('/uploads/dob', ((p.id - 1) % 10) + 1, '.jpg')
    WHEN 4 THEN CONCAT('/uploads/bill', ((p.id - 1) % 10) + 1, '.jpg')
    WHEN 5 THEN CONCAT('/uploads/mik', ((p.id - 1) % 10) + 1, '.jpg')
    WHEN 6 THEN CONCAT('/uploads/tart', ((p.id - 1) % 10) + 1, '.jpg')
    WHEN 7 THEN CONCAT('/uploads/hang', ((p.id - 1) % 10) + 1, '.jpg')
    ELSE '/uploads/Default.avatar.jpg'
  END,
  pic.alt_text = p.name,
  pic.mime_type = CASE
    WHEN p.category_id = 1 AND ((p.id - 1) % 10) + 1 IN (8,9) THEN 'image/png'
    ELSE 'image/jpeg'
  END
WHERE pic.owner_type='product'
  AND pic.is_active=1;

-- Hianyzo aktiv termekkep beszurasa.
INSERT INTO pictures (owner_type, owner_id, filename, path, alt_text, mime_type, size_bytes, is_active, created_at)
SELECT
  'product' AS owner_type,
  p.id AS owner_id,
  CASE p.category_id
    WHEN 1 THEN ELT(((p.id - 1) % 10) + 1, 'Yamaha.jpg','Fender Stratocaster.jpg','Ibanez RG421.jpg','lespaul.jpg','PRS.jpg','gitar.jfif','gitarka.jpg','gitarka2.png','gitarka3.png','meno.jpg')
    WHEN 2 THEN CONCAT('bass', ((p.id - 1) % 10) + 1, '.jpg')
    WHEN 3 THEN CONCAT('dob', ((p.id - 1) % 10) + 1, '.jpg')
    WHEN 4 THEN CONCAT('bill', ((p.id - 1) % 10) + 1, '.jpg')
    WHEN 5 THEN CONCAT('mik', ((p.id - 1) % 10) + 1, '.jpg')
    WHEN 6 THEN CONCAT('tart', ((p.id - 1) % 10) + 1, '.jpg')
    WHEN 7 THEN CONCAT('hang', ((p.id - 1) % 10) + 1, '.jpg')
    ELSE 'Default.avatar.jpg'
  END AS filename,
  CASE p.category_id
    WHEN 1 THEN CONCAT('/uploads/', ELT(((p.id - 1) % 10) + 1, 'Yamaha.jpg','Fender Stratocaster.jpg','Ibanez RG421.jpg','lespaul.jpg','PRS.jpg','gitar.jfif','gitarka.jpg','gitarka2.png','gitarka3.png','meno.jpg'))
    WHEN 2 THEN CONCAT('/uploads/bass', ((p.id - 1) % 10) + 1, '.jpg')
    WHEN 3 THEN CONCAT('/uploads/dob', ((p.id - 1) % 10) + 1, '.jpg')
    WHEN 4 THEN CONCAT('/uploads/bill', ((p.id - 1) % 10) + 1, '.jpg')
    WHEN 5 THEN CONCAT('/uploads/mik', ((p.id - 1) % 10) + 1, '.jpg')
    WHEN 6 THEN CONCAT('/uploads/tart', ((p.id - 1) % 10) + 1, '.jpg')
    WHEN 7 THEN CONCAT('/uploads/hang', ((p.id - 1) % 10) + 1, '.jpg')
    ELSE '/uploads/Default.avatar.jpg'
  END AS path,
  p.name AS alt_text,
  CASE
    WHEN p.category_id = 1 AND ((p.id - 1) % 10) + 1 IN (8,9) THEN 'image/png'
    ELSE 'image/jpeg'
  END AS mime_type,
  NULL AS size_bytes,
  1 AS is_active,
  NOW() AS created_at
FROM products p
LEFT JOIN pictures pic
  ON pic.owner_type='product'
 AND pic.owner_id=p.id
 AND pic.is_active=1
WHERE pic.id IS NULL;

-- Arva review-k torlese.
DELETE r
FROM reviews r
LEFT JOIN products p ON p.id=r.product_id
WHERE p.id IS NULL;

DELETE r
FROM reviews r
LEFT JOIN users u ON u.ID=r.user_id
WHERE u.ID IS NULL;

-- Minden termekhez legyen legalabb 1 velemeny.
INSERT INTO reviews (user_id, product_id, rating, comment, created_at)
SELECT
  u.reviewer_id,
  p.id,
  ((p.id % 3) + 3) AS rating,
  CONCAT('Jo valasztas: ', p.name),
  NOW()
FROM products p
CROSS JOIN (
  SELECT MIN(ID) AS reviewer_id
  FROM users
) u
LEFT JOIN reviews r ON r.product_id=p.id
WHERE r.id IS NULL
  AND u.reviewer_id IS NOT NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

