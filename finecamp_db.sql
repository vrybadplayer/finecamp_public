-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 24, 2024 at 03:28 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `finecamp_db`
--
CREATE DATABASE IF NOT EXISTS `finecamp_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `finecamp_db`;

-- --------------------------------------------------------

--
-- Table structure for table `bank_card`
--

CREATE TABLE `bank_card` (
  `bank_card_id` int(11) NOT NULL,
  `number` bigint(16) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` varchar(15) NOT NULL,
  `expiry_date` varchar(5) NOT NULL,
  `cvv` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bank_card`
--

INSERT INTO `bank_card` (`bank_card_id`, `number`, `name`, `type`, `expiry_date`, `cvv`) VALUES
(1, 5538285918593218, 'Johnson', 'Mastercard', '05/26', '40bd001563085fc35165329ea1ff5c5ecbdbbeef'),
(2, 5137733039024907, 'Jackson', 'Mastercard', '06/26', '51eac6b471a284d3341d8c0c63d0f1a286262a18'),
(3, 5440925875087517, 'Arick', 'Visa', '07/25', 'fc1200c7a7aa52109d762a9f005b149abef01479'),
(4, 5445730939549527, 'LimSam', 'Mastercard', '11/26', '40bd001563085fc35165329ea1ff5c5ecbdbbeef'),
(5, 5514590673638563, 'Erek', 'Visa', '04/25', '51eac6b471a284d3341d8c0c63d0f1a286262a18'),
(6, 5525545958087602, 'Chucky', 'Visa', '08/26', 'fc1200c7a7aa52109d762a9f005b149abef01479'),
(7, 5573622967639284, 'Aaron', 'Visa', '12/26', '40bd001563085fc35165329ea1ff5c5ecbdbbeef'),
(8, 5305176043433456, 'Kreken', 'Mastercard', '07/27', '51eac6b471a284d3341d8c0c63d0f1a286262a18'),
(9, 5291245127368361, 'basty', 'Visa', '03/25', 'fc1200c7a7aa52109d762a9f005b149abef01479'),
(10, 5182346771141633, 'chroma', 'Mastercard', '01/26', '40bd001563085fc35165329ea1ff5c5ecbdbbeef');

-- --------------------------------------------------------

--
-- Table structure for table `cart_item`
--

CREATE TABLE `cart_item` (
  `cart_item_id` int(11) NOT NULL,
  `quantity` int(10) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_variation_option_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart_item`
--

