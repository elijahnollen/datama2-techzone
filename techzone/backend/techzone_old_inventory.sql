CREATE DATABASE  IF NOT EXISTS `techzone_old_inventory` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `techzone_old_inventory`;
-- MySQL dump 10.13  Distrib 8.0.43, for Win64 (x86_64)
--
-- Host: localhost    Database: techzone_inventory
-- ------------------------------------------------------
-- Server version	8.0.43

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customers` (
                             `ï»¿#` int DEFAULT NULL,
                             `Customer Name` text,
                             `Phone Number` text,
                             `Email Address` text,
                             `Address` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customers`
--

LOCK TABLES `customers` WRITE;
/*!40000 ALTER TABLE `customers` DISABLE KEYS */;
INSERT INTO `customers` VALUES (1,'Juan Dela Cruz','0917-111-2222','juan.dc@gmail.com','Quezon City'),(2,'Maria Santos','0918-222-3333','maria.santos@yahoo.com','Makati City'),(3,'Jose Rizal','0919-333-4444','j.rizal@gmail.com','Manila'),(4,'Andres Bonifacio','0920-444-5555','andres.b@outlook.com','Tondo'),(5,'Gabriela Silang','0921-555-6666','g.silang@gmail.com','Ilocos'),(6,'Emilio Aguinaldo','0922-666-7777','emilio.a@yahoo.com','Cavite'),(7,'Apolinario Mabini','0923-777-8888','poly.mabini@gmail.com','Batangas'),(8,'Melchora Aquino','0924-888-9999','tandang.sora@gmail.com','Quezon City'),(9,'Antonio Luna','0925-999-0000','gen.luna@hotmail.com','Manila'),(10,'Gregorio Del Pilar','0926-000-1111','goyo.dp@gmail.com','Bulacan'),(11,'Lapu Lapu','0927-111-2222','lapu.cebu@yahoo.com','Cebu'),(12,'Francisco Balagtas','0928-222-3333','kiko.b@gmail.com','Bulacan'),(13,'Grace Poe','0929-333-4444','grace.p@senate.gov','Manila'),(14,'Manny Pacquiao','0930-444-5555','pacman@gym.com','GenSan'),(15,'Catriona Gray','0931-555-6666','cat.gray@universe.com','Albay'),(16,'Pia Wurtzbach','0932-666-7777','pia.w@universe.com','CDO'),(17,'Lea Salonga','0933-777-8888','lea.s@broadway.com','Manila'),(18,'Arnel Pineda','0934-888-9999','arnel.journey@gmail.com','Manila'),(19,'Regine Velasquez','0935-999-0000','regine.v@gma.com','Bulacan'),(20,'Sarah Geronimo','0936-000-1111','sarah.g@pop.com','Manila'),(21,'Vice Ganda','0937-111-2222','vice.g@showtime.com','Manila'),(22,'Coco Martin','0938-222-3333','coco.m@angprob.com','Quezon City'),(23,'Kathryn Bernardo','0939-333-4444','kath.b@abs.com','Nueva Ecija'),(24,'Daniel Padilla','0940-444-5555','dj.padilla@abs.com','Manila'),(25,'Liza Soberano','0941-555-6666','liza.s@care.com','Manila'),(26,'Enrique Gil','0942-666-7777','quen.gil@abs.com','Cebu'),(27,'Joshua Garcia','0943-777-8888','josh.g@gmail.com','Batangas'),(28,'Julia Barretto','0944-888-9999','julia.b@gmail.com','Marikina'),(29,'Alden Richards','0945-999-0000','alden.r@gma.com','Laguna'),(30,'Maine Mendoza','0946-000-1111','yaya.dub@gmail.com','Bulacan');
/*!40000 ALTER TABLE `customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `return_and_stock_log`
--

