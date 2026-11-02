-- SIAGA (Sistem Informasi Aset & Gangguan): struktur tabel + data referensi (izin, 16 peran, departemen, kategori, SLA, Super Admin).
-- Impor ke database kosong (phpMyAdmin › Import). Super Admin: admin@siaga.local / Siaga@2026 (wajib diganti saat login).
-- Profil awal: "Hotel Contoh" kode HTL — ganti di Pengaturan › Profil hotel SEBELUM mendaftarkan aset pertama.
-- Tanpa data contoh. Setelah impor jalankan: bash deploy/deploy-cpanel.sh update

/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.14-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: siaga_dump
-- ------------------------------------------------------
-- Server version	10.11.14-MariaDB-0ubuntu0.24.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `approval_limits`
--

DROP TABLE IF EXISTS `approval_limits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `approval_limits` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint(20) unsigned NOT NULL,
  `object_type` varchar(30) NOT NULL DEFAULT 'work_order',
  `user_id` bigint(20) unsigned NOT NULL,
  `max_amount` decimal(18,2) DEFAULT NULL COMMENT 'null = tanpa batas nominal',
  `delegate_user_id` bigint(20) unsigned DEFAULT NULL,
  `valid_from` date NOT NULL,
  `valid_to` date DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `approval_limits_property_id_foreign` (`property_id`),
  KEY `approval_limits_user_id_foreign` (`user_id`),
  KEY `approval_limits_delegate_user_id_foreign` (`delegate_user_id`),
  CONSTRAINT `approval_limits_delegate_user_id_foreign` FOREIGN KEY (`delegate_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `approval_limits_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `approval_limits_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `approval_limits`
--