INSERT INTO `cart_item` (`cart_item_id`, `quantity`, `user_id`, `product_id`, `product_variation_option_id`) VALUES
(5, 1, 6, 3, 8),
(7, 1, 8, 7, 19),
(8, 1, 9, 10, 26),
(9, 1, 9, 8, 22),
(24, 1, 2, 1, 1),
(25, 1, 2, 3, 8),
(38, 1, 5, 8, 21),
(39, 1, 5, 10, 26);

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `category_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(200) NOT NULL,
  `status` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`category_id`, `name`, `description`, `status`) VALUES
(1, 'No Category', 'Products without a specific category.', 'active'),
(2, 'Backpacks', 'Durable backpacks with multiple compartments', 'active'),
(3, 'Sleeping Bags', 'Comfortable sleeping bags for all seasons', 'active'),
(4, 'Tents', 'Camping tents for different weather conditions', 'active'),
(5, 'Lighting', 'The Lighting category includes lanterns, headlamps, flashlights, and portable solar lights, providing essential illumination for safe and convenient nighttime activities during camping or outdoor adve', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `created_datetime` datetime NOT NULL,
  `packaging_datetime` datetime DEFAULT NULL,
  `shipping_datetime` datetime DEFAULT NULL,
  `delivery_datetime` datetime DEFAULT NULL,
  `received_datetime` datetime DEFAULT NULL,
  `discount_amount` decimal(10,2) NOT NULL,
  `shipping_charge` decimal(10,2) NOT NULL,
  `tax_charge` decimal(10,2) NOT NULL,
  `subtotal_amount` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` varchar(10) NOT NULL,
  `shipping_detail_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `created_datetime`, `packaging_datetime`, `shipping_datetime`, `delivery_datetime`, `received_datetime`, `discount_amount`, `shipping_charge`, `tax_charge`, `subtotal_amount`, `total_amount`, `status`, `shipping_detail_id`, `user_id`) VALUES
(1, '2024-07-01 01:20:15', '2024-07-02 01:20:15', '2024-07-03 01:20:15', '2024-07-07 01:20:15', '2024-07-07 02:20:15', 20.00, 24.00, 26.00, 260.00, 290.00, 'completed', 12, 2),
(2, '2024-07-08 01:26:12', '2024-07-09 01:26:12', '2024-07-10 01:26:12', '2024-07-14 01:26:12', '2024-07-14 02:26:12', 20.00, 22.50, 30.00, 300.00, 332.50, 'completed', 13, 2),
(3, '2024-07-15 01:27:23', '2024-07-16 01:27:23', '2024-07-17 01:27:23', '2024-07-21 01:27:23', NULL, 20.00, 22.50, 24.00, 240.00, 266.50, 'delivered', 14, 2),
(4, '2024-07-22 01:28:45', '2024-07-23 01:28:45', '2024-07-24 01:28:45', '2024-07-28 01:28:45', NULL, 0.00, 13.50, 15.00, 150.00, 178.50, 'delivered', 15, 2),
(5, '2024-07-29 01:31:29', '2024-07-30 01:31:29', '2024-07-31 01:31:29', NULL, NULL, 0.00, 9.00, 10.00, 100.00, 119.00, 'shipping', 16, 2),
(6, '2024-08-05 01:37:30', '2024-08-06 01:37:30', '2024-08-07 01:37:30', NULL, NULL, 20.00, 18.00, 22.00, 220.00, 240.00, 'shipping', 17, 2),
(7, '2024-08-12 01:38:56', '2024-08-13 01:38:56', NULL, NULL, NULL, 0.00, 9.00, 11.00, 110.00, 130.00, 'packaged', 18, 2),
(8, '2024-08-19 01:41:16', '2024-08-20 01:41:16', NULL, NULL, NULL, 0.00, 18.00, 15.00, 150.00, 183.00, 'packaged', 19, 2),
(9, '2024-08-26 01:43:02', NULL, NULL, NULL, NULL, 0.00, 9.00, 18.00, 180.00, 207.00, 'created', 20, 2),
(10, '2024-09-02 01:47:42', NULL, NULL, NULL, NULL, 20.00, 28.00, 32.00, 320.00, 360.00, 'created', 21, 2),
(11, '2024-09-03 02:21:12', '2024-09-04 02:21:12', '2024-09-05 02:21:12', '2024-09-09 02:21:12', '2024-09-09 03:21:12', 40.00, 44.00, 56.00, 560.00, 620.00, 'completed', 23, 5),
(12, '2024-09-10 02:24:25', '2024-09-11 02:24:25', '2024-09-12 02:24:25', '2024-09-16 02:24:25', '2024-09-16 03:24:25', 20.00, 24.00, 33.00, 330.00, 367.00, 'completed', 24, 5),
(13, '2024-09-17 02:26:46', '2024-09-18 02:26:46', '2024-09-19 02:26:46', '2024-09-23 02:26:46', NULL, 0.00, 13.50, 13.00, 130.00, 156.50, 'delivered', 25, 5),
(14, '2024-09-24 02:28:50', '2024-09-25 02:28:50', '2024-09-26 02:28:50', '2024-09-30 02:28:50', NULL, 0.00, 5.00, 6.00, 60.00, 71.00, 'delivered', 26, 5),
(15, '2024-10-01 02:30:47', '2024-10-02 02:30:47', '2024-10-03 02:30:47', NULL, NULL, 0.00, 9.00, 12.00, 120.00, 141.00, 'shipping', 27, 5),
(16, '2024-10-08 02:32:41', '2024-10-09 02:32:41', '2024-10-10 02:32:41', NULL, NULL, 20.00, 18.00, 20.00, 200.00, 218.00, 'shipping', 28, 5),
(17, '2024-10-15 02:34:28', '2024-10-16 02:34:28', NULL, NULL, NULL, 0.00, 9.00, 11.00, 110.00, 130.00, 'packaged', 29, 5),
(18, '2024-10-22 02:35:52', '2024-10-23 02:35:52', NULL, NULL, NULL, 0.00, 13.50, 13.00, 130.00, 156.50, 'packaged', 30, 5),
(19, '2024-10-29 02:37:27', NULL, NULL, NULL, NULL, 20.00, 13.50, 36.00, 360.00, 389.50, 'created', 31, 5),
(20, '2024-11-06 02:38:39', NULL, NULL, NULL, NULL, 20.00, 32.00, 36.00, 360.00, 408.00, 'created', 32, 5);

-- --------------------------------------------------------

--
-- Table structure for table `order_item`
--

CREATE TABLE `order_item` (
  `order_item_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `variation_name` varchar(30) NOT NULL,
  `product_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_item`
--

INSERT INTO `order_item` (`order_item_id`, `name`, `variation_name`, `product_id`, `order_id`) VALUES
(1, 'Mountain Tent', 'Size', 1, 1),
(2, 'Solo Backpack', 'Color', 3, 2),
(3, 'Mountain Tent', 'Size', 1, 2),
(4, 'Expedition Backpack', 'Color', 4, 3),
(5, 'Summer Sleeping Bag', 'Size', 5, 4),
(6, 'Winter Sleeping Bag', 'Size', 6, 5),
(7, 'Winter Sleeping Bag', 'Size', 6, 6),
(8, 'Two-Person Tent', 'Size', 7, 6),
(9, 'Hiking Backpack', 'Color', 8, 7),
(10, 'Double Sleeping Bag', 'Size', 9, 8),
(11, 'Ultralight Tent', 'Size', 10, 9),
(12, 'Mountain Tent', 'Size', 1, 10),
(13, 'Family Tent', 'Size', 2, 10),
(14, 'Mountain Tent', 'Size', 1, 11),
(15, 'Mountain Tent', 'Size', 1, 12),
(16, 'Solo Backpack', 'Color', 3, 12),
(17, 'Expedition Backpack', 'Color', 4, 13),
(18, 'Summer Sleeping Bag', 'Size', 5, 14),
(19, 'Winter Sleeping Bag', 'Size', 6, 15),
(20, 'Winter Sleeping Bag', 'Size', 6, 16),
(21, 'Two-Person Tent', 'Size', 7, 16),
(22, 'Hiking Backpack', 'Color', 8, 17),
(23, 'Double Sleeping Bag', 'Size', 9, 18),
(24, 'Ultralight Tent', 'Size', 10, 19),
(25, 'Family Tent', 'Size', 2, 20),
(26, 'Hiking Backpack', 'Color', 8, 20);