DROP TABLE IF EXISTS `return_and_stock_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `return_and_stock_log` (
                                        `ï»¿Return ID` int DEFAULT NULL,
                                        `Date` text,
                                        `Customer` text,
                                        `Item` text,
                                        `Qty` int DEFAULT NULL,
                                        `Reason` text,
                                        `Status` text,
                                        `Current Stock` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `return_and_stock_log`
--

LOCK TABLES `return_and_stock_log` WRITE;
/*!40000 ALTER TABLE `return_and_stock_log` DISABLE KEYS */;
INSERT INTO `return_and_stock_log` VALUES (1,'1/2/2025','Juan Dela Cruz','Ryzen 5 5600',1,'Defective','Refunded',15),(2,'1/3/2025','Maria Santos','8GB DDR4 RAM',1,'Change of Mind','Refunded',40),(3,'1/5/2025','Andres Bonifacio','Ryzen 5 5600',2,'Defective','Replaced',12),(4,'1/6/2025','Emilio Aguinaldo','RTX 4060',1,'Defective','Refunded',5),(5,'1/7/2025','Apolinario Mabini','Mech Keyboard',1,'Change of Mind','Store Credit',20),(6,'1/8/2025','Manny Pacquiao','Gaming Chair',1,'Defective','Replaced',8),(7,'1/10/2025','Lea Salonga','Microphone USB',1,'Change of Mind','Refunded',25),(8,'1/12/2025','Kathryn Bernardo','Logitech G102',1,'Defective','Replaced',50),(9,'1/15/2025','Maine Mendoza','Ring Light',1,'Change of Mind','Refunded',30),(10,'1/18/2025','Jose Rizal','8GB DDR4 RAM',1,'Defective','Refunded',38),(11,'1/20/2025','Antonio Luna','RTX 4060',1,'Change of Mind','Refunded',3),(12,'1/22/2025','Catriona Gray','B550m Motherboard',1,'Defective','Replaced',10),(13,'1/25/2025','Vice Ganda','8GB DDR4 RAM',1,'Change of Mind','Store Credit',35),(14,'1/28/2025','Alden Richards','Mech Keyboard',1,'Defective','Refunded',18),(15,'1/30/2025','Gabriela Silang','Ring Light',1,'Defective','Replaced',28),(16,'2/2/2025','Francisco Balagtas','Microphone USB',1,'Change of Mind','Refunded',22),(17,'2/5/2025','Pia Wurtzbach','Mech Keyboard',1,'Defective','Refunded',15),(18,'2/8/2025','Daniel Padilla','Ryzen 7 5700X',1,'Change of Mind','Store Credit',6),(19,'2/10/2025','Julia Barretto','24\" IPS Monitor\"',1,'Defective','Replaced',14),(20,'2/12/2025','Juan Dela Cruz','Ryzen 5 5600',1,'Change of Mind','Refunded',10);
/*!40000 ALTER TABLE `return_and_stock_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sales_log`
--

DROP TABLE IF EXISTS `sales_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sales_log` (
                             `ï»¿ID` int DEFAULT NULL,
                             `Date` text,
                             `Customer` text,
                             `Item_Sold` text,
                             `Qty` int DEFAULT NULL,
                             `Sold_Price` text,
                             `Wholesale_Cost` text,
                             `Supplier_Name` text,
                             `Supplier_Contact` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sales_log`
--