LOCK TABLES `approval_limits` WRITE;
/*!40000 ALTER TABLE `approval_limits` DISABLE KEYS */;
/*!40000 ALTER TABLE `approval_limits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `asset_categories`
--

DROP TABLE IF EXISTS `asset_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `asset_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint(20) unsigned NOT NULL,
  `code` varchar(30) NOT NULL,
  `name` varchar(255) NOT NULL,
  `group` varchar(30) DEFAULT NULL,
  `default_criticality` varchar(10) NOT NULL DEFAULT 'medium',
  `tracking_mode` varchar(12) NOT NULL DEFAULT 'individual',
  `default_pm_interval_days` smallint(5) unsigned DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `asset_categories_property_id_code_unique` (`property_id`,`code`),
  CONSTRAINT `asset_categories_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asset_categories`
--

LOCK TABLES `asset_categories` WRITE;
/*!40000 ALTER TABLE `asset_categories` DISABLE KEYS */;
INSERT INTO `asset_categories` VALUES
(1,1,'AC-SPLIT','AC split kamar','kamar','medium','individual',90,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(2,1,'TV','Televisi','kamar','low','individual',NULL,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(3,1,'MINIBAR','Minibar / kulkas kamar','kamar','low','individual',180,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(4,1,'TEL','Telepon kamar','kamar','low','individual',NULL,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(5,1,'BED','Tempat tidur','kamar','low','individual',365,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(6,1,'LAMP','Lampu meja / baca','kamar','low','individual',NULL,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(7,1,'SAFE','Safe deposit box kamar','kamar','medium','individual',365,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(8,1,'DOORLOCK','Door lock elektronik','kamar','high','individual',180,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(9,1,'FURN','Furniture kamar','kamar','low','individual',NULL,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(10,1,'SANIT','Sanitary & perlengkapan mandi','kamar','medium','individual',180,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(11,1,'LINEN','Linen & handuk','kamar','low','quantity',NULL,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(12,1,'PROJ','Projector','mice','medium','individual',180,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(13,1,'SCREEN','Screen','mice','low','individual',NULL,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(14,1,'SOUND','Sound system','mice','medium','individual',180,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(15,1,'MIC','Microphone','mice','low','individual',NULL,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(16,1,'CHAIR-B','Kursi banquet','mice','low','quantity',NULL,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(17,1,'TABLE-B','Meja banquet','mice','low','quantity',NULL,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(18,1,'PART','Partisi ruang','mice','medium','individual',365,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(19,1,'PANEL-MICE','Panel listrik venue','mice','high','individual',180,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(20,1,'CHILL','Chiller kitchen','kitchen','high','individual',90,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(21,1,'FREEZ','Freezer','kitchen','high','individual',90,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(22,1,'STOVE','Kompor / burner','kitchen','high','individual',90,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(23,1,'OVEN','Oven','kitchen','medium','individual',180,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(24,1,'MIXER','Mixer pastry','kitchen','medium','individual',180,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(25,1,'EXHAUST','Exhaust hood','kitchen','high','individual',90,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(26,1,'GREASE','Grease trap','kitchen','medium','individual',30,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(27,1,'GAS','Instalasi gas','kitchen','critical','individual',30,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(28,1,'BOILER','Boiler','mep','critical','individual',30,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(29,1,'PUMP','Pompa','mep','high','individual',90,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(30,1,'TANK','Tangki air','mep','medium','individual',180,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(31,1,'GENSET','Genset','mep','critical','individual',30,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(32,1,'PANEL','Panel listrik','mep','critical','individual',90,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(33,1,'LIFT','Lift','mep','critical','individual',30,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(34,1,'HVAC','HVAC sentral / AHU','mep','high','individual',90,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(35,1,'POOL-EQ','Peralatan kolam renang','publik','medium','individual',30,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(36,1,'GYM-EQ','Peralatan gym','publik','low','individual',90,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(37,1,'APAR','APAR','keselamatan','critical','individual',30,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(38,1,'ALARM','Panel fire alarm','keselamatan','critical','individual',30,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(39,1,'DETECT','Detektor asap / panas','keselamatan','critical','individual',180,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(40,1,'HYDRANT','Hydrant','keselamatan','critical','individual',90,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(41,1,'EMLIGHT','Emergency light','keselamatan','high','individual',90,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(42,1,'SERVER','Server','it','critical','individual',90,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(43,1,'AP','Access point','it','medium','individual',NULL,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(44,1,'CCTV','CCTV','it','high','individual',90,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(45,1,'SWITCH','Network switch','it','high','individual',NULL,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(46,1,'PABX','PABX','it','high','individual',180,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(47,1,'PC','PC / laptop','it','low','individual',NULL,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(48,1,'PRINTER','Printer','it','low','individual',NULL,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(49,1,'ACCESS','Access control','it','high','individual',180,1,'2026-11-02 03:49:43','2026-11-02 03:49:43');
/*!40000 ALTER TABLE `asset_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `asset_components`
--

DROP TABLE IF EXISTS `asset_components`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `asset_components` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `parent_asset_id` bigint(20) unsigned NOT NULL,
  `component_asset_id` bigint(20) unsigned NOT NULL,
  `installed_at` timestamp NOT NULL,
  `removed_at` timestamp NULL DEFAULT NULL,
  `work_order_id` bigint(20) unsigned DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `replaced_component_id` bigint(20) unsigned DEFAULT NULL,
  `active_component_key` bigint(20) unsigned DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `asset_components_active_component_key_unique` (`active_component_key`),
  KEY `asset_components_parent_asset_id_foreign` (`parent_asset_id`),
  KEY `asset_components_component_asset_id_foreign` (`component_asset_id`),
  KEY `asset_components_replaced_component_id_foreign` (`replaced_component_id`),
  KEY `asset_components_created_by_foreign` (`created_by`),
  CONSTRAINT `asset_components_component_asset_id_foreign` FOREIGN KEY (`component_asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `asset_components_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `asset_components_parent_asset_id_foreign` FOREIGN KEY (`parent_asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `asset_components_replaced_component_id_foreign` FOREIGN KEY (`replaced_component_id`) REFERENCES `assets` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asset_components`
--

LOCK TABLES `asset_components` WRITE;
/*!40000 ALTER TABLE `asset_components` DISABLE KEYS */;
/*!40000 ALTER TABLE `asset_components` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `asset_financials`
--

DROP TABLE IF EXISTS `asset_financials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `asset_financials` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` bigint(20) unsigned NOT NULL,
  `acquisition_date` date DEFAULT NULL,
  `acquisition_cost` decimal(18,2) DEFAULT NULL,
  `replacement_estimate` decimal(18,2) DEFAULT NULL,
  `cost_center` varchar(40) DEFAULT NULL,
  `accounting_ref` varchar(255) DEFAULT NULL,
  `verification_status` varchar(20) NOT NULL DEFAULT 'unverified',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `asset_financials_asset_id_unique` (`asset_id`),
  CONSTRAINT `asset_financials_asset_id_foreign` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asset_financials`
--

LOCK TABLES `asset_financials` WRITE;
/*!40000 ALTER TABLE `asset_financials` DISABLE KEYS */;
/*!40000 ALTER TABLE `asset_financials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `asset_labels`
--

DROP TABLE IF EXISTS `asset_labels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `asset_labels` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint(20) unsigned NOT NULL,
  `asset_id` bigint(20) unsigned DEFAULT NULL,
  `quantity_group_id` bigint(20) unsigned DEFAULT NULL,
  `stock_code` varchar(30) DEFAULT NULL COMMENT 'nomor label stok kosong, mis. HTL-QR-000001',
  `token_hash` char(64) NOT NULL,
  `token_ciphertext` text NOT NULL COMMENT 'terenkripsi APP_KEY, untuk cetak ulang',
  `status` varchar(12) NOT NULL DEFAULT 'active',
  `activated_at` timestamp NULL DEFAULT NULL,
  `revoked_at` timestamp NULL DEFAULT NULL,
  `revoke_reason` varchar(255) DEFAULT NULL,
  `revoked_by` bigint(20) unsigned DEFAULT NULL,
  `bound_by` bigint(20) unsigned DEFAULT NULL,
  `bound_at` timestamp NULL DEFAULT NULL,
  `active_target_key` varchar(40) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `asset_labels_token_hash_unique` (`token_hash`),
  UNIQUE KEY `asset_labels_active_target_key_unique` (`active_target_key`),
  UNIQUE KEY `asset_labels_stock_code_unique` (`stock_code`),
  KEY `asset_labels_property_id_foreign` (`property_id`),
  KEY `asset_labels_asset_id_foreign` (`asset_id`),
  KEY `asset_labels_quantity_group_id_foreign` (`quantity_group_id`),
  KEY `asset_labels_revoked_by_foreign` (`revoked_by`),
  KEY `asset_labels_bound_by_foreign` (`bound_by`),
  CONSTRAINT `asset_labels_asset_id_foreign` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`),
  CONSTRAINT `asset_labels_bound_by_foreign` FOREIGN KEY (`bound_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `asset_labels_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`),
  CONSTRAINT `asset_labels_quantity_group_id_foreign` FOREIGN KEY (`quantity_group_id`) REFERENCES `asset_quantity_groups` (`id`),
  CONSTRAINT `asset_labels_revoked_by_foreign` FOREIGN KEY (`revoked_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asset_labels`
--

LOCK TABLES `asset_labels` WRITE;
/*!40000 ALTER TABLE `asset_labels` DISABLE KEYS */;
/*!40000 ALTER TABLE `asset_labels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `asset_quantity_groups`
--

DROP TABLE IF EXISTS `asset_quantity_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `asset_quantity_groups` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint(20) unsigned NOT NULL,
  `group_code` varchar(30) NOT NULL,
  `location_id` bigint(20) unsigned NOT NULL,
  `category_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `unit` varchar(20) NOT NULL DEFAULT 'pcs',
  `on_hand` decimal(14,2) DEFAULT NULL COMMENT 'null = belum dihitung',
  `expected_qty` decimal(14,2) DEFAULT NULL,
  `status` varchar(12) NOT NULL DEFAULT 'draft',
  `generation_batch_id` bigint(20) unsigned DEFAULT NULL,
  `installation_status` varchar(20) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `asset_quantity_groups_property_id_group_code_unique` (`property_id`,`group_code`),
  KEY `asset_quantity_groups_location_id_foreign` (`location_id`),
  KEY `asset_quantity_groups_category_id_foreign` (`category_id`),
  KEY `asset_quantity_groups_generation_batch_id_index` (`generation_batch_id`),
  CONSTRAINT `asset_quantity_groups_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `asset_categories` (`id`),
  CONSTRAINT `asset_quantity_groups_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`),
  CONSTRAINT `asset_quantity_groups_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asset_quantity_groups`
--

LOCK TABLES `asset_quantity_groups` WRITE;
/*!40000 ALTER TABLE `asset_quantity_groups` DISABLE KEYS */;
/*!40000 ALTER TABLE `asset_quantity_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `asset_status_events`
--

DROP TABLE IF EXISTS `asset_status_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `asset_status_events` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` bigint(20) unsigned NOT NULL,
  `dimension` varchar(30) NOT NULL,
  `old_value` varchar(255) DEFAULT NULL,
  `new_value` varchar(255) DEFAULT NULL,
  `old_label` varchar(255) DEFAULT NULL,
  `new_label` varchar(255) DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `source_type` varchar(40) DEFAULT NULL,
  `source_id` bigint(20) unsigned DEFAULT NULL,
  `actor_id` bigint(20) unsigned DEFAULT NULL,
  `occurred_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  KEY `asset_status_events_actor_id_foreign` (`actor_id`),
  KEY `asset_status_events_asset_id_dimension_index` (`asset_id`,`dimension`),
  CONSTRAINT `asset_status_events_actor_id_foreign` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `asset_status_events_asset_id_foreign` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asset_status_events`
--

LOCK TABLES `asset_status_events` WRITE;
/*!40000 ALTER TABLE `asset_status_events` DISABLE KEYS */;
/*!40000 ALTER TABLE `asset_status_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `asset_transfer_items`
--

DROP TABLE IF EXISTS `asset_transfer_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `asset_transfer_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `transfer_id` bigint(20) unsigned NOT NULL,
  `asset_id` bigint(20) unsigned NOT NULL,
  `from_location_id` bigint(20) unsigned DEFAULT NULL,
  `condition_out` char(1) DEFAULT NULL,
  `condition_in` char(1) DEFAULT NULL,
  `received_scan` tinyint(1) NOT NULL DEFAULT 0,
  `note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `asset_transfer_items_transfer_id_asset_id_unique` (`transfer_id`,`asset_id`),
  KEY `asset_transfer_items_asset_id_foreign` (`asset_id`),
  KEY `asset_transfer_items_from_location_id_foreign` (`from_location_id`),
  CONSTRAINT `asset_transfer_items_asset_id_foreign` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`),
  CONSTRAINT `asset_transfer_items_from_location_id_foreign` FOREIGN KEY (`from_location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `asset_transfer_items_transfer_id_foreign` FOREIGN KEY (`transfer_id`) REFERENCES `asset_transfers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asset_transfer_items`
--

LOCK TABLES `asset_transfer_items` WRITE;
/*!40000 ALTER TABLE `asset_transfer_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `asset_transfer_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `asset_transfers`
--

DROP TABLE IF EXISTS `asset_transfers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `asset_transfers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint(20) unsigned NOT NULL,
  `number` varchar(30) NOT NULL,
  `from_location_id` bigint(20) unsigned DEFAULT NULL,
  `to_location_id` bigint(20) unsigned NOT NULL,
  `to_department_id` bigint(20) unsigned DEFAULT NULL,
  `reason` varchar(255) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `requested_by` bigint(20) unsigned NOT NULL,
  `approved_by` bigint(20) unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `dispatched_by` bigint(20) unsigned DEFAULT NULL,
  `dispatched_at` timestamp NULL DEFAULT NULL,
  `received_by` bigint(20) unsigned DEFAULT NULL,
  `received_at` timestamp NULL DEFAULT NULL,
  `status_reason` varchar(255) DEFAULT NULL,
  `version` int(10) unsigned NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `asset_transfers_number_unique` (`number`),
  KEY `asset_transfers_property_id_foreign` (`property_id`),
  KEY `asset_transfers_from_location_id_foreign` (`from_location_id`),
  KEY `asset_transfers_to_location_id_foreign` (`to_location_id`),
  KEY `asset_transfers_to_department_id_foreign` (`to_department_id`),
  KEY `asset_transfers_requested_by_foreign` (`requested_by`),
  KEY `asset_transfers_approved_by_foreign` (`approved_by`),
  KEY `asset_transfers_dispatched_by_foreign` (`dispatched_by`),
  KEY `asset_transfers_received_by_foreign` (`received_by`),
  CONSTRAINT `asset_transfers_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `asset_transfers_dispatched_by_foreign` FOREIGN KEY (`dispatched_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `asset_transfers_from_location_id_foreign` FOREIGN KEY (`from_location_id`) REFERENCES `locations` (`id`),
  CONSTRAINT `asset_transfers_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`),
  CONSTRAINT `asset_transfers_received_by_foreign` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `asset_transfers_requested_by_foreign` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`),
  CONSTRAINT `asset_transfers_to_department_id_foreign` FOREIGN KEY (`to_department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `asset_transfers_to_location_id_foreign` FOREIGN KEY (`to_location_id`) REFERENCES `locations` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asset_transfers`
--

LOCK TABLES `asset_transfers` WRITE;
/*!40000 ALTER TABLE `asset_transfers` DISABLE KEYS */;
/*!40000 ALTER TABLE `asset_transfers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `assets`
--

DROP TABLE IF EXISTS `assets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `assets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `public_id` char(26) NOT NULL,
  `property_id` bigint(20) unsigned NOT NULL,
  `asset_code` varchar(30) NOT NULL COMMENT 'permanen, dibuat server, mis. HTL-AST-000001 (prefiks = kode properti)',
  `name` varchar(255) NOT NULL,
  `category_id` bigint(20) unsigned NOT NULL,
  `brand` varchar(255) DEFAULT NULL,
  `model` varchar(255) DEFAULT NULL,
  `serial_number` varchar(255) DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `location_id` bigint(20) unsigned DEFAULT NULL,
  `department_id` bigint(20) unsigned DEFAULT NULL,
  `custodian_id` bigint(20) unsigned DEFAULT NULL,
  `custodian_unit` varchar(255) DEFAULT NULL COMMENT 'unit penanggung jawab bila bukan perorangan',
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `condition_grade` char(1) DEFAULT NULL,
  `criticality` varchar(10) DEFAULT NULL,
  `operational_status` varchar(20) NOT NULL DEFAULT 'serviceable',
  `lifecycle_status` varchar(12) NOT NULL DEFAULT 'draft',
  `in_transit` tinyint(1) NOT NULL DEFAULT 0,
  `last_inspected_at` timestamp NULL DEFAULT NULL,
  `ownership` varchar(20) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `in_service_date` date DEFAULT NULL,
  `vendor_id` bigint(20) unsigned DEFAULT NULL,
  `invoice_ref` varchar(255) DEFAULT NULL,
  `funding_source` varchar(255) DEFAULT NULL,
  `pm_interval_days` smallint(5) unsigned DEFAULT NULL,
  `specification` text DEFAULT NULL,
  `warranty_until` date DEFAULT NULL,
  `contract_ref` varchar(255) DEFAULT NULL,
  `verification_status` varchar(20) NOT NULL DEFAULT 'unverified',
  `legacy_code` varchar(255) DEFAULT NULL,
  `migration_source` varchar(255) DEFAULT NULL,
  `generation_batch_id` bigint(20) unsigned DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `verified_by` bigint(20) unsigned DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `installation_status` varchar(20) NOT NULL DEFAULT 'pending',
  `version` int(10) unsigned NOT NULL DEFAULT 1,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `assets_property_id_asset_code_unique` (`property_id`,`asset_code`),
  UNIQUE KEY `assets_public_id_unique` (`public_id`),
  KEY `assets_category_id_foreign` (`category_id`),
  KEY `assets_location_id_foreign` (`location_id`),
  KEY `assets_department_id_foreign` (`department_id`),
  KEY `assets_custodian_id_foreign` (`custodian_id`),
  KEY `assets_parent_id_foreign` (`parent_id`),
  KEY `assets_vendor_id_foreign` (`vendor_id`),
  KEY `assets_created_by_foreign` (`created_by`),
  KEY `assets_verified_by_foreign` (`verified_by`),
  KEY `assets_property_id_lifecycle_status_index` (`property_id`,`lifecycle_status`),
  KEY `assets_property_id_location_id_index` (`property_id`,`location_id`),
  KEY `assets_serial_number_index` (`serial_number`),
  KEY `assets_legacy_code_index` (`legacy_code`),
  KEY `assets_generation_batch_id_index` (`generation_batch_id`),
  CONSTRAINT `assets_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `asset_categories` (`id`),
  CONSTRAINT `assets_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `assets_custodian_id_foreign` FOREIGN KEY (`custodian_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `assets_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
  CONSTRAINT `assets_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`),
  CONSTRAINT `assets_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `assets` (`id`),
  CONSTRAINT `assets_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`),
  CONSTRAINT `assets_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE SET NULL,
  CONSTRAINT `assets_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assets`
--

LOCK TABLES `assets` WRITE;
/*!40000 ALTER TABLE `assets` DISABLE KEYS */;
/*!40000 ALTER TABLE `assets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attachments`
--

DROP TABLE IF EXISTS `attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `attachments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `attachable_type` varchar(255) NOT NULL,
  `attachable_id` bigint(20) unsigned NOT NULL,
  `kind` varchar(30) NOT NULL DEFAULT 'evidence',
  `disk` varchar(20) NOT NULL DEFAULT 'local',
  `path` varchar(255) NOT NULL,
  `original_name` varchar(255) DEFAULT NULL,
  `mime` varchar(100) DEFAULT NULL,
  `size` bigint(20) unsigned DEFAULT NULL,
  `checksum` varchar(64) DEFAULT NULL,
  `visibility` varchar(20) NOT NULL DEFAULT 'internal',
  `uploaded_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attachments_attachable_type_attachable_id_index` (`attachable_type`,`attachable_id`),
  KEY `attachments_uploaded_by_foreign` (`uploaded_by`),
  CONSTRAINT `attachments_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attachments`
--

LOCK TABLES `attachments` WRITE;
/*!40000 ALTER TABLE `attachments` DISABLE KEYS */;
/*!40000 ALTER TABLE `attachments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `property_id` bigint(20) unsigned DEFAULT NULL,
  `event` varchar(30) NOT NULL,
  `auditable_type` varchar(255) NOT NULL,
  `auditable_id` bigint(20) unsigned DEFAULT NULL,
  `auditable_label` varchar(255) DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `reason` varchar(255) DEFAULT NULL,
  `request_id` varchar(40) DEFAULT NULL,
  `url` varchar(500) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `audit_logs_user_id_foreign` (`user_id`),
  KEY `audit_logs_property_id_foreign` (`property_id`),
  KEY `audit_logs_auditable_type_auditable_id_index` (`auditable_type`,`auditable_id`),
  CONSTRAINT `audit_logs_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE SET NULL,
  CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` bigint(20) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` bigint(20) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `client_events`
--

DROP TABLE IF EXISTS `client_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `client_events` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `action` varchar(60) NOT NULL,
  `payload_hash` varchar(64) NOT NULL,
  `result` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`result`)),
  `device_time` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `client_events_user_id_action_uuid_unique` (`user_id`,`action`,`uuid`),
  CONSTRAINT `client_events_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `client_events`
--

LOCK TABLES `client_events` WRITE;
/*!40000 ALTER TABLE `client_events` DISABLE KEYS */;
/*!40000 ALTER TABLE `client_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `comments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `commentable_type` varchar(255) NOT NULL,
  `commentable_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `body` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `comments_commentable_type_commentable_id_index` (`commentable_type`,`commentable_id`),
  KEY `comments_user_id_foreign` (`user_id`),
  CONSTRAINT `comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comments`
--

LOCK TABLES `comments` WRITE;
/*!40000 ALTER TABLE `comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `departments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint(20) unsigned NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `cost_center` varchar(40) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `departments_property_id_code_unique` (`property_id`,`code`),
  CONSTRAINT `departments_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departments`
--

LOCK TABLES `departments` WRITE;
/*!40000 ALTER TABLE `departments` DISABLE KEYS */;
INSERT INTO `departments` VALUES
(1,1,'EXE','Executive Office',NULL,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(2,1,'FO','Front Office',NULL,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(3,1,'HK','Housekeeping',NULL,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(4,1,'FB','Food & Beverage Service',NULL,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(5,1,'KIT','Kitchen & Pastry',NULL,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(6,1,'ENG','Engineering',NULL,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(7,1,'SM','Sales & Marketing',NULL,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(8,1,'ACC','Accounting & Finance',NULL,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(9,1,'HR','Human Resources',NULL,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(10,1,'PUR','Purchasing',NULL,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(11,1,'EDP','EDP / IT',NULL,1,'2026-11-02 03:49:43','2026-11-02 03:49:43'),
(12,1,'SEC','Security',NULL,1,'2026-11-02 03:49:43','2026-11-02 03:49:43');
/*!40000 ALTER TABLE `departments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` varchar(255) NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `generation_batches`
--

DROP TABLE IF EXISTS `generation_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `generation_batches` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint(20) unsigned NOT NULL,
  `number` varchar(30) NOT NULL,
  `template_id` bigint(20) unsigned NOT NULL,
  `template_version` smallint(5) unsigned NOT NULL,
  `idempotency_key` varchar(64) NOT NULL,
  `room_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`room_ids`)),
  `preview_snapshot` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`preview_snapshot`)),
  `match_decisions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`match_decisions`)),
  `status` varchar(20) NOT NULL DEFAULT 'previewed',
  `created_assets` int(10) unsigned NOT NULL DEFAULT 0,
  `matched_assets` int(10) unsigned NOT NULL DEFAULT 0,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `committed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `generation_batches_number_unique` (`number`),
  UNIQUE KEY `generation_batches_idempotency_key_unique` (`idempotency_key`),
  KEY `generation_batches_property_id_foreign` (`property_id`),
  KEY `generation_batches_template_id_foreign` (`template_id`),
  KEY `generation_batches_created_by_foreign` (`created_by`),
  CONSTRAINT `generation_batches_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `generation_batches_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`),
  CONSTRAINT `generation_batches_template_id_foreign` FOREIGN KEY (`template_id`) REFERENCES `room_asset_templates` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `generation_batches`
--

LOCK TABLES `generation_batches` WRITE;
/*!40000 ALTER TABLE `generation_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `generation_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `generation_results`
--

DROP TABLE IF EXISTS `generation_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `generation_results` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `batch_id` bigint(20) unsigned NOT NULL,
  `room_id` bigint(20) unsigned NOT NULL,
  `status` varchar(12) NOT NULL,
  `created_count` int(10) unsigned NOT NULL DEFAULT 0,
  `matched_count` int(10) unsigned NOT NULL DEFAULT 0,
  `skipped_count` int(10) unsigned NOT NULL DEFAULT 0,
  `message` text DEFAULT NULL,
  `detail` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`detail`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `generation_results_batch_id_room_id_unique` (`batch_id`,`room_id`),
  KEY `generation_results_room_id_foreign` (`room_id`),
  CONSTRAINT `generation_results_batch_id_foreign` FOREIGN KEY (`batch_id`) REFERENCES `generation_batches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `generation_results_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `locations` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `generation_results`
--

LOCK TABLES `generation_results` WRITE;
/*!40000 ALTER TABLE `generation_results` DISABLE KEYS */;
/*!40000 ALTER TABLE `generation_results` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `installation_events`
--

DROP TABLE IF EXISTS `installation_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `installation_events` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `asset_label_id` bigint(20) unsigned NOT NULL,
  `asset_id` bigint(20) unsigned DEFAULT NULL,
  `quantity_group_id` bigint(20) unsigned DEFAULT NULL,
  `location_id` bigint(20) unsigned DEFAULT NULL,
  `print_item_id` bigint(20) unsigned DEFAULT NULL,
  `status` varchar(20) NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `actor_id` bigint(20) unsigned DEFAULT NULL,
  `occurred_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `installation_events_asset_id_foreign` (`asset_id`),
  KEY `installation_events_quantity_group_id_foreign` (`quantity_group_id`),
  KEY `installation_events_location_id_foreign` (`location_id`),
  KEY `installation_events_print_item_id_foreign` (`print_item_id`),
  KEY `installation_events_actor_id_foreign` (`actor_id`),
  KEY `installation_events_asset_label_id_occurred_at_index` (`asset_label_id`,`occurred_at`),
  CONSTRAINT `installation_events_actor_id_foreign` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `installation_events_asset_id_foreign` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE SET NULL,
  CONSTRAINT `installation_events_asset_label_id_foreign` FOREIGN KEY (`asset_label_id`) REFERENCES `asset_labels` (`id`),
  CONSTRAINT `installation_events_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `installation_events_print_item_id_foreign` FOREIGN KEY (`print_item_id`) REFERENCES `label_print_items` (`id`) ON DELETE SET NULL,
  CONSTRAINT `installation_events_quantity_group_id_foreign` FOREIGN KEY (`quantity_group_id`) REFERENCES `asset_quantity_groups` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `installation_events`
--

LOCK TABLES `installation_events` WRITE;
/*!40000 ALTER TABLE `installation_events` DISABLE KEYS */;
/*!40000 ALTER TABLE `installation_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` smallint(5) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `label_print_batches`
--

DROP TABLE IF EXISTS `label_print_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `label_print_batches` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint(20) unsigned NOT NULL,
  `number` varchar(30) NOT NULL,
  `template_version` varchar(20) NOT NULL,
  `media_profile` varchar(30) NOT NULL,
  `start_cell` smallint(5) unsigned NOT NULL DEFAULT 1,
  `filters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`filters`)),
  `purpose` varchar(12) NOT NULL DEFAULT 'new',
  `reprint_reason` varchar(255) DEFAULT NULL,
  `label_count` int(10) unsigned NOT NULL DEFAULT 0,
  `sheet_count` int(10) unsigned NOT NULL DEFAULT 0,
  `status` varchar(20) NOT NULL DEFAULT 'ready',
  `file_path` varchar(255) DEFAULT NULL,
  `generated_at` timestamp NULL DEFAULT NULL,
  `downloaded_at` timestamp NULL DEFAULT NULL,
  `printed_confirmed_at` timestamp NULL DEFAULT NULL,
  `printed_confirmed_by` bigint(20) unsigned DEFAULT NULL,
  `operator_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `label_print_batches_number_unique` (`number`),
  KEY `label_print_batches_property_id_foreign` (`property_id`),
  KEY `label_print_batches_printed_confirmed_by_foreign` (`printed_confirmed_by`),
  KEY `label_print_batches_operator_id_foreign` (`operator_id`),
  CONSTRAINT `label_print_batches_operator_id_foreign` FOREIGN KEY (`operator_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `label_print_batches_printed_confirmed_by_foreign` FOREIGN KEY (`printed_confirmed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `label_print_batches_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `label_print_batches`
--

LOCK TABLES `label_print_batches` WRITE;
/*!40000 ALTER TABLE `label_print_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `label_print_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `label_print_items`
--

DROP TABLE IF EXISTS `label_print_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `label_print_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `batch_id` bigint(20) unsigned NOT NULL,
  `asset_label_id` bigint(20) unsigned NOT NULL,
  `asset_id` bigint(20) unsigned DEFAULT NULL,
  `quantity_group_id` bigint(20) unsigned DEFAULT NULL,
  `location_id` bigint(20) unsigned DEFAULT NULL,
  `location_snapshot` varchar(255) DEFAULT NULL,
  `name_snapshot` varchar(255) NOT NULL,
  `code_snapshot` varchar(40) NOT NULL,
  `template_version` varchar(20) NOT NULL,
  `sheet_no` smallint(5) unsigned NOT NULL DEFAULT 1,
  `cell_no` smallint(5) unsigned NOT NULL DEFAULT 1,
  `copy_no` smallint(5) unsigned NOT NULL DEFAULT 1,
  `reprint_reason` varchar(255) DEFAULT NULL,
  `sequence` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `label_print_items_batch_id_foreign` (`batch_id`),
  KEY `label_print_items_asset_label_id_foreign` (`asset_label_id`),
  KEY `label_print_items_asset_id_foreign` (`asset_id`),
  KEY `label_print_items_quantity_group_id_foreign` (`quantity_group_id`),
  KEY `label_print_items_location_id_foreign` (`location_id`),
  CONSTRAINT `label_print_items_asset_id_foreign` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE SET NULL,
  CONSTRAINT `label_print_items_asset_label_id_foreign` FOREIGN KEY (`asset_label_id`) REFERENCES `asset_labels` (`id`),
  CONSTRAINT `label_print_items_batch_id_foreign` FOREIGN KEY (`batch_id`) REFERENCES `label_print_batches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `label_print_items_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `label_print_items_quantity_group_id_foreign` FOREIGN KEY (`quantity_group_id`) REFERENCES `asset_quantity_groups` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `label_print_items`
--

LOCK TABLES `label_print_items` WRITE;
/*!40000 ALTER TABLE `label_print_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `label_print_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `locations`
--

DROP TABLE IF EXISTS `locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `locations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint(20) unsigned NOT NULL,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `code` varchar(40) NOT NULL,
  `name` varchar(255) NOT NULL,
  `location_type` varchar(20) NOT NULL,
  `room_type_id` bigint(20) unsigned DEFAULT NULL,
  `room_ref` varchar(20) DEFAULT NULL COMMENT 'nomor kamar di PMS',
  `floor_label` varchar(20) DEFAULT NULL,
  `sort_key` varchar(60) DEFAULT NULL COMMENT 'urutan natural: gedung/lantai/nomor',
  `pms_room_status` varchar(30) DEFAULT NULL COMMENT 'informasi terpisah dari status aset',
  `full_path` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `locations_property_id_code_unique` (`property_id`,`code`),
  KEY `locations_parent_id_foreign` (`parent_id`),
  KEY `locations_room_type_id_foreign` (`room_type_id`),
  KEY `locations_property_id_location_type_index` (`property_id`,`location_type`),
  KEY `locations_sort_key_index` (`sort_key`),
  CONSTRAINT `locations_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `locations` (`id`),
  CONSTRAINT `locations_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`),
  CONSTRAINT `locations_room_type_id_foreign` FOREIGN KEY (`room_type_id`) REFERENCES `room_types` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `locations`
--

LOCK TABLES `locations` WRITE;
/*!40000 ALTER TABLE `locations` DISABLE KEYS */;
/*!40000 ALTER TABLE `locations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES
(1,'0001_01_01_000000_create_users_table',1),
(2,'0001_01_01_000001_create_cache_table',1),
(3,'0001_01_01_000002_create_jobs_table',1),
(4,'2026_10_21_000001_create_reference_tables',1),
(5,'2026_10_21_000002_create_asset_tables',1),
(6,'2026_10_21_000003_create_label_and_room_template_tables',1),
(7,'2026_10_21_000004_create_maintenance_tables',1),
(8,'2026_11_02_000005_add_branding_to_properties',1),
(9,'2026_11_02_000006_create_survey_tables',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notification_logs`
--

DROP TABLE IF EXISTS `notification_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `notification_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `dedupe_key` varchar(191) NOT NULL,
  `channel` varchar(20) NOT NULL DEFAULT 'database',
  `status` varchar(20) NOT NULL DEFAULT 'sent',
  `recipients` int(10) unsigned NOT NULL DEFAULT 0,
  `error` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `notification_logs_dedupe_key_unique` (`dedupe_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notification_logs`
--

LOCK TABLES `notification_logs` WRITE;
/*!40000 ALTER TABLE `notification_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `notification_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) unsigned NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permission_role`
--

DROP TABLE IF EXISTS `permission_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `permission_role` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `permission_role_role_id_foreign` (`role_id`),
  CONSTRAINT `permission_role_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `permission_role_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permission_role`
--

LOCK TABLES `permission_role` WRITE;
/*!40000 ALTER TABLE `permission_role` DISABLE KEYS */;
INSERT INTO `permission_role` VALUES
(1,1),
(1,2),
(1,3),
(1,4),
(1,5),
(1,11),
(1,12),
(2,1),
(2,2),
(2,3),
(2,4),
(2,5),
(2,7),
(2,11),
(2,12),
(2,16),
(3,1),
(3,2),
(3,3),
(3,4),
(3,5),
(3,11),
(3,12),
(3,16),
(4,1),
(4,2),
(4,3),
(4,4),
(4,5),
(4,6),
(4,7),
(4,8),
(4,9),
(4,10),
(4,11),
(4,12),
(4,13),
(4,14),
(4,15),
(4,16),
(5,1),
(5,5),
(5,11),
(6,1),
(6,5),
(6,8),
(6,11),
(7,1),
(7,11),
(8,1),
(8,2),
(8,3),
(8,4),
(8,12),
(8,16),
(9,1),
(9,12),
(10,1),
(10,5),
(10,11),
(11,1),
(11,4),
(12,1),
(12,11),
(13,1),
(13,11),
(14,1),
(14,11),
(15,1),
(15,5),
(15,6),
(15,8),
(15,11),
(16,1),
(16,5),
(16,8),
(16,11),
(17,1),
(17,5),
(17,7),
(17,11),
(18,1),
(18,4),
(18,7),
(18,12),
(19,1),
(19,5),
(19,6),
(19,11),
(20,1),
(20,4),
(20,5),
(20,6),
(20,7),
(20,8),
(20,9),
(20,10),
(20,11),
(20,13),
(20,14),
(20,15),
(21,1),
(21,2),
(21,3),
(21,4),
(21,5),
(21,12),
(21,16),
(22,1),
(22,5),
(23,1),
(23,2),
(23,3),
(23,4),
(23,5),
(23,6),
(23,7),
(23,10),
(23,12),
(23,13),
(23,14),
(23,16),
(24,1),
(24,5),
(25,1),
(25,5),
(26,1),
(26,5),
(26,6),
(27,1),
(27,5),
(28,1),
(28,4),
(28,5),
(28,12),
(29,1),
(29,4),
(29,5),
(30,1),
(30,12),
(31,1),
(31,10),
(32,1),
(32,11),
(32,14),
(32,15),
(33,1),
(33,15),
(34,1),
(34,15),
(35,1),
(35,15),
(36,1),
(36,2),
(36,3),
(36,4),
(36,15),
(36,16);
/*!40000 ALTER TABLE `permission_role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(80) NOT NULL,
  `name` varchar(255) NOT NULL,
  `group` varchar(60) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES
(1,'dashboard.view','Beranda manajerial (KPI, risiko, biaya)','Umum','2026-11-02 03:49:43','2026-11-02 03:49:43'),
(2,'report.view','Melihat laporan','Umum','2026-11-02 03:49:43','2026-11-02 03:49:43'),
(3,'report.export','Ekspor laporan (CSV)','Umum','2026-11-02 03:49:43','2026-11-02 03:49:43'),
(4,'asset.view','Melihat register & kartu aset','Aset','2026-11-02 03:49:43','2026-11-02 03:49:43'),
(5,'asset.edit','Membuat & mengubah data aset','Aset','2026-11-02 03:49:43','2026-11-02 03:49:43'),
(6,'asset.verify','Verifikasi fisik aset','Aset','2026-11-02 03:49:43','2026-11-02 03:49:43'),
(7,'asset.activate','Mengaktifkan / menghentikan aset (siklus hidup)','Aset','2026-11-02 03:49:43','2026-11-02 03:49:43'),
(8,'asset.view_financial','Melihat harga & nilai aset','Aset','2026-11-02 03:49:43','2026-11-02 03:49:43'),
(9,'asset.edit_financial','Mengubah data finansial aset','Aset','2026-11-02 03:49:43','2026-11-02 03:49:43'),
(10,'template.manage','Menyusun template aset tipe kamar','Aset','2026-11-02 03:49:43','2026-11-02 03:49:43'),
(11,'template.publish','Memeriksa & menerbitkan template','Aset','2026-11-02 03:49:43','2026-11-02 03:49:43'),
(12,'generation.run','Pembuatan aset massal untuk kamar','Aset','2026-11-02 03:49:43','2026-11-02 03:49:43'),
(13,'label.print','Membuat batch & cetak label QR','Aset','2026-11-02 03:49:43','2026-11-02 03:49:43'),
(14,'label.rotate','Rotasi token label (nonaktifkan label lama)','Aset','2026-11-02 03:49:43','2026-11-02 03:49:43'),
(15,'label.install','Konfirmasi pemasangan label','Aset','2026-11-02 03:49:43','2026-11-02 03:49:43'),
(16,'survey.run','Pendataan aset lapangan (checklist kamar, tempel & hubungkan label)','Aset','2026-11-02 03:49:43','2026-11-02 03:49:43'),
(17,'transfer.request','Mengajukan mutasi aset','Aset','2026-11-02 03:49:43','2026-11-02 03:49:43'),
(18,'transfer.approve','Menyetujui mutasi aset','Aset','2026-11-02 03:49:43','2026-11-02 03:49:43'),
(19,'transfer.receive','Mengirim / menerima mutasi aset','Aset','2026-11-02 03:49:43','2026-11-02 03:49:43'),
(20,'request.create','Membuat laporan kerusakan','Pemeliharaan','2026-11-02 03:49:43','2026-11-02 03:49:43'),
(21,'request.view_all','Melihat semua laporan (lintas departemen)','Pemeliharaan','2026-11-02 03:49:43','2026-11-02 03:49:43'),
(22,'request.triage','Triase laporan & menetapkan prioritas','Pemeliharaan','2026-11-02 03:49:43','2026-11-02 03:49:43'),
(23,'wo.view','Melihat work order','Pemeliharaan','2026-11-02 03:49:43','2026-11-02 03:49:43'),
(24,'wo.create','Membuat work order','Pemeliharaan','2026-11-02 03:49:43','2026-11-02 03:49:43'),
(25,'wo.assign','Menugaskan & menjadwalkan work order','Pemeliharaan','2026-11-02 03:49:43','2026-11-02 03:49:43'),
(26,'wo.execute','Mengerjakan work order (teknisi)','Pemeliharaan','2026-11-02 03:49:43','2026-11-02 03:49:43'),
(27,'wo.verify','Memverifikasi hasil pekerjaan','Pemeliharaan','2026-11-02 03:49:43','2026-11-02 03:49:43'),
(28,'wo.approve','Menyetujui biaya / lingkup work order','Pemeliharaan','2026-11-02 03:49:43','2026-11-02 03:49:43'),
(29,'wo.close','Menutup & membuka ulang work order','Pemeliharaan','2026-11-02 03:49:43','2026-11-02 03:49:43'),
(30,'wo.cost_finalize','Finalisasi biaya (Finance)','Pemeliharaan','2026-11-02 03:49:43','2026-11-02 03:49:43'),
(31,'room.block_confirm','Konfirmasi blokir kamar (Front Office)','Pemeliharaan','2026-11-02 03:49:43','2026-11-02 03:49:43'),
(32,'reference.manage','Lokasi, kategori, departemen, tipe kamar, vendor','Pengaturan','2026-11-02 03:49:43','2026-11-02 03:49:43'),
(33,'settings.manage','SLA & batas persetujuan','Pengaturan','2026-11-02 03:49:43','2026-11-02 03:49:43'),
(34,'user.manage','Pengguna & penugasan peran','Pengaturan','2026-11-02 03:49:43','2026-11-02 03:49:43'),
(35,'role.manage','Peran & izin','Pengaturan','2026-11-02 03:49:43','2026-11-02 03:49:43'),
(36,'audit.view','Log audit','Pengaturan','2026-11-02 03:49:43','2026-11-02 03:49:43');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `properties`
--

DROP TABLE IF EXISTS `properties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `properties` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `legal_name` varchar(255) DEFAULT NULL,
  `timezone` varchar(40) NOT NULL DEFAULT 'Asia/Jakarta',
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(80) DEFAULT NULL,
  `phone` varchar(40) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `star_rating` varchar(10) DEFAULT NULL,
  `room_count` smallint(5) unsigned DEFAULT NULL,
  `app_name` varchar(60) DEFAULT NULL,
  `tagline` varchar(120) DEFAULT NULL,
  `logo_path` varchar(255) DEFAULT NULL,
  `logo_dark_path` varchar(255) DEFAULT NULL,
  `primary_color` varchar(7) NOT NULL DEFAULT '#1F3A5F',
  `accent_color` varchar(7) NOT NULL DEFAULT '#C8102E',
  `currency` varchar(3) NOT NULL DEFAULT 'IDR',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `properties_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `properties`
--

LOCK TABLES `properties` WRITE;
/*!40000 ALTER TABLE `properties` DISABLE KEYS */;
INSERT INTO `properties` VALUES
(1,'HTL','Hotel Contoh',NULL,'Asia/Jakarta',NULL,NULL,NULL,NULL,NULL,NULL,'SIAGA',NULL,NULL,NULL,'#1F3A5F','#C8102E','IDR',1,'2026-11-02 03:49:43','2026-11-02 03:49:43');
/*!40000 ALTER TABLE `properties` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `quantity_movements`
--

DROP TABLE IF EXISTS `quantity_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `quantity_movements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `quantity_group_id` bigint(20) unsigned NOT NULL,
  `type` varchar(20) NOT NULL,
  `qty_before` decimal(14,2) DEFAULT NULL,
  `qty_after` decimal(14,2) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `actor_id` bigint(20) unsigned DEFAULT NULL,
  `occurred_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  KEY `quantity_movements_quantity_group_id_foreign` (`quantity_group_id`),
  KEY `quantity_movements_actor_id_foreign` (`actor_id`),
  CONSTRAINT `quantity_movements_actor_id_foreign` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `quantity_movements_quantity_group_id_foreign` FOREIGN KEY (`quantity_group_id`) REFERENCES `asset_quantity_groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `quantity_movements`
--

LOCK TABLES `quantity_movements` WRITE;
/*!40000 ALTER TABLE `quantity_movements` DISABLE KEYS */;
/*!40000 ALTER TABLE `quantity_movements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `request_links`
--

DROP TABLE IF EXISTS `request_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `request_links` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `service_request_id` bigint(20) unsigned NOT NULL,
  `work_order_id` bigint(20) unsigned NOT NULL,
  `linked_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `request_links_service_request_id_work_order_id_unique` (`service_request_id`,`work_order_id`),
  KEY `request_links_work_order_id_foreign` (`work_order_id`),
  KEY `request_links_linked_by_foreign` (`linked_by`),
  CONSTRAINT `request_links_linked_by_foreign` FOREIGN KEY (`linked_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `request_links_service_request_id_foreign` FOREIGN KEY (`service_request_id`) REFERENCES `service_requests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `request_links_work_order_id_foreign` FOREIGN KEY (`work_order_id`) REFERENCES `work_orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `request_links`
--

LOCK TABLES `request_links` WRITE;
/*!40000 ALTER TABLE `request_links` DISABLE KEYS */;
/*!40000 ALTER TABLE `request_links` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_user`
--

DROP TABLE IF EXISTS `role_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `property_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_user_role_id_user_id_property_id_unique` (`role_id`,`user_id`,`property_id`),
  KEY `role_user_user_id_foreign` (`user_id`),
  KEY `role_user_property_id_foreign` (`property_id`),
  CONSTRAINT `role_user_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_user_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_user`
--

LOCK TABLES `role_user` WRITE;
/*!40000 ALTER TABLE `role_user` DISABLE KEYS */;
INSERT INTO `role_user` VALUES
(1,1,1,NULL);
/*!40000 ALTER TABLE `role_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(60) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES
(1,'super-admin','Super Admin','Konsultan / vendor aplikasi — semua izin','2026-11-02 03:49:43','2026-11-02 03:49:43'),
(2,'owner','Owner','Melihat seluruh kinerja & biaya; memutuskan investasi/penghapusan','2026-11-02 03:49:43','2026-11-02 03:49:43'),
(3,'owner-rep','Owner Representative','Memantau risiko aset & anggaran; mengkaji prioritas & usulan penggantian','2026-11-02 03:49:43','2026-11-02 03:49:43'),
(4,'gm','General Manager','Mengawasi operasi, backlog, downtime, SLA & persetujuan sesuai batas nilai','2026-11-02 03:49:43','2026-11-02 03:49:43'),
(5,'chief-engineering','Chief Engineering','Triase, prioritas, penjadwalan teknisi & verifikasi pekerjaan','2026-11-02 03:49:43','2026-11-02 03:49:43'),
(6,'technician','Teknisi','Melihat tugas & aset relevan; mencatat diagnosis, waktu, material & bukti','2026-11-02 03:49:43','2026-11-02 03:49:43'),
(7,'hod','HOD','Melapor, melihat laporan departemennya, mengonfirmasi layanan & kondisi aset','2026-11-02 03:49:43','2026-11-02 03:49:43'),
(8,'surveyor','Petugas Pendataan','Keliling kamar/area: tempel label QR, cek isi kamar, catat kondisi & foto','2026-11-02 03:49:43','2026-11-02 03:49:43'),
(9,'reporter','Pelapor (staf)','Membuat laporan kerusakan & melihat laporan sendiri','2026-11-02 03:49:43','2026-11-02 03:49:43'),
(10,'front-office','Front Office','Melapor & mengonfirmasi blokir kamar terkait perbaikan','2026-11-02 03:49:43','2026-11-02 03:49:43'),
(11,'asset-controller','Asset Controller','Memelihara register, label & inventarisasi; mengusulkan mutasi & koreksi','2026-11-02 03:49:43','2026-11-02 03:49:43'),
(12,'finance','Finance / Chief Accountant','Data finansial, validasi biaya, anggaran & rekonsiliasi','2026-11-02 03:49:43','2026-11-02 03:49:43'),
(13,'storekeeper','Storekeeper','Mengelola stok engineering (rilis B)','2026-11-02 03:49:43','2026-11-02 03:49:43'),
(14,'purchasing','Purchasing','Vendor & referensi pembelian','2026-11-02 03:49:43','2026-11-02 03:49:43'),
(15,'it-admin','IT Administrator','Konfigurasi teknis & akun; tidak otomatis berwenang menyetujui biaya','2026-11-02 03:49:43','2026-11-02 03:49:43'),
(16,'auditor','Auditor','Membaca data & jejak audit dalam lingkup yang ditetapkan','2026-11-02 03:49:43','2026-11-02 03:49:43');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `room_asset_slots`
--

DROP TABLE IF EXISTS `room_asset_slots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `room_asset_slots` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint(20) unsigned NOT NULL,
  `room_id` bigint(20) unsigned NOT NULL,
  `item_key` varchar(40) NOT NULL,
  `unit_position` smallint(5) unsigned NOT NULL DEFAULT 1,
  `position_label` varchar(255) DEFAULT NULL,
  `category_id` bigint(20) unsigned NOT NULL,
  `standard_name` varchar(255) NOT NULL,
  `mode` varchar(12) NOT NULL DEFAULT 'individual',
  `expected_qty` decimal(14,2) DEFAULT NULL COMMENT 'hanya slot kuantitas',
  `template_id` bigint(20) unsigned DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'empty',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `room_slot_key_unique` (`property_id`,`room_id`,`item_key`,`unit_position`),
  KEY `room_asset_slots_room_id_foreign` (`room_id`),
  KEY `room_asset_slots_category_id_foreign` (`category_id`),
  KEY `room_asset_slots_template_id_foreign` (`template_id`),
  CONSTRAINT `room_asset_slots_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `asset_categories` (`id`),
  CONSTRAINT `room_asset_slots_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`),
  CONSTRAINT `room_asset_slots_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `locations` (`id`),
  CONSTRAINT `room_asset_slots_template_id_foreign` FOREIGN KEY (`template_id`) REFERENCES `room_asset_templates` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `room_asset_slots`
--

LOCK TABLES `room_asset_slots` WRITE;
/*!40000 ALTER TABLE `room_asset_slots` DISABLE KEYS */;
/*!40000 ALTER TABLE `room_asset_slots` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `room_asset_templates`
--

DROP TABLE IF EXISTS `room_asset_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `room_asset_templates` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint(20) unsigned NOT NULL,
  `code` varchar(30) NOT NULL,
  `version` smallint(5) unsigned NOT NULL DEFAULT 1,
  `name` varchar(255) NOT NULL,
  `room_type_id` bigint(20) unsigned NOT NULL,
  `status` varchar(12) NOT NULL DEFAULT 'draft',
  `notes` text DEFAULT NULL,
  `prepared_by` bigint(20) unsigned DEFAULT NULL,
  `reviewed_by` bigint(20) unsigned DEFAULT NULL,
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `room_asset_templates_property_id_code_version_unique` (`property_id`,`code`,`version`),
  KEY `room_asset_templates_room_type_id_foreign` (`room_type_id`),
  KEY `room_asset_templates_prepared_by_foreign` (`prepared_by`),
  KEY `room_asset_templates_reviewed_by_foreign` (`reviewed_by`),
  CONSTRAINT `room_asset_templates_prepared_by_foreign` FOREIGN KEY (`prepared_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `room_asset_templates_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`),
  CONSTRAINT `room_asset_templates_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `room_asset_templates_room_type_id_foreign` FOREIGN KEY (`room_type_id`) REFERENCES `room_types` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `room_asset_templates`
--

LOCK TABLES `room_asset_templates` WRITE;
/*!40000 ALTER TABLE `room_asset_templates` DISABLE KEYS */;
/*!40000 ALTER TABLE `room_asset_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `room_blocks`
--

DROP TABLE IF EXISTS `room_blocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `room_blocks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `room_id` bigint(20) unsigned NOT NULL,
  `work_order_id` bigint(20) unsigned DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'proposed',
  `started_at` timestamp NULL DEFAULT NULL,
  `ended_at` timestamp NULL DEFAULT NULL,
  `reason` varchar(255) NOT NULL,
  `proposed_by` bigint(20) unsigned DEFAULT NULL,
  `fo_confirmed_by` bigint(20) unsigned DEFAULT NULL,
  `fo_confirmed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `room_blocks_room_id_foreign` (`room_id`),
  KEY `room_blocks_work_order_id_foreign` (`work_order_id`),
  KEY `room_blocks_proposed_by_foreign` (`proposed_by`),
  KEY `room_blocks_fo_confirmed_by_foreign` (`fo_confirmed_by`),
  CONSTRAINT `room_blocks_fo_confirmed_by_foreign` FOREIGN KEY (`fo_confirmed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `room_blocks_proposed_by_foreign` FOREIGN KEY (`proposed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `room_blocks_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `locations` (`id`),
  CONSTRAINT `room_blocks_work_order_id_foreign` FOREIGN KEY (`work_order_id`) REFERENCES `work_orders` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `room_blocks`
--

LOCK TABLES `room_blocks` WRITE;
/*!40000 ALTER TABLE `room_blocks` DISABLE KEYS */;
/*!40000 ALTER TABLE `room_blocks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `room_exceptions`
--

DROP TABLE IF EXISTS `room_exceptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `room_exceptions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `room_id` bigint(20) unsigned NOT NULL,
  `item_key` varchar(40) NOT NULL,
  `type` varchar(20) NOT NULL,
  `quantity` smallint(5) unsigned DEFAULT NULL COMMENT 'jumlah aktual untuk tipe quantity/added',
  `substitute_category_id` bigint(20) unsigned DEFAULT NULL,
  `substitute_name` varchar(255) DEFAULT NULL,
  `reason` varchar(255) NOT NULL,
  `recorded_by` bigint(20) unsigned DEFAULT NULL,
  `reviewed_by` bigint(20) unsigned DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `room_exceptions_room_id_item_key_unique` (`room_id`,`item_key`),
  KEY `room_exceptions_substitute_category_id_foreign` (`substitute_category_id`),
  KEY `room_exceptions_recorded_by_foreign` (`recorded_by`),
  KEY `room_exceptions_reviewed_by_foreign` (`reviewed_by`),
  CONSTRAINT `room_exceptions_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `room_exceptions_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `room_exceptions_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `room_exceptions_substitute_category_id_foreign` FOREIGN KEY (`substitute_category_id`) REFERENCES `asset_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `room_exceptions`
--

LOCK TABLES `room_exceptions` WRITE;
/*!40000 ALTER TABLE `room_exceptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `room_exceptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `room_surveys`
--

DROP TABLE IF EXISTS `room_surveys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `room_surveys` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint(20) unsigned NOT NULL,
  `location_id` bigint(20) unsigned NOT NULL,
  `status` varchar(12) NOT NULL DEFAULT 'in_progress',
  `surveyor_id` bigint(20) unsigned DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `finished_at` timestamp NULL DEFAULT NULL,
  `found_count` smallint(5) unsigned NOT NULL DEFAULT 0,
  `missing_count` smallint(5) unsigned NOT NULL DEFAULT 0,
  `extra_count` smallint(5) unsigned NOT NULL DEFAULT 0,
  `note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `room_surveys_location_id_unique` (`location_id`),
  KEY `room_surveys_surveyor_id_foreign` (`surveyor_id`),
  KEY `room_surveys_property_id_status_index` (`property_id`,`status`),
  CONSTRAINT `room_surveys_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`),
  CONSTRAINT `room_surveys_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`),
  CONSTRAINT `room_surveys_surveyor_id_foreign` FOREIGN KEY (`surveyor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `room_surveys`
--

LOCK TABLES `room_surveys` WRITE;
/*!40000 ALTER TABLE `room_surveys` DISABLE KEYS */;
/*!40000 ALTER TABLE `room_surveys` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `room_template_items`
--

DROP TABLE IF EXISTS `room_template_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `room_template_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `template_id` bigint(20) unsigned NOT NULL,
  `item_key` varchar(40) NOT NULL COMMENT 'stabil lintas versi, mis. tv-utama',
  `category_id` bigint(20) unsigned NOT NULL,
  `standard_name` varchar(255) NOT NULL,
  `quantity` smallint(5) unsigned NOT NULL DEFAULT 1,
  `mode` varchar(12) NOT NULL DEFAULT 'individual',
  `unit` varchar(20) NOT NULL DEFAULT 'unit',
  `positions` varchar(255) DEFAULT NULL COMMENT 'label posisi per unit, pisahkan koma: Kiri, Kanan',
  `default_department_id` bigint(20) unsigned DEFAULT NULL,
  `default_criticality` varchar(10) DEFAULT NULL,
  `specification` text DEFAULT NULL,
  `pm_interval_days` smallint(5) unsigned DEFAULT NULL COMMENT 'usulan; berlaku setelah aktivasi',
  `sort` smallint(5) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `room_template_items_template_id_item_key_unique` (`template_id`,`item_key`),
  KEY `room_template_items_category_id_foreign` (`category_id`),
  KEY `room_template_items_default_department_id_foreign` (`default_department_id`),
  CONSTRAINT `room_template_items_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `asset_categories` (`id`),
  CONSTRAINT `room_template_items_default_department_id_foreign` FOREIGN KEY (`default_department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `room_template_items_template_id_foreign` FOREIGN KEY (`template_id`) REFERENCES `room_asset_templates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `room_template_items`
--

LOCK TABLES `room_template_items` WRITE;
/*!40000 ALTER TABLE `room_template_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `room_template_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `room_types`
--

DROP TABLE IF EXISTS `room_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `room_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint(20) unsigned NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `bed_config` varchar(40) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `room_types_property_id_code_unique` (`property_id`,`code`),
  CONSTRAINT `room_types_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `room_types`
--

LOCK TABLES `room_types` WRITE;
/*!40000 ALTER TABLE `room_types` DISABLE KEYS */;
/*!40000 ALTER TABLE `room_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `scan_logs`
--

DROP TABLE IF EXISTS `scan_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `scan_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `asset_label_id` bigint(20) unsigned DEFAULT NULL,
  `asset_id` bigint(20) unsigned DEFAULT NULL,
  `result` varchar(20) NOT NULL,
  `channel` varchar(20) NOT NULL DEFAULT 'url',
  `ip_address` varchar(45) DEFAULT NULL,
  `scanned_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  KEY `scan_logs_user_id_foreign` (`user_id`),
  KEY `scan_logs_asset_label_id_foreign` (`asset_label_id`),
  KEY `scan_logs_asset_id_foreign` (`asset_id`),
  CONSTRAINT `scan_logs_asset_id_foreign` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE SET NULL,
  CONSTRAINT `scan_logs_asset_label_id_foreign` FOREIGN KEY (`asset_label_id`) REFERENCES `asset_labels` (`id`) ON DELETE SET NULL,
  CONSTRAINT `scan_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `scan_logs`
--

LOCK TABLES `scan_logs` WRITE;
/*!40000 ALTER TABLE `scan_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `scan_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sequences`
--

DROP TABLE IF EXISTS `sequences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sequences` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint(20) unsigned NOT NULL,
  `prefix` varchar(20) NOT NULL,
  `year` smallint(5) unsigned NOT NULL DEFAULT 0,
  `last_number` bigint(20) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sequences_property_id_prefix_year_unique` (`property_id`,`prefix`,`year`),
  CONSTRAINT `sequences_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sequences`
--

LOCK TABLES `sequences` WRITE;
/*!40000 ALTER TABLE `sequences` DISABLE KEYS */;
/*!40000 ALTER TABLE `sequences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_requests`
--

DROP TABLE IF EXISTS `service_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `service_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint(20) unsigned NOT NULL,
  `number` varchar(30) NOT NULL,
  `asset_id` bigint(20) unsigned DEFAULT NULL,
  `quantity_group_id` bigint(20) unsigned DEFAULT NULL,
  `location_id` bigint(20) unsigned DEFAULT NULL,
  `reporter_id` bigint(20) unsigned NOT NULL,
  `reporter_department_id` bigint(20) unsigned DEFAULT NULL,
  `reporter_contact` varchar(255) DEFAULT NULL,
  `category` varchar(30) NOT NULL,
  `symptom` text NOT NULL,
  `impact` varchar(30) DEFAULT NULL,
  `is_safety` tinyint(1) NOT NULL DEFAULT 0,
  `priority` varchar(4) NOT NULL DEFAULT 'P3',
  `status` varchar(30) NOT NULL DEFAULT 'submitted',
  `received_at` timestamp NOT NULL,
  `response_due_at` timestamp NULL DEFAULT NULL,
  `response_at` timestamp NULL DEFAULT NULL,
  `responded_by` bigint(20) unsigned DEFAULT NULL,
  `triaged_at` timestamp NULL DEFAULT NULL,
  `triaged_by` bigint(20) unsigned DEFAULT NULL,
  `status_reason` varchar(255) DEFAULT NULL,
  `duplicate_of_id` bigint(20) unsigned DEFAULT NULL,
  `room_block_proposed` tinyint(1) NOT NULL DEFAULT 0,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `client_event_uuid` char(36) DEFAULT NULL,
  `version` int(10) unsigned NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `service_requests_number_unique` (`number`),
  UNIQUE KEY `service_requests_reporter_id_client_event_uuid_unique` (`reporter_id`,`client_event_uuid`),
  KEY `service_requests_asset_id_foreign` (`asset_id`),
  KEY `service_requests_quantity_group_id_foreign` (`quantity_group_id`),
  KEY `service_requests_location_id_foreign` (`location_id`),
  KEY `service_requests_reporter_department_id_foreign` (`reporter_department_id`),
  KEY `service_requests_responded_by_foreign` (`responded_by`),
  KEY `service_requests_triaged_by_foreign` (`triaged_by`),
  KEY `service_requests_duplicate_of_id_foreign` (`duplicate_of_id`),
  KEY `service_requests_property_id_status_index` (`property_id`,`status`),
  CONSTRAINT `service_requests_asset_id_foreign` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE SET NULL,
  CONSTRAINT `service_requests_duplicate_of_id_foreign` FOREIGN KEY (`duplicate_of_id`) REFERENCES `service_requests` (`id`) ON DELETE SET NULL,
  CONSTRAINT `service_requests_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`),
  CONSTRAINT `service_requests_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`),
  CONSTRAINT `service_requests_quantity_group_id_foreign` FOREIGN KEY (`quantity_group_id`) REFERENCES `asset_quantity_groups` (`id`) ON DELETE SET NULL,
  CONSTRAINT `service_requests_reporter_department_id_foreign` FOREIGN KEY (`reporter_department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `service_requests_reporter_id_foreign` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`),
  CONSTRAINT `service_requests_responded_by_foreign` FOREIGN KEY (`responded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `service_requests_triaged_by_foreign` FOREIGN KEY (`triaged_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_requests`
--

LOCK TABLES `service_requests` WRITE;
/*!40000 ALTER TABLE `service_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `service_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sla_policies`
--

DROP TABLE IF EXISTS `sla_policies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sla_policies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint(20) unsigned NOT NULL,
  `priority` varchar(4) NOT NULL,
  `name` varchar(255) NOT NULL,
  `example` varchar(255) DEFAULT NULL,
  `response_minutes` int(10) unsigned NOT NULL,
  `recovery_minutes` int(10) unsigned DEFAULT NULL COMMENT 'null = target per kasus',
  `calendar` varchar(10) NOT NULL DEFAULT '24h',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sla_policies_property_id_priority_unique` (`property_id`,`priority`),
  CONSTRAINT `sla_policies_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sla_policies`
--

LOCK TABLES `sla_policies` WRITE;
/*!40000 ALTER TABLE `sla_policies` DISABLE KEYS */;
INSERT INTO `sla_policies` VALUES
(1,1,'P1','P1 Darurat','Bahaya listrik, dugaan kebocoran gas, kegagalan utilitas kritis',5,NULL,'24h','2026-11-02 03:49:43','2026-11-02 03:49:43'),
(2,1,'P2','P2 Mendesak','Kamar tidak dapat dijual, chiller gagal, gangguan acara aktif',15,120,'24h','2026-11-02 03:49:43','2026-11-02 03:49:43'),
(3,1,'P3','P3 Normal','Kerusakan dengan layanan alternatif yang aman',120,1440,'business','2026-11-02 03:49:43','2026-11-02 03:49:43'),
(4,1,'P4','P4 Terencana','Kosmetik atau penyempurnaan tanpa gangguan layanan',540,10080,'business','2026-11-02 03:49:43','2026-11-02 03:49:43');
/*!40000 ALTER TABLE `sla_policies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `slot_assignments`
--

DROP TABLE IF EXISTS `slot_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `slot_assignments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `slot_id` bigint(20) unsigned NOT NULL,
  `asset_id` bigint(20) unsigned DEFAULT NULL,
  `quantity_group_id` bigint(20) unsigned DEFAULT NULL,
  `assigned_at` timestamp NOT NULL,
  `released_at` timestamp NULL DEFAULT NULL,
  `assign_reason` varchar(30) NOT NULL DEFAULT 'generated',
  `release_reason` varchar(255) DEFAULT NULL,
  `generation_batch_id` bigint(20) unsigned DEFAULT NULL,
  `actor_id` bigint(20) unsigned DEFAULT NULL,
  `active_slot_key` bigint(20) unsigned DEFAULT NULL,
  `active_asset_key` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slot_assignments_active_slot_key_unique` (`active_slot_key`),
  UNIQUE KEY `slot_assignments_active_asset_key_unique` (`active_asset_key`),
  KEY `slot_assignments_slot_id_foreign` (`slot_id`),
  KEY `slot_assignments_asset_id_foreign` (`asset_id`),
  KEY `slot_assignments_quantity_group_id_foreign` (`quantity_group_id`),
  KEY `slot_assignments_actor_id_foreign` (`actor_id`),
  CONSTRAINT `slot_assignments_actor_id_foreign` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `slot_assignments_asset_id_foreign` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `slot_assignments_quantity_group_id_foreign` FOREIGN KEY (`quantity_group_id`) REFERENCES `asset_quantity_groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `slot_assignments_slot_id_foreign` FOREIGN KEY (`slot_id`) REFERENCES `room_asset_slots` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `slot_assignments`
--

LOCK TABLES `slot_assignments` WRITE;
/*!40000 ALTER TABLE `slot_assignments` DISABLE KEYS */;
/*!40000 ALTER TABLE `slot_assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `status_events`
--

DROP TABLE IF EXISTS `status_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `status_events` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `subject_type` varchar(255) NOT NULL,
  `subject_id` bigint(20) unsigned NOT NULL,
  `from_status` varchar(30) DEFAULT NULL,
  `to_status` varchar(30) NOT NULL,
  `action` varchar(40) NOT NULL,
  `reason` text DEFAULT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `actor_id` bigint(20) unsigned DEFAULT NULL,
  `occurred_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  KEY `status_events_subject_type_subject_id_index` (`subject_type`,`subject_id`),
  KEY `status_events_actor_id_foreign` (`actor_id`),
  CONSTRAINT `status_events_actor_id_foreign` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `status_events`
--

LOCK TABLES `status_events` WRITE;
/*!40000 ALTER TABLE `status_events` DISABLE KEYS */;
/*!40000 ALTER TABLE `status_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint(20) unsigned DEFAULT NULL,
  `department_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `job_title` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(40) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `must_change_password` tinyint(1) NOT NULL DEFAULT 0,
  `password_changed_at` timestamp NULL DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `app_authentication_secret` text DEFAULT NULL,
  `app_authentication_recovery_codes` text DEFAULT NULL,
  `labor_rate` decimal(14,2) DEFAULT NULL COMMENT 'tarif manajerial per jam (internal)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_property_id_foreign` (`property_id`),
  KEY `users_department_id_foreign` (`department_id`),
  CONSTRAINT `users_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(1,1,NULL,'Super Admin','Super Admin','admin@siaga.local',NULL,'2026-11-02 03:49:43','$2y$12$oS3xDJn4pqperIsF4nrJYe2z5TIBUFnbYBwEFYXtAu7wIgBlYnBpS',NULL,'2026-11-02 03:49:44','2026-11-02 03:49:44',1,1,NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vendors`
--

DROP TABLE IF EXISTS `vendors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vendors` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint(20) unsigned NOT NULL,
  `code` varchar(30) NOT NULL,
  `name` varchar(255) NOT NULL,
  `specialization` varchar(255) DEFAULT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `phone` varchar(40) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `service_hours` varchar(255) DEFAULT NULL,
  `sla_note` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vendors_property_id_code_unique` (`property_id`,`code`),
  CONSTRAINT `vendors_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vendors`
--

LOCK TABLES `vendors` WRITE;
/*!40000 ALTER TABLE `vendors` DISABLE KEYS */;
/*!40000 ALTER TABLE `vendors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wo_cost_lines`
--

DROP TABLE IF EXISTS `wo_cost_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `wo_cost_lines` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `work_order_id` bigint(20) unsigned NOT NULL,
  `type` varchar(20) NOT NULL,
  `description` varchar(255) NOT NULL,
  `quantity` decimal(14,3) NOT NULL DEFAULT 1.000,
  `unit_cost` decimal(18,2) NOT NULL DEFAULT 0.00,
  `amount` decimal(18,2) NOT NULL DEFAULT 0.00,
  `cost_status` varchar(12) NOT NULL DEFAULT 'provisional',
  `is_cash_out` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'false = alokasi biaya internal',
  `source_type` varchar(40) DEFAULT NULL,
  `source_id` bigint(20) unsigned DEFAULT NULL,
  `invoice_ref` varchar(255) DEFAULT NULL,
  `reverses_id` bigint(20) unsigned DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `finalized_by` bigint(20) unsigned DEFAULT NULL,
  `finalized_at` timestamp NULL DEFAULT NULL,
  `period` varchar(7) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `wo_cost_lines_source_type_source_id_unique` (`source_type`,`source_id`),
  KEY `wo_cost_lines_work_order_id_foreign` (`work_order_id`),
  KEY `wo_cost_lines_reverses_id_foreign` (`reverses_id`),
  KEY `wo_cost_lines_created_by_foreign` (`created_by`),
  KEY `wo_cost_lines_finalized_by_foreign` (`finalized_by`),
  CONSTRAINT `wo_cost_lines_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `wo_cost_lines_finalized_by_foreign` FOREIGN KEY (`finalized_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `wo_cost_lines_reverses_id_foreign` FOREIGN KEY (`reverses_id`) REFERENCES `wo_cost_lines` (`id`) ON DELETE SET NULL,
  CONSTRAINT `wo_cost_lines_work_order_id_foreign` FOREIGN KEY (`work_order_id`) REFERENCES `work_orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wo_cost_lines`
--

LOCK TABLES `wo_cost_lines` WRITE;
/*!40000 ALTER TABLE `wo_cost_lines` DISABLE KEYS */;
/*!40000 ALTER TABLE `wo_cost_lines` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wo_time_logs`
--

DROP TABLE IF EXISTS `wo_time_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `wo_time_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `work_order_id` bigint(20) unsigned NOT NULL,
  `technician_id` bigint(20) unsigned NOT NULL,
  `started_at` timestamp NOT NULL,
  `ended_at` timestamp NULL DEFAULT NULL,
  `approved_minutes` int(10) unsigned DEFAULT NULL,
  `rate_snapshot` decimal(14,2) DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `wo_time_logs_work_order_id_foreign` (`work_order_id`),
  KEY `wo_time_logs_technician_id_foreign` (`technician_id`),
  CONSTRAINT `wo_time_logs_technician_id_foreign` FOREIGN KEY (`technician_id`) REFERENCES `users` (`id`),
  CONSTRAINT `wo_time_logs_work_order_id_foreign` FOREIGN KEY (`work_order_id`) REFERENCES `work_orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wo_time_logs`
--

LOCK TABLES `wo_time_logs` WRITE;
/*!40000 ALTER TABLE `wo_time_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `wo_time_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `work_orders`
--

DROP TABLE IF EXISTS `work_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `work_orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint(20) unsigned NOT NULL,
  `number` varchar(30) NOT NULL,
  `type` varchar(20) NOT NULL DEFAULT 'corrective',
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `asset_id` bigint(20) unsigned DEFAULT NULL,
  `location_id` bigint(20) unsigned DEFAULT NULL,
  `priority` varchar(4) NOT NULL DEFAULT 'P3',
  `is_safety` tinyint(1) NOT NULL DEFAULT 0,
  `is_emergency` tinyint(1) NOT NULL DEFAULT 0,
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `received_at` timestamp NOT NULL COMMENT 'waktu laporan pertama diterima server',
  `response_due_at` timestamp NULL DEFAULT NULL,
  `due_at` timestamp NULL DEFAULT NULL COMMENT 'target pemulihan',
  `sla_calendar` varchar(10) NOT NULL DEFAULT '24h',
  `sla_cycle` int(10) unsigned NOT NULL DEFAULT 1 COMMENT 'reopen memulai siklus baru',
  `hold_minutes` int(10) unsigned NOT NULL DEFAULT 0,
  `hold_started_at` timestamp NULL DEFAULT NULL,
  `hold_reason` varchar(255) DEFAULT NULL,
  `estimated_cost` decimal(18,2) DEFAULT NULL,
  `requires_approval` tinyint(1) NOT NULL DEFAULT 0,
  `approved_by` bigint(20) unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `approval_note` varchar(255) DEFAULT NULL,
  `pic_id` bigint(20) unsigned DEFAULT NULL,
  `team_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`team_ids`)),
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `estimated_hours` decimal(6,2) DEFAULT NULL,
  `needs_vendor` tinyint(1) NOT NULL DEFAULT 0,
  `vendor_id` bigint(20) unsigned DEFAULT NULL,
  `warranty_decision` varchar(20) DEFAULT NULL,
  `permits` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'ptw, loto, hot_work, height, room_access' CHECK (json_valid(`permits`)),
  `room_access_needed` tinyint(1) NOT NULL DEFAULT 0,
  `started_at` timestamp NULL DEFAULT NULL,
  `failure_mode` varchar(255) DEFAULT NULL,
  `diagnosis` text DEFAULT NULL,
  `action_taken` text DEFAULT NULL,
  `function_test_result` varchar(12) DEFAULT NULL,
  `root_cause` text DEFAULT NULL,
  `follow_up` text DEFAULT NULL,
  `checklist` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`checklist`)),
  `evidence_exception_reason` varchar(255) DEFAULT NULL,
  `restored_at` timestamp NULL DEFAULT NULL COMMENT 'layanan kembali aman (termasuk solusi sementara)',
  `completed_at` timestamp NULL DEFAULT NULL,
  `completed_by` bigint(20) unsigned DEFAULT NULL,
  `cost_status` varchar(12) NOT NULL DEFAULT 'provisional',
  `no_cost_reason` varchar(255) DEFAULT NULL,
  `verified_by` bigint(20) unsigned DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `verification_note` varchar(255) DEFAULT NULL,
  `asset_status_after` varchar(20) DEFAULT NULL,
  `fo_confirmed` tinyint(1) DEFAULT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `closed_by` bigint(20) unsigned DEFAULT NULL,
  `reopen_count` int(10) unsigned NOT NULL DEFAULT 0,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `version` int(10) unsigned NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `work_orders_number_unique` (`number`),
  KEY `work_orders_location_id_foreign` (`location_id`),
  KEY `work_orders_approved_by_foreign` (`approved_by`),
  KEY `work_orders_pic_id_foreign` (`pic_id`),
  KEY `work_orders_vendor_id_foreign` (`vendor_id`),
  KEY `work_orders_completed_by_foreign` (`completed_by`),
  KEY `work_orders_verified_by_foreign` (`verified_by`),
  KEY `work_orders_closed_by_foreign` (`closed_by`),
  KEY `work_orders_created_by_foreign` (`created_by`),
  KEY `work_orders_property_id_status_index` (`property_id`,`status`),
  KEY `work_orders_asset_id_failure_mode_index` (`asset_id`,`failure_mode`),
  CONSTRAINT `work_orders_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `work_orders_asset_id_foreign` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE SET NULL,
  CONSTRAINT `work_orders_closed_by_foreign` FOREIGN KEY (`closed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `work_orders_completed_by_foreign` FOREIGN KEY (`completed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `work_orders_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `work_orders_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `work_orders_pic_id_foreign` FOREIGN KEY (`pic_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `work_orders_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`),
  CONSTRAINT `work_orders_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE SET NULL,
  CONSTRAINT `work_orders_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `work_orders`
--

LOCK TABLES `work_orders` WRITE;
/*!40000 ALTER TABLE `work_orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `work_orders` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed
