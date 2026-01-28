-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: database:3306
-- Generation Time: Jan 28, 2026 at 06:40 PM
-- Server version: 10.6.23-MariaDB-ubu2204
-- PHP Version: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `blog`
--

-- --------------------------------------------------------

--
-- Table structure for table `article_images`
--

CREATE TABLE `article_images` (
  `image_id` int(11) NOT NULL,
  `FK_article_id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `article_images`
--

INSERT INTO `article_images` (`image_id`, `FK_article_id`, `file_path`, `alt_text`, `created_at`) VALUES
(4, 5, 'picture-uploads/articles/img_695fb082252104.34705564_different-colored-carrots-and-other-vegetables-harvested-from-the-garden.jpg', '', '2026-01-08 13:26:26'),
(5, 6, 'picture-uploads/articles/img_695fbaaea9a098.01834064_pexels-canvastudio-3194518.jpg', '', '2026-01-08 14:09:50'),
(6, 4, 'picture-uploads/articles/img_695fbb004e92f1.11361560_pexels-scott-lord-564881271-35552951.jpg', '', '2026-01-08 14:11:12'),
(7, 5, 'picture-uploads/articles/img_695fe202529104.28586673_pexels-nc-farm-bureau-mark-2255924.jpg', '', '2026-01-08 16:57:38'),
(8, 4, 'picture-uploads/articles/img_695fe8ff6f6ea3.06281877_pexels-daniel-cid-634838605-17809421.jpg', '', '2026-01-08 17:27:27'),
(10, 2, 'picture-uploads/articles/img_696017d5945866.50649559_pexels-chaitaastic-1796736.jpg', '', '2026-01-08 20:47:17'),
(11, 2, 'picture-uploads/articles/img_696017f26a7f25.34039729_pexels-hebaysal-773471.jpg', '', '2026-01-08 20:47:46'),
(13, 7, 'picture-uploads/articles/img_69601c8c209d76.65346213_pexels-emma-filer-718293-1572728.jpg', '', '2026-01-08 21:07:24'),
(14, 8, 'picture-uploads/articles/img_69611c9bc4cce9.69392746_pexels-anntarazevich-7251847.jpg', '', '2026-01-09 15:19:55'),
(15, 9, 'picture-uploads/articles/img_69611d83bb2e53.90617412_pexels-franco-la-pioggia-2154192962-33816053.jpg', '', '2026-01-09 15:23:47'),
(16, 10, 'picture-uploads/articles/img_696156fcc7b196.40224780_pexels-matthias-oben-2060028-3687918.jpg', '', '2026-01-09 19:29:00'),
(17, 1, 'picture-uploads/articles/img_6961573139a3f5.60253042_pexels-junior-teixeira-1064069-2047905.jpg', '', '2026-01-09 19:29:53');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `article_images`
--
ALTER TABLE `article_images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `FK_article_id` (`FK_article_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `article_images`
--
ALTER TABLE `article_images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `article_images`
--
ALTER TABLE `article_images`
  ADD CONSTRAINT `article_images_ibfk_1` FOREIGN KEY (`FK_article_id`) REFERENCES `articles` (`article_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
