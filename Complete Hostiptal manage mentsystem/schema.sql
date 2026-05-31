-- ============================================================
--  MediCore HMS — Database Schema
--  Database: hosss
--  Charset:  utf8mb4 (full Unicode + emoji support)
-- ============================================================

CREATE DATABASE IF NOT EXISTS `hosss`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `hosss`;

-- ── PATIENTS ──────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `patients` (
    `patientID`  INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `firstName`  VARCHAR(100) NOT NULL,
    `lastName`   VARCHAR(100) NOT NULL,
    `dob`        DATE         NOT NULL,
    `gender`     ENUM('Male','Female','Other') NOT NULL,
    `address`    TEXT,
    `phone1`     VARCHAR(20)  NOT NULL,
    `phone2`     VARCHAR(20),
    `email`      VARCHAR(150) NOT NULL,
    `createdAt`  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_patient_email` (`email`),
    INDEX `idx_patient_name`  (`lastName`, `firstName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── DOCTORS ───────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `doctors` (
    `doctorID`        INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `firstName`       VARCHAR(100) NOT NULL,
    `lastName`        VARCHAR(100) NOT NULL,
    `specialty`       VARCHAR(150) NOT NULL,
    `phone1`          VARCHAR(20),
    `phone2`          VARCHAR(20),
    `email`           VARCHAR(150),
    `experienceYears` INT          DEFAULT 0,
    `createdAt`       TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_doctor_specialty` (`specialty`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── NURSES ────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `nurses` (
    `nurseID`    INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `firstName`  VARCHAR(100) NOT NULL,
    `lastName`   VARCHAR(100) NOT NULL,
    `department` VARCHAR(150),
    `phone1`     VARCHAR(20),
    `phone2`     VARCHAR(20),
    `email`      VARCHAR(150),
    `createdAt`  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── STAFF ─────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `staff` (
    `staffID`    INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `firstName`  VARCHAR(100) NOT NULL,
    `lastName`   VARCHAR(100) NOT NULL,
    `department` VARCHAR(150),
    `phone1`     VARCHAR(20),
    `phone2`     VARCHAR(20),
    `email`      VARCHAR(150),
    `createdAt`  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── DEPARTMENTS ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `departments` (
    `departmentID`   INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `departmentName` VARCHAR(255) NOT NULL,
    `location`       VARCHAR(255),
    `createdAt`      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── ROOMS ─────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `rooms` (
    `roomID`             INT         NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `roomNumber`         VARCHAR(20) NOT NULL UNIQUE,
    `roomType`           VARCHAR(100),
    `capacity`           INT         DEFAULT 1,
    `availabilityStatus` ENUM('Available','Occupied','Under Maintenance') DEFAULT 'Available',
    `createdAt`          TIMESTAMP   DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── APPOINTMENTS ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `appointments` (
    `appointmentID`   INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `appointmentDate` DATE         NOT NULL,
    `reason`          TEXT,
    `status`          ENUM('Scheduled','Completed','Cancelled') DEFAULT 'Scheduled',
    `patientID`       INT          NOT NULL,
    `doctorID`        INT          NOT NULL,
    `createdAt`       TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`patientID`) REFERENCES `patients`(`patientID`) ON DELETE CASCADE,
    FOREIGN KEY (`doctorID`)  REFERENCES `doctors`(`doctorID`)  ON DELETE CASCADE,
    INDEX `idx_appt_date`      (`appointmentDate`),
    INDEX `idx_appt_patient`   (`patientID`),
    INDEX `idx_appt_doctor`    (`doctorID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── TREATMENTS ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `treatments` (
    `treatmentID`   INT           NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `treatmentDate` DATE          NOT NULL,
    `description`   TEXT,
    `cost`          DECIMAL(10,2) DEFAULT 0.00,
    `patientID`     INT           NOT NULL,
    `createdAt`     TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`patientID`) REFERENCES `patients`(`patientID`) ON DELETE CASCADE,
    INDEX `idx_treatment_patient` (`patientID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── PRESCRIPTIONS ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `prescriptions` (
    `prescriptionID`   INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `prescriptionDate` DATE         NOT NULL,
    `medication`       VARCHAR(255) NOT NULL,
    `dosage`           VARCHAR(150),
    `patientID`        INT          NOT NULL,
    `doctorID`         INT          NOT NULL,
    `createdAt`        TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`patientID`) REFERENCES `patients`(`patientID`) ON DELETE CASCADE,
    FOREIGN KEY (`doctorID`)  REFERENCES `doctors`(`doctorID`)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── BILLING ───────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `billing` (
    `billingID`     INT           NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `patientID`     INT           NOT NULL,
    `amount`        DECIMAL(10,2) NOT NULL,
    `billingDate`   DATE          NOT NULL,
    `paymentStatus` ENUM('Pending','Paid','Partial','Overdue') DEFAULT 'Pending',
    `createdAt`     TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`patientID`) REFERENCES `patients`(`patientID`) ON DELETE CASCADE,
    INDEX `idx_billing_patient` (`patientID`),
    INDEX `idx_billing_status`  (`paymentStatus`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── INSURANCE ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `insurance` (
    `insuranceID`     INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `patientID`       INT          NOT NULL,
    `providerName`    VARCHAR(255) NOT NULL,
    `policyNumber`    VARCHAR(100) NOT NULL,
    `coverageDetails` TEXT,
    `createdAt`       TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`patientID`) REFERENCES `patients`(`patientID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── LAB TESTS ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `labtests` (
    `testID`     INT       NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `testDate`   DATE      NOT NULL,
    `testResult` TEXT,
    `patientID`  INT       NOT NULL,
    `createdAt`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`patientID`) REFERENCES `patients`(`patientID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── MEDICATIONS ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `medications` (
    `medicationID`   INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `medicationName` VARCHAR(255) NOT NULL,
    `dosage`         VARCHAR(150),
    `patientID`      INT          NOT NULL,
    `createdAt`      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`patientID`) REFERENCES `patients`(`patientID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── SURGERIES ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `surgeries` (
    `surgeryID`   INT       NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `surgeryDate` DATE      NOT NULL,
    `description` TEXT,
    `patientID`   INT       NOT NULL,
    `createdAt`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`patientID`) REFERENCES `patients`(`patientID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── FEEDBACK ──────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `feedback` (
    `feedbackID`   INT       NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `feedbackText` TEXT      NOT NULL,
    `patientID`    INT       NOT NULL,
    `createdAt`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`patientID`) REFERENCES `patients`(`patientID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  Schema complete. All tables ready.
-- ============================================================