-- --------------------------------------------------------

--
-- Table structure for table `order_item_variation_option`
--

CREATE TABLE `order_item_variation_option` (
  `order_item_variation_option_id` int(11) NOT NULL,
  `name` varchar(30) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `quantity` int(10) NOT NULL,
  `image` varchar(50) NOT NULL,
  `weight` decimal(10,2) NOT NULL,
  `order_item_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_item_variation_option`
--

INSERT INTO `order_item_variation_option` (`order_item_variation_option_id`, `name`, `unit_price`, `quantity`, `image`, `weight`, `order_item_id`) VALUES
(1, 'Small', 120.00, 1, 'mountain_tent_small.jpg', 2.30, 1),
(2, 'Medium', 140.00, 1, 'mountain_tent_medium.jpg', 2.80, 1),
(3, 'Black', 90.00, 2, 'solo_backpack_black.jpg', 1.20, 2),
(4, 'Small', 120.00, 1, 'mountain_tent_small.jpg', 2.30, 3),
(5, 'Green', 120.00, 2, 'expedition_backpack_green.jpg', 2.50, 4),
(6, 'Small', 50.00, 3, 'summer_sleeping_bag_small.jpg', 0.70, 5),
(7, 'Small', 100.00, 1, 'winter_sleeping_bag_small.jpg', 1.50, 6),
(8, 'Small', 100.00, 1, 'winter_sleeping_bag_small.jpg', 1.50, 7),
(9, 'Spacious', 120.00, 1, 'two_person_tent_spacious.jpg', 2.50, 8),
(10, 'Black', 110.00, 1, 'hiking_backpack_black.jpg', 1.80, 9),
(11, 'Extra Warm', 150.00, 1, 'double_sleeping_bag_extra_warm.jpg', 3.20, 10),
(12, 'Standard', 180.00, 1, 'ultralight_tent_standard.jpg', 1.50, 11),
(13, 'Small', 120.00, 1, 'mountain_tent_small.jpg', 2.30, 12),
(14, '4-Person', 200.00, 1, 'family_tent_4_person.jpg', 4.50, 13),
(15, 'Small', 120.00, 2, 'mountain_tent_small.jpg', 2.30, 14),
(16, 'Large', 160.00, 2, 'mountain_tent_large.jpg', 3.20, 14),
(17, 'Small', 120.00, 2, 'mountain_tent_small.jpg', 2.30, 15),
(18, 'Red', 90.00, 1, 'solo_backpack_red.jpg', 1.20, 16),
(19, 'Camo', 130.00, 1, 'expedition_backpack_camo.jpg', 2.80, 17),
(20, 'Medium', 60.00, 1, 'summer_sleeping_bag_medium.jpg', 0.80, 18),
(21, 'Medium', 120.00, 1, 'winter_sleeping_bag_medium.jpg', 2.00, 19),
(22, 'Small', 100.00, 1, 'winter_sleeping_bag_small.jpg', 1.50, 20),
(23, 'Compact', 100.00, 1, 'two_person_tent_compact.jpg', 2.00, 21),
(24, 'Black', 110.00, 1, 'hiking_backpack_black.jpg', 1.80, 22),
(25, 'Standard', 130.00, 1, 'double_sleeping_bag_standard.jpg', 2.80, 23),
(26, 'Standard', 180.00, 2, 'ultralight_tent_standard.jpg', 1.50, 24),
(27, '6-Person', 250.00, 1, 'family_tent_6_person.jpg', 6.00, 25),
(28, 'Blue', 110.00, 1, 'hiking_backpack_blue.jpg', 1.80, 26);

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `payment_id` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `created_datetime` datetime NOT NULL,
  `card_type` varchar(15) NOT NULL,
  `order_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`payment_id`, `total`, `created_datetime`, `card_type`, `order_id`) VALUES
(1, 290.00, '2024-07-01 01:20:15', 'Mastercard', 1),
(2, 332.50, '2024-07-08 01:26:12', 'Mastercard', 2),
(3, 266.50, '2024-07-15 01:27:23', 'Mastercard', 3),
(4, 178.50, '2024-07-22 01:28:45', 'Mastercard', 4),
(5, 119.00, '2024-07-29 01:31:29', 'Mastercard', 5),
(6, 240.00, '2024-08-05 01:37:30', 'Mastercard', 6),
(7, 130.00, '2024-08-12 01:38:56', 'Mastercard', 7),
(8, 183.00, '2024-08-19 01:41:16', 'Mastercard', 8),
(9, 207.00, '2024-08-26 01:43:02', 'Mastercard', 9),
(10, 360.00, '2024-09-02 01:47:42', 'Mastercard', 10),
(11, 620.00, '2024-09-03 02:21:12', 'Visa', 11),
(12, 367.00, '2024-09-10 02:24:25', 'Visa', 12),
(13, 156.50, '2024-09-17 02:26:46', 'Visa', 13),
(14, 71.00, '2024-09-24 02:28:50', 'Visa', 14),
(15, 141.00, '2024-10-01 02:30:47', 'Visa', 15),
(16, 218.00, '2024-10-08 02:32:41', 'Visa', 16),
(17, 130.00, '2024-10-15 02:34:28', 'Visa', 17),
(18, 156.50, '2024-10-22 02:35:52', 'Visa', 18),
(19, 389.50, '2024-10-29 02:37:27', 'Visa', 19),
(20, 408.00, '2024-11-06 02:38:39', 'Visa', 20);

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `product_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(200) NOT NULL,
  `min_stock_level` int(11) NOT NULL,
  `variation_name` varchar(30) NOT NULL,
  `product_image` varchar(50) NOT NULL,
  `status` varchar(10) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`product_id`, `name`, `description`, `min_stock_level`, `variation_name`, `product_image`, `status`, `category_id`) VALUES
(1, 'Mountain Tent', 'A lightweight tent suitable for mountain conditions', 50, 'Size', 'product_1.jpg', 'active', 4),
(2, 'Family Tent', 'A spacious tent suitable for families', 50, 'Size', 'product_2.jpg', 'active', 4),
(3, 'Solo Backpack', 'A compact backpack for solo hikers', 50, 'Color', 'product_3.jpg', 'active', 2),
(4, 'Expedition Backpack', 'A large backpack for long expeditions', 50, 'Color', 'product_4.jpg', 'active', 2),
(5, 'Summer Sleeping Bag', 'A lightweight sleeping bag for summer', 50, 'Size', 'product_5.jpg', 'active', 3),
(6, 'Winter Sleeping Bag', 'A heavy-duty sleeping bag for winter', 50, 'Size', 'product_6.jpg', 'active', 3),
(7, 'Two-Person Tent', 'Tent suitable for two people', 50, 'Size', 'product_7.jpg', 'active', 4),
(8, 'Hiking Backpack', 'Backpack with hydration system', 50, 'Color', 'product_8.jpg', 'active', 2),
(9, 'Double Sleeping Bag', 'Sleeping bag for two people', 50, 'Size', 'product_9.jpg', 'active', 3),
(10, 'Ultralight Tent', 'Super lightweight tent for fast packing', 50, 'Size', 'product_10.jpg', 'active', 4),
(11, 'Nature Hiking Inflatable Tent', 'Easy assembly / dismantling. Very quick to pitch: all-in-one inflatable structure and pre-assembled bedroom', 10, 'Size', '66f1887d0e1c8.jpg', 'inactive', 4),
(12, 'Fully automatic hydraulic tent', 'A Fully Automatic Hydraulic Tent is a quick-setup tent that uses a hydraulic system for effortless, instant assembly and disassembly. Ideal for camping and outdoor adventures.', 30, 'Color', '66f186728b01f.jpg', 'active', 4),
(13, 'Rainproof Sunscreen Tent', 'The Outdoor Portable Folding Rainproof Sunscreen Tent offers easy setup, foldable convenience, and protection from rain and sun, making it perfect for camping and outdoor activities.', 50, 'Color', '66f18712c64da.jpg', 'active', 4),
(14, 'Easy Assemble 3 Person Camping Tent', 'Offers quick setup, spacious interior for three people, and reliable protection from the elements, making it perfect for hassle-free outdoor adventures.', 45, 'Color', '66f18781ec17a.jpg', 'active', 4),
(15, 'Hiking Trekking Backpack', 'Designed for comfort and organization, perfect for long hikes and treks with ample storage and ergonomic support.', 50, 'Color', '66f185c802c2b.jpg', 'active', 2),
(16, 'Lightweight Hiking Backpack', 'Provides durable, water-resistant protection with a lightweight design, ideal for outdoor adventures and keeping your gear dry and secure.', 60, 'Color', '66f1852da6a70.jpg', 'active', 2),
(17, 'Orka Sleeping Bag', 'Durable, warm, and lightweight sleeping bag designed for outdoor adventures, providing comfort and insulation for a restful night\'s sleep in various weather conditions.', 100, 'Color', '66f18ca059155.jpg', 'active', 3),
(18, 'Vintage Style Camping Lantern', 'It provides reliable and adjustable brightness, making it perfect for illuminating campsites, hiking trails, or emergency situations.', 100, 'Color', '66f18ef7bc99d.jpg', 'active', 5),
(19, 'Rechargeable Camping Lantern', 'It features a rechargeable battery with a USB port for easy charging, offering long-lasting illumination.', 100, 'Color', '66f18fecb6819.jpg', 'active', 5),
(20, 'Retro LED Camping Lantern', 'Combines vintage charm with modern LED technology, providing a warm, ambient glow for outdoor settings.', 80, 'Model', '66f190d10a14a.jpg', 'active', 5),
(21, 'Folding Leather Camping Chair', 'Ideal for outdoor adventures, this chair provides excellent support and portability, making it perfect for camping, picnics, or backyard gatherings', 70, 'Color', '66f1916002bd6.jpg', 'active', 1);