LOCK TABLES `sales_log` WRITE;
/*!40000 ALTER TABLE `sales_log` DISABLE KEYS */;
INSERT INTO `sales_log` VALUES (1,'1/1/2025','Juan Dela Cruz','Ryzen 5 5600',1,'8,500','6,500','AMD Phil','02-8888-1111'),(2,'1/1/2025','Maria Santos','RTX 4060',1,'18,500','16,000','Asus Ph','02-8888-2222'),(3,'1/1/2025','Maria Santos','8GB DDR4 RAM',2,'1,500','900','Kingston D.','02-8888-3333'),(4,'1/2/2025','Jose Rizal','Logitech G102',1,'995','700','Logi Dist','02-8888-4444'),(5,'1/2/2025','Andres Bonifacio','Ryzen 5 5600',10,'8,200','6,500','AMD Phil','02-8888-1111'),(6,'1/3/2025','Gabriela Silang','24\" IPS Monitor\"',1,'7,500','5,500','Nvision','0917-000-1111'),(7,'1/3/2025','Emilio Aguinaldo','RTX 4060',1,'18,500','16,000','Asus Philippines','02-8888-2222'),(8,'1/4/2025','Apolinario Mabini','Mech Keyboard',1,'2,500','1,800','Rakk Gears','0918-000-2222'),(9,'1/4/2025','Melchora Aquino','1TB NVMe SSD',1,'3,500','2,500','Kingston D.','02-8888-3333'),(10,'1/5/2025','Antonio Luna','Ryzen 7 5700X',1,'12,000','9,500','AMD Phil','02-8888-1111'),(11,'1/5/2025','Gregorio Del Pilar','B550m Motherboard',1,'6,500','5,000','Asus Ph','02-8888-2222'),(12,'1/6/2025','Lapu Lapu','RTX 4070',1,'38,000','34,000','Gigabyte Ph','02-8888-5555'),(13,'1/6/2025','Francisco Balagtas','650W PSU',1,'3,000','2,200','Corsair D.','02-8888-6666'),(14,'1/7/2025','Grace Poe','Webcam 1080p',1,'1,200','800','Logi Dist','02-8888-4444'),(15,'1/7/2025','Manny Pacquiao','Gaming Chair',5,'8,000','5,000','SecretLab','02-8888-7777'),(16,'1/8/2025','Catriona Gray','Ring Light',1,'500','200','Generic','N/A'),(17,'1/8/2025','Pia Wurtzbach','Ryzen 5 5600',1,'8,500','6,500','AMD Phil','02-8888-1111'),(18,'1/9/2025','Lea Salonga','Microphone USB',1,'2,500','1,500','Maono','0919-000-3333'),(19,'1/9/2025','Arnel Pineda','Sound Card',1,'1,500','900','Creative','02-8888-8888'),(20,'1/10/2025','Regine Velasquez','24\" IPS Monitor\"',2,'7,500','5,500','Nvision','0917-000-1111'),(21,'1/10/2025','Vice Ganda','RTX 3060',1,'18,000','14,000','Asus Ph','02-8888-2222'),(22,'1/10/2025','Coco Martin','Ryzen 5 5600',1,'8,500','6,500','AMD Dist','02-8888-1111'),(23,'1/11/2025','Kathryn Bernardo','Logitech G102',1,'995','700','Logi Dist','02-8888-4444'),(24,'1/11/2025','Daniel Padilla','Mech Keyboard',1,'2,500','1,800','Rakk Gears','0918-000-2222'),(25,'1/12/2025','Liza Soberano','1TB NVMe SSD',1,'3,500','2,500','Kingston D.','02-8888-3333'),(26,'1/12/2025','Enrique Gil','RTX 4070',1,'38,000','34,000','Gigabyte Ph','02-8888-5555'),(27,'1/13/2025','Joshua Garcia','Gaming Chair',1,'8,000','5,000','SecretLab','02-8888-7777'),(28,'1/13/2025','Julia Barretto','Webcam 1080p',1,'1,200','800','Logi Dist','02-8888-4444'),(29,'1/14/2025','Alden Richards','Ryzen 7 5700X',1,'12,000','9,500','AMD Phil','02-8888-1111'),(30,'1/14/2025','Maine Mendoza','Ring Light',2,'500','200','Generic','N/A'),(31,'1/15/2025','Juan Dela Cruz','650W PSU',1,'3,000','2,200','Corsair D.','02-8888-6666'),(32,'1/15/2025','Maria Santos','B550m Motherboard',1,'6,500','5,000','Asus Philippines','02-8888-2222'),(33,'1/15/2025','Jose Rizal','8GB DDR4 RAM',4,'1,500','900','Kingston D.','02-8888-3333'),(34,'1/16/2025','Andres Bonifacio','RTX 3060',1,'18,000','14,000','Asus Dist','02-8888-2222'),(35,'1/16/2025','Gabriela Silang','24\" IPS Monitor\"',1,'7,500','5,500','Nvision','0917-000-1111'),(36,'1/17/2025','Emilio Aguinaldo','Sound Card',1,'1,500','900','Creative','02-8888-8888'),(37,'1/17/2025','Apolinario Mabini','Microphone USB',1,'2,500','1,500','Maono','0919-000-3333'),(38,'1/18/2025','Melchora Aquino','Ryzen 5 5600',1,'8,500','6,500','AMD Phil','02-8888-1111'),(39,'1/18/2025','Antonio Luna','RTX 4060',2,'18,500','16,000','Asus Ph','02-8888-2222'),(40,'1/19/2025','Gregorio Del Pilar','Logitech G102',1,'995','700','Logi Dist','02-8888-4444'),(41,'1/19/2025','Lapu Lapu','Mech Keyboard',1,'2,500','1,800','Rakk Gears','0918-000-2222'),(42,'1/20/2025','Francisco Balagtas','Gaming Chair',1,'8,000','5,000','SecretLab','02-8888-7777'),(43,'1/20/2025','Grace Poe','1TB NVMe SSD',1,'3,500','2,500','Kingston D.','02-8888-3333'),(44,'1/21/2025','Manny Pacquiao','RTX 4070',1,'38,000','34,000','Gigabyte Ph','02-8888-5555'),(45,'1/21/2025','Catriona Gray','B550m Motherboard',1,'6,500','5,000','Asus Ph','02-8888-2222'),(46,'1/21/2025','Pia Wurtzbach','Webcam 1080p',1,'1,200','800','Logi Dist','02-8888-4444'),(47,'1/22/2025','Lea Salonga','Ring Light',1,'500','200','Generic','N/A'),(48,'1/22/2025','Arnel Pineda','Ryzen 7 5700X',1,'12,000','9,500','AMD Dist','02-8888-1111'),(49,'1/23/2025','Regine Velasquez','650W PSU',1,'3,000','2,200','Corsair D.','02-8888-6666'),(50,'1/23/2025','Vice Ganda','8GB DDR4 RAM',2,'1,500','900','Kingston D.','02-8888-3333'),(51,'1/24/2025','Coco Martin','RTX 3060',1,'18,000','14,000','Asus Philippines','02-8888-2222'),(52,'1/24/2025','Kathryn Bernardo','24\" IPS Monitor\"',1,'7,500','5,500','Nvision','0917-000-1111'),(53,'1/25/2025','Daniel Padilla','Sound Card',1,'1,500','900','Creative','02-8888-8888'),(54,'1/25/2025','Liza Soberano','Microphone USB',1,'2,500','1,500','Maono','0919-000-3333'),(55,'1/26/2025','Enrique Gil','Ryzen 5 5600',1,'8,500','6,500','AMD Phil','02-8888-1111'),(56,'1/26/2025','Joshua Garcia','RTX 4060',1,'18,500','16,000','Asus Ph','02-8888-2222'),(57,'1/26/2025','Julia Barretto','Logitech G102',1,'995','700','Logi Dist','02-8888-4444'),(58,'1/27/2025','Alden Richards','Mech Keyboard',1,'2,500','1,800','Rakk Gears','0918-000-2222'),(59,'1/27/2025','Maine Mendoza','Gaming Chair',1,'8,000','5,000','SecretLab','02-8888-7777'),(60,'1/28/2025','Juan Dela Cruz','1TB NVMe SSD',2,'3,500','2,500','Kingston D.','02-8888-3333'),(61,'1/28/2025','Maria Santos','RTX 4070',1,'38,000','34,000','Gigabyte Ph','02-8888-5555'),(62,'1/28/2025','Jose Rizal','B550m Motherboard',1,'6,500','5,000','Asus Dist','02-8888-2222'),(63,'1/29/2025','Andres Bonifacio','Webcam 1080p',1,'1,200','800','Logi Dist','02-8888-4444'),(64,'1/29/2025','Gabriela Silang','Ring Light',1,'500','200','Generic','N/A'),(65,'1/30/2025','Emilio Aguinaldo','Ryzen 7 5700X',1,'12,000','9,500','AMD Phil','02-8888-1111'),(66,'1/30/2025','Apolinario Mabini','650W PSU',1,'3,000','2,200','Corsair D.','02-8888-6666'),(67,'1/31/2025','Melchora Aquino','8GB DDR4 RAM',2,'1,500','900','Kingston D.','02-8888-3333'),(68,'1/31/2025','Antonio Luna','RTX 3060',1,'18,000','14,000','Asus Ph','02-8888-2222'),(69,'2/1/2025','Gregorio Del Pilar','24\" IPS Monitor\"',1,'7,500','5,500','Nvision','0917-000-1111'),(70,'2/1/2025','Lapu Lapu','Sound Card',1,'1,500','900','Creative','02-8888-8888'),(71,'2/1/2025','Francisco Balagtas','Microphone USB',1,'2,500','1,500','Maono','0919-000-3333'),(72,'2/2/2025','Grace Poe','Ryzen 5 5600',1,'8,500','6,500','AMD Dist','02-8888-1111'),(73,'2/2/2025','Manny Pacquiao','RTX 4060',5,'18,500','16,000','Asus Philippines','02-8888-2222'),(74,'2/3/2025','Catriona Gray','Logitech G102',1,'995','700','Logi Dist','02-8888-4444'),(75,'2/3/2025','Pia Wurtzbach','Mech Keyboard',1,'2,500','1,800','Rakk Gears','0918-000-2222'),(76,'2/4/2025','Lea Salonga','Gaming Chair',1,'8,000','5,000','SecretLab','02-8888-7777'),(77,'2/4/2025','Arnel Pineda','1TB NVMe SSD',1,'3,500','2,500','Kingston D.','02-8888-3333'),(78,'2/5/2025','Regine Velasquez','RTX 4070',1,'38,000','34,000','Gigabyte Ph','02-8888-5555'),(79,'2/5/2025','Vice Ganda','B550m Motherboard',1,'6,500','5,000','Asus Ph','02-8888-2222'),(80,'2/6/2025','Coco Martin','Webcam 1080p',1,'1,200','800','Logi Dist','02-8888-4444'),(81,'2/6/2025','Kathryn Bernardo','Ring Light',1,'500','200','Generic','N/A'),(82,'2/7/2025','Daniel Padilla','Ryzen 7 5700X',1,'12,000','9,500','AMD Phil','02-8888-1111'),(83,'2/7/2025','Liza Soberano','650W PSU',1,'3,000','2,200','Corsair D.','02-8888-6666'),(84,'2/8/2025','Enrique Gil','8GB DDR4 RAM',2,'1,500','900','Kingston D.','02-8888-3333'),(85,'2/8/2025','Joshua Garcia','RTX 3060',1,'18,000','14,000','Asus Ph','02-8888-2222'),(86,'2/9/2025','Julia Barretto','24\" IPS Monitor\"',1,'7,500','5,500','Nvision','0917-000-1111'),(87,'2/9/2025','Alden Richards','Sound Card',1,'1,500','900','Creative','02-8888-8888'),(88,'2/10/2025','Maine Mendoza','Microphone USB',1,'2,500','1,500','Maono','0919-000-3333'),(89,'2/10/2025','Juan Dela Cruz','Ryzen 5 5600',1,'8,500','6,500','AMD Phil','02-8888-1111'),(90,'2/11/2025','Maria Santos','RTX 4060',1,'18,500','16,000','Asus Ph','02-8888-2222'),(91,'2/11/2025','Jose Rizal','Logitech G102',1,'995','700','Logi Dist','02-8888-4444'),(92,'2/12/2025','Andres Bonifacio','Mech Keyboard',1,'2,500','1,800','Rakk Gears','0918-000-2222'),(93,'2/12/2025','Gabriela Silang','Gaming Chair',1,'8,000','5,000','SecretLab','02-8888-7777'),(94,'2/13/2025','Emilio Aguinaldo','1TB NVMe SSD',1,'3,500','2,500','Kingston D.','02-8888-3333'),(95,'2/13/2025','Apolinario Mabini','RTX 4070',1,'38,000','34,000','Gigabyte Ph','02-8888-5555'),(96,'2/14/2025','Melchora Aquino','B550m Motherboard',1,'6,500','5,000','Asus Dist','02-8888-2222'),(97,'2/14/2025','Antonio Luna','Webcam 1080p',1,'1,200','800','Logi Dist','02-8888-4444'),(98,'2/14/2025','Gregorio Del Pilar','Ring Light',1,'500','200','Generic','N/A'),(99,'2/15/2025','Lapu Lapu','Ryzen 7 5700X',1,'12,000','9,500','AMD Phil','02-8888-1111'),(100,'2/15/2025','Francisco Balagtas','650W PSU',1,'3,000','2,200','Corsair D.','02-8888-6666');
/*!40000 ALTER TABLE `sales_log` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-01-02 23:31:36
