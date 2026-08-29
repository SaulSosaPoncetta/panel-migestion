-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema mydb
-- -----------------------------------------------------
-- -----------------------------------------------------
-- Schema panelmigestion
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema panelmigestion
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `panelmigestion` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci ;
USE `panelmigestion` ;

-- -----------------------------------------------------
-- Table `panelmigestion`.`cache`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `panelmigestion`.`cache` (
  `key` VARCHAR(255) NOT NULL,
  `value` MEDIUMTEXT NOT NULL,
  `expiration` BIGINT NOT NULL,
  PRIMARY KEY (`key`),
  INDEX `cache_expiration_index` (`expiration` ASC) VISIBLE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `panelmigestion`.`cache_locks`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `panelmigestion`.`cache_locks` (
  `key` VARCHAR(255) NOT NULL,
  `owner` VARCHAR(255) NOT NULL,
  `expiration` BIGINT NOT NULL,
  PRIMARY KEY (`key`),
  INDEX `cache_locks_expiration_index` (`expiration` ASC) VISIBLE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `panelmigestion`.`clientes`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `panelmigestion`.`clientes` (
  `id_cliente` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `telefono` VARCHAR(255) NULL DEFAULT NULL,
  `identificacion_fiscal` VARCHAR(255) NULL DEFAULT NULL,
  `activo` TINYINT(1) NOT NULL DEFAULT '1',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_cliente`),
  UNIQUE INDEX `clientes_email_unique` (`email` ASC) VISIBLE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `panelmigestion`.`sistemas_saas`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `panelmigestion`.`sistemas_saas` (
  `id_sistema` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL,
  `url_base` VARCHAR(255) NOT NULL,
  `webhook_url` VARCHAR(255) NULL DEFAULT NULL,
  `api_key` VARCHAR(255) NOT NULL,
  `webhook_secret` VARCHAR(255) NOT NULL,
  `activo` TINYINT(1) NOT NULL DEFAULT '1',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_sistema`),
  UNIQUE INDEX `sistemas_saas_slug_unique` (`slug` ASC) VISIBLE,
  UNIQUE INDEX `sistemas_saas_api_key_unique` (`api_key` ASC) VISIBLE)
ENGINE = InnoDB
AUTO_INCREMENT = 2
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `panelmigestion`.`cliente_sistemas`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `panelmigestion`.`cliente_sistemas` (
  `id_cliente_sistema` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_cliente` BIGINT UNSIGNED NOT NULL,
  `id_sistema` BIGINT UNSIGNED NOT NULL,
  `referencia_externa` VARCHAR(255) NULL DEFAULT NULL,
  `fecha_alta` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_cliente_sistema`),
  UNIQUE INDEX `cliente_sistemas_id_cliente_id_sistema_unique` (`id_cliente` ASC, `id_sistema` ASC) VISIBLE,
  INDEX `cliente_sistemas_id_sistema_foreign` (`id_sistema` ASC) VISIBLE,
  CONSTRAINT `cliente_sistemas_id_cliente_foreign`
    FOREIGN KEY (`id_cliente`)
    REFERENCES `panelmigestion`.`clientes` (`id_cliente`)
    ON DELETE CASCADE,
  CONSTRAINT `cliente_sistemas_id_sistema_foreign`
    FOREIGN KEY (`id_sistema`)
    REFERENCES `panelmigestion`.`sistemas_saas` (`id_sistema`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `panelmigestion`.`failed_jobs`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `panelmigestion`.`failed_jobs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` VARCHAR(255) NOT NULL,
  `connection` VARCHAR(255) NOT NULL,
  `queue` VARCHAR(255) NOT NULL,
  `payload` LONGTEXT NOT NULL,
  `exception` LONGTEXT NOT NULL,
  `failed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `failed_jobs_uuid_unique` (`uuid` ASC) VISIBLE,
  INDEX `failed_jobs_connection_queue_failed_at_index` (`connection` ASC, `queue` ASC, `failed_at` ASC) VISIBLE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `panelmigestion`.`job_batches`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `panelmigestion`.`job_batches` (
  `id` VARCHAR(255) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `total_jobs` INT NOT NULL,
  `pending_jobs` INT NOT NULL,
  `failed_jobs` INT NOT NULL,
  `failed_job_ids` LONGTEXT NOT NULL,
  `options` MEDIUMTEXT NULL DEFAULT NULL,
  `cancelled_at` INT NULL DEFAULT NULL,
  `created_at` INT NOT NULL,
  `finished_at` INT NULL DEFAULT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `panelmigestion`.`jobs`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `panelmigestion`.`jobs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue` VARCHAR(255) NOT NULL,
  `payload` LONGTEXT NOT NULL,
  `attempts` SMALLINT UNSIGNED NOT NULL,
  `reserved_at` INT UNSIGNED NULL DEFAULT NULL,
  `available_at` INT UNSIGNED NOT NULL,
  `created_at` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `jobs_queue_index` (`queue` ASC) VISIBLE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `panelmigestion`.`migrations`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `panelmigestion`.`migrations` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` VARCHAR(255) NOT NULL,
  `batch` INT NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 10
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `panelmigestion`.`permissions`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `panelmigestion`.`permissions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `guard_name` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `permissions_name_guard_name_unique` (`name` ASC, `guard_name` ASC) VISIBLE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `panelmigestion`.`model_has_permissions`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `panelmigestion`.`model_has_permissions` (
  `permission_id` BIGINT UNSIGNED NOT NULL,
  `model_type` VARCHAR(255) NOT NULL,
  `model_id` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`permission_id`, `model_id`, `model_type`),
  INDEX `model_has_permissions_model_id_model_type_index` (`model_id` ASC, `model_type` ASC) VISIBLE,
  CONSTRAINT `model_has_permissions_permission_id_foreign`
    FOREIGN KEY (`permission_id`)
    REFERENCES `panelmigestion`.`permissions` (`id`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `panelmigestion`.`roles`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `panelmigestion`.`roles` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `guard_name` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `roles_name_guard_name_unique` (`name` ASC, `guard_name` ASC) VISIBLE)
ENGINE = InnoDB
AUTO_INCREMENT = 2
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `panelmigestion`.`model_has_roles`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `panelmigestion`.`model_has_roles` (
  `role_id` BIGINT UNSIGNED NOT NULL,
  `model_type` VARCHAR(255) NOT NULL,
  `model_id` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`role_id`, `model_id`, `model_type`),
  INDEX `model_has_roles_model_id_model_type_index` (`model_id` ASC, `model_type` ASC) VISIBLE,
  CONSTRAINT `model_has_roles_role_id_foreign`
    FOREIGN KEY (`role_id`)
    REFERENCES `panelmigestion`.`roles` (`id`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `panelmigestion`.`suscripciones`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `panelmigestion`.`suscripciones` (
  `id_suscripcion` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_cliente_sistema` BIGINT UNSIGNED NOT NULL,
  `plan` VARCHAR(255) NULL DEFAULT NULL,
  `tipo` ENUM('recurrente', 'unico') NOT NULL,
  `monto` DECIMAL(12,2) NOT NULL,
  `moneda` VARCHAR(3) NOT NULL DEFAULT 'ARS',
  `estado` ENUM('pendiente', 'activa', 'vencida', 'suspendida', 'cancelada') NOT NULL DEFAULT 'pendiente',
  `fecha_inicio` DATE NULL DEFAULT NULL,
  `proxima_fecha_cobro` DATE NULL DEFAULT NULL,
  `mp_preapproval_id` VARCHAR(255) NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_suscripcion`),
  INDEX `suscripciones_id_cliente_sistema_foreign` (`id_cliente_sistema` ASC) VISIBLE,
  CONSTRAINT `suscripciones_id_cliente_sistema_foreign`
    FOREIGN KEY (`id_cliente_sistema`)
    REFERENCES `panelmigestion`.`cliente_sistemas` (`id_cliente_sistema`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `panelmigestion`.`pagos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `panelmigestion`.`pagos` (
  `id_pago` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_suscripcion` BIGINT UNSIGNED NOT NULL,
  `medio_pago` VARCHAR(255) NOT NULL DEFAULT 'mercadopago',
  `mp_payment_id` VARCHAR(255) NULL DEFAULT NULL,
  `mp_preference_id` VARCHAR(255) NULL DEFAULT NULL,
  `monto` DECIMAL(12,2) NOT NULL,
  `moneda` VARCHAR(3) NOT NULL DEFAULT 'ARS',
  `estado` ENUM('pendiente', 'aprobado', 'rechazado', 'reembolsado') NOT NULL DEFAULT 'pendiente',
  `fecha_pago` TIMESTAMP NULL DEFAULT NULL,
  `payload_raw` JSON NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_pago`),
  INDEX `pagos_id_suscripcion_foreign` (`id_suscripcion` ASC) VISIBLE,
  INDEX `pagos_mp_payment_id_index` (`mp_payment_id` ASC) VISIBLE,
  CONSTRAINT `pagos_id_suscripcion_foreign`
    FOREIGN KEY (`id_suscripcion`)
    REFERENCES `panelmigestion`.`suscripciones` (`id_suscripcion`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `panelmigestion`.`password_reset_tokens`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `panelmigestion`.`password_reset_tokens` (
  `email` VARCHAR(255) NOT NULL,
  `token` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`email`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `panelmigestion`.`role_has_permissions`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `panelmigestion`.`role_has_permissions` (
  `permission_id` BIGINT UNSIGNED NOT NULL,
  `role_id` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`permission_id`, `role_id`),
  INDEX `role_has_permissions_role_id_foreign` (`role_id` ASC) VISIBLE,
  CONSTRAINT `role_has_permissions_permission_id_foreign`
    FOREIGN KEY (`permission_id`)
    REFERENCES `panelmigestion`.`permissions` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign`
    FOREIGN KEY (`role_id`)
    REFERENCES `panelmigestion`.`roles` (`id`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `panelmigestion`.`sessions`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `panelmigestion`.`sessions` (
  `id` VARCHAR(255) NOT NULL,
  `user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `ip_address` VARCHAR(45) NULL DEFAULT NULL,
  `user_agent` TEXT NULL DEFAULT NULL,
  `payload` LONGTEXT NOT NULL,
  `last_activity` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `sessions_user_id_index` (`user_id` ASC) VISIBLE,
  INDEX `sessions_last_activity_index` (`last_activity` ASC) VISIBLE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `panelmigestion`.`users`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `panelmigestion`.`users` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `email_verified_at` TIMESTAMP NULL DEFAULT NULL,
  `password` VARCHAR(255) NOT NULL,
  `remember_token` VARCHAR(100) NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `users_email_unique` (`email` ASC) VISIBLE)
ENGINE = InnoDB
AUTO_INCREMENT = 4
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