-- --------------------------------------------------------

--
-- Table structure for table `product_variation_option`
--

CREATE TABLE `product_variation_option` (
  `product_variation_option_id` int(11) NOT NULL,
  `name` varchar(30) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `stock_quantity` int(10) NOT NULL,
  `image` varchar(50) NOT NULL,
  `weight` decimal(10,2) NOT NULL,
  `product_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_variation_option`
--

INSERT INTO `product_variation_option` (`product_variation_option_id`, `name`, `unit_price`, `stock_quantity`, `image`, `weight`, `product_id`) VALUES
(1, 'Small', 120.00, 50, 'mountain_tent_small.jpg', 2.30, 1),
(2, 'Medium', 140.00, 40, 'mountain_tent_medium.jpg', 2.80, 1),
(3, 'Large', 160.00, 0, 'mountain_tent_large.jpg', 3.20, 1),
(4, '4-Person', 200.00, 25, 'family_tent_4_person.jpg', 4.50, 2),
(5, '6-Person', 250.00, 20, 'family_tent_6_person.jpg', 6.00, 2),
(6, '8-Person', 300.00, 15, 'family_tent_8_person.jpg', 8.00, 2),
(7, 'Blue', 90.00, 60, 'solo_backpack_blue.jpg', 1.20, 3),
(8, 'Black', 90.00, 55, 'solo_backpack_black.jpg', 1.20, 3),
(9, 'Red', 90.00, 50, 'solo_backpack_red.jpg', 1.20, 3),
(10, 'Green', 120.00, 40, 'expedition_backpack_green.jpg', 2.50, 4),
(11, 'Black', 120.00, 35, 'expedition_backpack_black.jpg', 2.50, 4),
(12, 'Camo', 130.00, 30, 'expedition_backpack_camo.jpg', 2.80, 4),
(13, 'Small', 50.00, 70, 'summer_sleeping_bag_small.jpg', 0.70, 5),
(14, 'Medium', 60.00, 60, 'summer_sleeping_bag_medium.jpg', 0.80, 5),
(15, 'Large', 70.00, 50, 'summer_sleeping_bag_large.jpg', 1.00, 5),
(16, 'Small', 100.00, 45, 'winter_sleeping_bag_small.jpg', 1.50, 6),
(17, 'Medium', 120.00, 35, 'winter_sleeping_bag_medium.jpg', 2.00, 6),
(18, 'Large', 140.00, 25, 'winter_sleeping_bag_large.jpg', 2.50, 6),
(19, 'Compact', 100.00, 40, 'two_person_tent_compact.jpg', 2.00, 7),
(20, 'Spacious', 120.00, 35, 'two_person_tent_spacious.jpg', 2.50, 7),
(21, 'Blue', 110.00, 50, 'hiking_backpack_blue.jpg', 1.80, 8),
(22, 'Green', 110.00, 45, 'hiking_backpack_green.jpg', 1.80, 8),
(23, 'Black', 110.00, 40, 'hiking_backpack_black.jpg', 1.80, 8),
(24, 'Standard', 130.00, 30, 'double_sleeping_bag_standard.jpg', 2.80, 9),
(25, 'Extra Warm', 150.00, 25, 'double_sleeping_bag_extra_warm.jpg', 3.20, 9),
(26, 'Standard', 180.00, 20, 'ultralight_tent_standard.jpg', 1.50, 10),
(27, 'Deluxe', 220.00, 15, 'ultralight_tent_deluxe.jpg', 1.70, 10),
(28, '4 Person Tent', 399.00, 20, '66f1887d21da7.jpg', 10.50, 11),
(29, 'Green', 150.00, 50, '66f186729e59f.jpg', 10.00, 12),
(30, 'Red', 150.00, 50, '66f18672b1daa.jpg', 10.00, 12),
(31, 'Blue', 150.00, 50, '66f18672c5477.jpg', 10.00, 12),
(32, 'Yellow', 150.00, 50, '66f18672d8514.jpg', 10.00, 12),
(33, 'Blue', 250.00, 100, '66f18712d9ed4.jpg', 14.50, 13),
(34, 'Pink', 250.00, 100, '66f18712ed908.jpg', 14.50, 13),
(35, 'Mocha', 250.00, 100, '66f187130cdf9.jpg', 14.50, 13),
(36, 'Yellow', 199.00, 80, '66f187820bee1.jpg', 15.90, 14),
(37, 'Grey', 199.00, 100, '66f187821e788.jpg', 15.90, 14),
(38, 'Green', 199.00, 100, '66f1878231d86.jpg', 15.90, 14),
(39, 'Black', 139.00, 99, '66f185c82db24.jpg', 1.50, 15),
(40, 'Blue', 139.00, 99, '66f185c8563f5.jpg', 1.50, 15),
(41, 'Red', 139.00, 99, '66f185c87ecc9.jpg', 1.50, 15),
(42, 'Red', 179.00, 30, '66f1852dd9172.jpg', 2.00, 16),
(43, 'Blue', 179.00, 80, '66f1852e16a16.jpg', 2.00, 16),
(44, 'Green', 179.00, 70, '66f1852e47505.jpg', 2.00, 16),
(45, 'Red', 49.90, 150, '66f18ca06db26.jpg', 1.90, 17),
(46, 'Blue', 49.90, 87, '66f18ca081453.jpg', 1.90, 17),
(47, 'Green', 49.90, 102, '66f18ca094ab2.jpg', 1.90, 17),
(48, 'Blue', 24.90, 200, '66f18ef7cfd3e.jpg', 0.90, 18),
(49, 'Green', 24.90, 99, '66f18ef7e1e4a.jpg', 0.90, 18),
(50, 'Green', 29.90, 129, '66f18fecc98af.jpg', 1.20, 19),
(51, 'Orange', 29.90, 110, '66f18fecdb7ad.jpg', 1.20, 19),
(52, 'Standard', 32.90, 100, '66f190d11c9b5.jpg', 1.30, 20),
(53, 'Brown', 49.90, 99, '66f19160157ff.jpg', 2.50, 21),
(54, 'Black', 49.90, 69, '66f1916027580.jpg', 2.50, 21),
(55, 'Green', 49.90, 20, '66f191603947e.jpg', 2.49, 21);

-- --------------------------------------------------------

--
-- Table structure for table `shipping_detail`
--

CREATE TABLE `shipping_detail` (
  `shipping_detail_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `recipient_name` varchar(100) NOT NULL,
  `recipient_phone_no` varchar(20) NOT NULL,
  `address_line` varchar(100) NOT NULL,
  `postcode` varchar(10) NOT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shipping_detail`
--

INSERT INTO `shipping_detail` (`shipping_detail_id`, `user_id`, `recipient_name`, `recipient_phone_no`, `address_line`, `postcode`, `city`, `state`) VALUES
(1, 1, 'Katie', '016-7411951', 'No 4,Jalan Tun Razak 1/2,Taman Tun Razak', '50400', 'Kuala Lumpur', 'Federal Territory of Kuala Lumpur'),
(2, 2, 'Ronni', '019-0023164', 'No 2,Jalan Ampang 3B,Taman Ampang', '50450', 'Kuala Lumpur', 'Federal Territory of Kuala Lumpur'),
(3, 3, 'Emmy Tan', '015-5462569', 'No 9,Jalan Merdeka 5/8,Taman Merdeka', '50000', 'Labuan', 'Federal Territory of Labuan'),
(4, 4, 'Katina', '013-6053391', 'No 3,Jalan Bukit Bintang 5/8,Taman Bukit Bintang', '50200', 'Kuala Lumpur', 'Federal Territory of Kuala Lumpur'),
(5, 5, 'Christyna', '018-0024403', 'No 5,Jalan Jaya 3C,Taman Putra Jaya', '50350', 'Putrajaya', 'Federal Territory of Putrajaya'),
(6, 6, 'Isaac', '016-5318322', 'No 1,Jalan Bangsar 8,Pandan Bangsar', '59000', 'Kuala Lumpur', 'Federal Territory of Kuala Lumpur'),
(7, 7, 'Levy', '015-3551879', 'No 7,Jalan Petaling 6,Taman Petaling', '50100', 'Petaling Jaya', 'Selangor'),
(8, 8, 'Kimmy', '011-05915997', 'No 4,Jalan Dato\' Khaw Kim Bee 1/2,Taman Dato\' Khaw Kim Bee', '93000', 'Kuching', 'Sarawak'),
(9, 9, 'Marius', '016-0154018', 'No 5,Jalan Tuaran 5D,Taman Tuaran', '88000', 'Kota Kinabalu', 'Sabah'),
(10, 10, 'Ethel', '010-5106428', 'No 6,Jalan Bunga Raya 3A,Taman Bunga Raya', '56100', 'Kuala Lumpur', 'Federal Territory of Kuala Lumpur'),
(11, 2, 'Ali', '012-3456789', 'No.10,Jalan Bunga 2/3,Taman Bunga', '43300', 'Seri Kembangan', 'Selangor'),
(12, NULL, 'Ronni', '019-0023164', 'No 2,Jalan Ampang 3B,Taman Ampang', '50450', 'Kuala Lumpur', 'Federal Territory of Kuala Lumpur'),
(13, NULL, 'Ali', '012-3456789', 'No.10,Jalan Bunga 2/3,Taman Bunga', '43300', 'Seri Kembangan', 'Selangor'),
(14, NULL, 'Ronni', '019-0023164', 'No 2,Jalan Ampang 3B,Taman Ampang', '50450', 'Kuala Lumpur', 'Federal Territory of Kuala Lumpur'),
(15, NULL, 'Ali', '012-3456789', 'No.10,Jalan Bunga 2/3,Taman Bunga', '43300', 'Seri Kembangan', 'Selangor'),
(16, NULL, 'Ronni', '019-0023164', 'No 2,Jalan Ampang 3B,Taman Ampang', '50450', 'Kuala Lumpur', 'Federal Territory of Kuala Lumpur'),
(17, NULL, 'Ali', '012-3456789', 'No.10,Jalan Bunga 2/3,Taman Bunga', '43300', 'Seri Kembangan', 'Selangor'),
(18, NULL, 'Ronni', '019-0023164', 'No 2,Jalan Ampang 3B,Taman Ampang', '50450', 'Kuala Lumpur', 'Federal Territory of Kuala Lumpur'),
(19, NULL, 'Ali', '012-3456789', 'No.10,Jalan Bunga 2/3,Taman Bunga', '43300', 'Seri Kembangan', 'Selangor'),
(20, NULL, 'Ronni', '019-0023164', 'No 2,Jalan Ampang 3B,Taman Ampang', '50450', 'Kuala Lumpur', 'Federal Territory of Kuala Lumpur'),
(21, NULL, 'Ali', '012-3456789', 'No.10,Jalan Bunga 2/3,Taman Bunga', '43300', 'Seri Kembangan', 'Selangor'),
(22, 5, 'Abu', '012-3333333', 'No.5,Jalan Jaya 2/7,Taman Jaya', '43300', 'Seri Kembangan', 'Selangor'),
(23, NULL, 'Christyna', '018-0024403', 'No 5,Jalan Jaya 3C,Taman Putra Jaya', '50350', 'Putrajaya', 'Federal Territory of Putrajaya'),
(24, NULL, 'Abu', '012-3333333', 'No.5,Jalan Jaya 2/7,Taman Jaya', '43300', 'Seri Kembangan', 'Selangor'),
(25, NULL, 'Christyna', '018-0024403', 'No 5,Jalan Jaya 3C,Taman Putra Jaya', '50350', 'Putrajaya', 'Federal Territory of Putrajaya'),
(26, NULL, 'Abu', '012-3333333', 'No.5,Jalan Jaya 2/7,Taman Jaya', '43300', 'Seri Kembangan', 'Selangor'),
(27, NULL, 'Christyna', '018-0024403', 'No 5,Jalan Jaya 3C,Taman Putra Jaya', '50350', 'Putrajaya', 'Federal Territory of Putrajaya'),
(28, NULL, 'Abu', '012-3333333', 'No.5,Jalan Jaya 2/7,Taman Jaya', '43300', 'Seri Kembangan', 'Selangor'),
(29, NULL, 'Christyna', '018-0024403', 'No 5,Jalan Jaya 3C,Taman Putra Jaya', '50350', 'Putrajaya', 'Federal Territory of Putrajaya'),
(30, NULL, 'Abu', '012-3333333', 'No.5,Jalan Jaya 2/7,Taman Jaya', '43300', 'Seri Kembangan', 'Selangor'),
(31, NULL, 'Christyna', '018-0024403', 'No 5,Jalan Jaya 3C,Taman Putra Jaya', '50350', 'Putrajaya', 'Federal Territory of Putrajaya'),
(32, NULL, 'Abu', '012-3333333', 'No.5,Jalan Jaya 2/7,Taman Jaya', '43300', 'Seri Kembangan', 'Selangor');

-- --------------------------------------------------------

--
-- Table structure for table `token`
--

CREATE TABLE `token` (
  `token_id` varchar(100) NOT NULL,
  `expire` datetime NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone_no` varchar(20) NOT NULL,
  `photo` varchar(50) NOT NULL,
  `role` varchar(10) NOT NULL,
  `status` varchar(100) NOT NULL,
  `try` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `username`, `password`, `email`, `phone_no`, `photo`, `role`, `status`, `try`) VALUES
(1, 'Johnson', '097a2ee08e88b5865cdb07d4a039b88f5d3a84a2', 'achance0@gmail.com', '012-1624489', '66f11fdbe9aca.jpg', 'Admin', 'active', 0),
(2, 'Jackson', '1df49cf5267789a014e7d7ef24df57ebf2456a45', 'rsander1fine@gmail.com', '012-7732117', '66f11d0212a7d.jpg', 'Member', 'active', 0),
(3, 'Arick Kong', '2245f87c42fbd416c571c42d7411cc632729c0f8', 'mwong2@gmail.com', '011-70400471', '66e7b9239bdab.jpg', 'Admin', 'active', 0),
(4, 'Lim Sam', 'a79be9f6ec1ac285c15d32572e10a1c582b720fd', 'mepilet3@gmail.com', '016-9672250', '66f11fee09e37.jpg', 'Admin', 'active', 0),
(5, 'Erek', 'bbc8430dfd6d83d2a8059273d178964da6ebf64d', 'shurford4@gmail.com', '013-6487871', '66e7bba08351e.jpg', 'Member', 'active', 0),
(6, 'Chucky', '95ca644b99206ffc646f93256a0e9cd837403732', 'cfrondt5@gmail.com', '017-7020439', '66f11cdd37ac5.jpg', 'Member', 'active', 0),
(7, 'Aaron', '1bc468c9a149abfacfbb74b1d88ff742680a546f', 'gmerlin6@gmail.com', '015-6063563', '66f12051db2af.jpg', 'Root', 'active', 0),
(8, 'Keren', 'aff43705a9313d349830f4079e1b149500116cb1', 'mkynder7@gmail.com', '012-7502918', '66f11d14de228.jpg', 'Member', 'active', 0),
(9, 'Basty En', '9f04bee9d54d2b496a3b32837c5036f252a007e7', 'odehoogh8@gmail.com', '013-2521914', '66f11e4612c41.jpg', 'Member', 'inactive', 0),
(10, 'Chong Ming', '0f6071b09ece93489cb84833d6735c84b63d5b8c', 'sgullen9@gmail.com', '014-8074257', '66f11ffd1cea6.jpg', 'Admin', 'active', 0),
(11, 'Jackson Lim', 'e695a9034ea5659899015e64862e0346a2f3e455', 'jackson10@gmail.com', '011-1568974', '66f11f68732c6.jpg', 'Member', 'inactive', 0),
(12, 'Jack Lee', 'b7dc801ca706d71ddb947b9e2fbcbca546978146', 'jacklee11@gmail.com', '011-3568974', '66f11f46c4c8f.jpg', 'Member', 'inactive', 0),
(13, 'Koh Jia Lim', 'd290a656bddc6b85702bc3ef4519355498bac8a5', 'kohjialim12@gmail.com', '011-4568974', '66e7ab549f737.jpg', 'Member', 'terminated', 0),
(14, 'Ivan Kor', '1c1589521fe95097b72320fb11a407a51759e393', 'ivankor13@gmail.com', '011-2568974', '66e7aaeeb174f.jpg', 'Member', 'terminated', 0),
(15, 'Johnson Kong', '54af4b4f78bc7cd03bf165c0d92ae287c37b841d', 'johnsonkong14@gmail.com', '011-2568975', '66e830837054f.jpg', 'Member', 'active', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bank_card`
--
ALTER TABLE `bank_card`
  ADD PRIMARY KEY (`bank_card_id`);

--
-- Indexes for table `cart_item`
--
ALTER TABLE `cart_item`
  ADD PRIMARY KEY (`cart_item_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `cart_item_ibfk_3` (`product_variation_option_id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `shipping_detail_id` (`shipping_detail_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_item`
--
ALTER TABLE `order_item`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `order_item_variation_option`
--
ALTER TABLE `order_item_variation_option`
  ADD PRIMARY KEY (`order_item_variation_option_id`),
  ADD KEY `order_item_id` (`order_item_id`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `product_variation_option`
--
ALTER TABLE `product_variation_option`
  ADD PRIMARY KEY (`product_variation_option_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `shipping_detail`
--
ALTER TABLE `shipping_detail`
  ADD PRIMARY KEY (`shipping_detail_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `token`
--
ALTER TABLE `token`
  ADD PRIMARY KEY (`token_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bank_card`
--
ALTER TABLE `bank_card`
  MODIFY `bank_card_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `cart_item`
--
ALTER TABLE `cart_item`
  MODIFY `cart_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `order_item`
--
ALTER TABLE `order_item`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `order_item_variation_option`
--
ALTER TABLE `order_item_variation_option`
  MODIFY `order_item_variation_option_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `product_variation_option`
--
ALTER TABLE `product_variation_option`
  MODIFY `product_variation_option_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `shipping_detail`
--
ALTER TABLE `shipping_detail`
  MODIFY `shipping_detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart_item`
--
ALTER TABLE `cart_item`
  ADD CONSTRAINT `cart_item_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `cart_item_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`),
  ADD CONSTRAINT `cart_item_ibfk_3` FOREIGN KEY (`product_variation_option_id`) REFERENCES `product_variation_option` (`product_variation_option_id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`shipping_detail_id`) REFERENCES `shipping_detail` (`shipping_detail_id`);

--
-- Constraints for table `order_item`
--
ALTER TABLE `order_item`
  ADD CONSTRAINT `order_item_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`),
  ADD CONSTRAINT `order_item_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`);

--
-- Constraints for table `order_item_variation_option`
--
ALTER TABLE `order_item_variation_option`
  ADD CONSTRAINT `order_item_variation_option_ibfk_1` FOREIGN KEY (`order_item_id`) REFERENCES `order_item` (`order_item_id`);

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`);

--
-- Constraints for table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `product_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`);

--
-- Constraints for table `product_variation_option`
--
ALTER TABLE `product_variation_option`
  ADD CONSTRAINT `product_variation_option_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`);

--
-- Constraints for table `shipping_detail`
--
ALTER TABLE `shipping_detail`
  ADD CONSTRAINT `shipping_detail_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `token`
--
ALTER TABLE `token`
  ADD CONSTRAINT `token_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
