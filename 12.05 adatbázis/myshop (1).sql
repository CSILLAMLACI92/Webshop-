-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- Gép: localhost:8889
-- Létrehozás ideje: 2025. Dec 05. 09:05
-- Kiszolgáló verziója: 5.7.24
-- PHP verzió: 8.3.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Adatbázis: `myshop`
--

DELIMITER $$
--
-- Eljárások
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `ADD_PRODUCT_VARIANT` (IN `p_product_id` INT, IN `p_variant_name` VARCHAR(255), IN `p_stock` INT)   BEGIN
    INSERT INTO product_variants(product_id, variant_name, stock)
    VALUES(p_product_id, p_variant_name, p_stock);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ADD_REVIEW` (IN `p_user_id` INT, IN `p_product_id` INT, IN `p_rating` INT, IN `p_comment` TEXT)   BEGIN
    INSERT INTO reviews(user_id, product_id, rating, comment)
    VALUES(p_user_id, p_product_id, p_rating, p_comment);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ADD_TO_CART` (IN `p_user_id` INT, IN `p_product_id` INT, IN `p_quantity` INT)   BEGIN
    -- 1. Megpróbáljuk frissíteni a mennyiséget, ha a tétel már létezik
    UPDATE cart
    SET quantity = quantity + p_quantity
    WHERE user_id = p_user_id AND product_id = p_product_id;

    -- 2. Ellenőrizzük, történt-e frissítés
    -- ROW_COUNT() megmondja, hány sor frissült. Ha 0, akkor a tétel új.
    IF ROW_COUNT() = 0 THEN
        -- 3. Ha a frissítés sikertelen (a tétel nem létezett), beszúrunk egy új sort
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
-- Tábla szerkezet ehhez a táblához `cart`
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
-- Tábla szerkezet ehhez a táblához `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- A tábla adatainak kiíratása `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `created_at`) VALUES
(1, 'Yamaha Pacifica 112V', 'Gitár leírás', '135000.00', '2025-12-01 09:10:15'),
(2, 'Fender Stratocaster', 'Gitár leírás', '420000.00', '2025-12-01 09:10:15'),
(3, 'Ibanez RG421', 'Gitár leírás', '180000.00', '2025-12-01 09:10:15'),
(4, 'Epiphone Les Paul', 'Gitár leírás', '190000.00', '2025-12-01 09:10:15'),
(5, 'PRS SE Custom 24', 'Gitár leírás', '320000.00', '2025-12-01 09:10:15');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `product_variants`
--

CREATE TABLE `product_variants` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_name` varchar(100) NOT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `stock` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `reviews`
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
-- A tábla adatainak kiíratása `reviews`
--

INSERT INTO `reviews` (`id`, `user_id`, `product_id`, `rating`, `comment`, `created_at`) VALUES
(17, 26, 1, 4, 'asd', '2025-12-01 09:10:22'),
(18, 26, 2, 2, 'áh', '2025-12-01 09:12:17'),
(19, 26, 3, 1, 'nagyon szar', '2025-12-01 09:14:00');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `users`
--

CREATE TABLE `users` (
  `ID` int(11) NOT NULL COMMENT 'Identification',
  `username` varchar(30) NOT NULL,
  `email` varchar(70) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `profile_pic` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Bejelentkezési adatok';

--
-- A tábla adatainak kiíratása `users`
--

INSERT INTO `users` (`ID`, `username`, `email`, `password_hash`, `role`, `created_at`, `profile_pic`) VALUES
(12, 'admin', 'admin@example.com', '$2b$10$f50mJJ1cjj9KinBO8PpZvOARrm2FHeK/BeLGpRIJ0z8fTwpJxMZ6G', 'admin', '2025-11-18 09:32:45', 'uploads/default.png'),
(23, '1', '1@1', '$2y$10$xoNpec4zKeqEZhd0nmLTcOmpY5lqD8SwV9g4sHAKr6TA8OTXT8tQ6', 'user', '2025-11-18 09:35:56', 'uploads/pfp_23_1763458678.png'),
(24, 'erik', 'erik@erik', '$2y$10$uk.NpusviZXCN9nNdU6x4.s6yYdZ4VpwJPAiPriDbhSniGliqtyeC', 'user', '2025-11-21 08:12:12', 'pfp_24_1763719991.png'),
(25, '6', '6@6', '$2y$10$GOTeooeg2mSHR6VCMCvze.Sd/Hm.rqDMjhEG.Qv9FQDq71F4l05Ea', 'user', '2025-11-21 09:48:57', 'pfp_25_1763718860.png'),
(26, 'Er', 'er@er', '$2y$10$N2HDEaFsQ5mkgpdIGfjgd.l/Wpa9hZyTEXJfWngwZmLYOywiMepLW', 'user', '2025-12-01 08:39:11', 'pfp_26_1764578382.png');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `user_activity`
--

CREATE TABLE `user_activity` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `user_addresses`
--

CREATE TABLE `user_addresses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `address_line1` varchar(255) NOT NULL,
  `city` varchar(100) DEFAULT NULL,
  `zip` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexek a kiírt táblákhoz
--

--
-- A tábla indexei `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_product_unique` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- A tábla indexei `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- A tábla indexei `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- A tábla indexei `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_review` (`product_id`,`user_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- A tábla indexei `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- A tábla indexei `user_activity`
--
ALTER TABLE `user_activity`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- A tábla indexei `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- A kiírt táblák AUTO_INCREMENT értéke
--

--
-- AUTO_INCREMENT a táblához `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT a táblához `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT a táblához `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT a táblához `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT a táblához `users`
--
ALTER TABLE `users`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identification', AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT a táblához `user_activity`
--
ALTER TABLE `user_activity`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT a táblához `user_addresses`
--
ALTER TABLE `user_addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Megkötések a kiírt táblákhoz
--

--
-- Megkötések a táblához `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Megkötések a táblához `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `product_variants_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Megkötések a táblához `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`ID`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Megkötések a táblához `user_activity`
--
ALTER TABLE `user_activity`
  ADD CONSTRAINT `user_activity_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`ID`);

--
-- Megkötések a táblához `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD CONSTRAINT `user_addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
