<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260721223841 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE attendances (id INT AUTO_INCREMENT NOT NULL, student_id INT NOT NULL, date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', status VARCHAR(30) DEFAULT \'PRESENT\' NOT NULL, justification LONGTEXT DEFAULT NULL, justification_doc_path VARCHAR(255) DEFAULT NULL, INDEX IDX_9C6B8FD4CB944F1A (student_id), INDEX idx_attendances_student_date (student_id, date), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE audit_logs (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, action VARCHAR(100) NOT NULL, entity_class VARCHAR(255) DEFAULT NULL, entity_id INT DEFAULT NULL, old_values JSON DEFAULT NULL, new_values JSON DEFAULT NULL, ip_address VARCHAR(45) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_D62F2858A76ED395 (user_id), INDEX idx_audit_user_created (user_id, created_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE bulletins (id INT AUTO_INCREMENT NOT NULL, student_id INT NOT NULL, term VARCHAR(30) NOT NULL, general_average NUMERIC(4, 2) NOT NULL, teacher_appreciation LONGTEXT DEFAULT NULL, pdf_path VARCHAR(255) DEFAULT NULL, generated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_83F7923ECB944F1A (student_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cms_content (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(50) NOT NULL, translations JSON NOT NULL, image_path VARCHAR(255) DEFAULT NULL, published_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE enrollments (id INT AUTO_INCREMENT NOT NULL, student_id INT NOT NULL, status VARCHAR(50) DEFAULT \'PENDING\' NOT NULL, selected_day VARCHAR(20) DEFAULT NULL, selected_time_slot VARCHAR(50) DEFAULT NULL, includes_books TINYINT(1) DEFAULT 1 NOT NULL, optional_accessories JSON DEFAULT NULL, total_amount NUMERIC(10, 2) NOT NULL, discount_amount NUMERIC(10, 2) DEFAULT \'0.00\' NOT NULL, registration_fee NUMERIC(10, 2) DEFAULT \'50.00\' NOT NULL, structured_reference VARCHAR(35) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_CCD8C132DA3A1018 (structured_reference), INDEX IDX_CCD8C132CB944F1A (student_id), INDEX idx_enrollments_student_status (student_id, status), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE grades (id INT AUTO_INCREMENT NOT NULL, student_id INT NOT NULL, teacher_id INT NOT NULL, title VARCHAR(150) NOT NULL, score NUMERIC(5, 2) NOT NULL, max_score NUMERIC(5, 2) DEFAULT \'20.00\' NOT NULL, coefficient NUMERIC(3, 1) DEFAULT \'1.0\' NOT NULL, exam_date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', term VARCHAR(30) DEFAULT \'TRIMESTER_1\' NOT NULL, INDEX IDX_3AE36110CB944F1A (student_id), INDEX IDX_3AE3611041807E1D (teacher_id), INDEX idx_grades_student_date (student_id, exam_date), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE invoices (id INT AUTO_INCREMENT NOT NULL, payment_id INT NOT NULL, invoice_number VARCHAR(100) NOT NULL, issue_date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', pdf_path VARCHAR(255) DEFAULT NULL, total_excl_tax NUMERIC(10, 2) NOT NULL, tax_amount NUMERIC(10, 2) DEFAULT \'0.00\' NOT NULL, total_incl_tax NUMERIC(10, 2) NOT NULL, structured_bank_reference VARCHAR(35) DEFAULT NULL, UNIQUE INDEX UNIQ_6A2F2F952DA68207 (invoice_number), UNIQUE INDEX UNIQ_6A2F2F954C3A3BB (payment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE parents (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, full_name VARCHAR(255) NOT NULL, phone VARCHAR(50) NOT NULL, secondary_phone VARCHAR(50) DEFAULT NULL, address LONGTEXT DEFAULT NULL, iban VARCHAR(34) DEFAULT NULL, bic VARCHAR(11) DEFAULT NULL, sepa_mandate_ref VARCHAR(100) DEFAULT NULL, sepa_mandate_signature_date DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', preferred_payment_method VARCHAR(50) DEFAULT \'STRIPE\' NOT NULL, UNIQUE INDEX UNIQ_FD501D6AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE payment_schedules (id INT AUTO_INCREMENT NOT NULL, enrollment_id INT NOT NULL, title VARCHAR(100) NOT NULL, due_date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', amount NUMERIC(10, 2) NOT NULL, status VARCHAR(50) DEFAULT \'PENDING\' NOT NULL, payment_method VARCHAR(50) NOT NULL, INDEX IDX_7212F3A68F7DB25B (enrollment_id), INDEX idx_schedules_enrollment_status (enrollment_id, status, due_date), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE payments (id INT AUTO_INCREMENT NOT NULL, schedule_id INT DEFAULT NULL, enrollment_id INT NOT NULL, user_id INT NOT NULL, agent_user_id INT DEFAULT NULL, amount NUMERIC(10, 2) NOT NULL, currency VARCHAR(3) DEFAULT \'EUR\' NOT NULL, status VARCHAR(50) DEFAULT \'PENDING\' NOT NULL, payment_method VARCHAR(50) NOT NULL, transaction_reference VARCHAR(100) DEFAULT NULL, structured_reference_used VARCHAR(35) DEFAULT NULL, stripe_payment_intent_id VARCHAR(100) DEFAULT NULL, receipt_number VARCHAR(100) DEFAULT NULL, validated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_65D29B32A40BC2D5 (schedule_id), INDEX IDX_65D29B328F7DB25B (enrollment_id), INDEX IDX_65D29B32A76ED395 (user_id), INDEX IDX_65D29B32753DB13F (agent_user_id), INDEX idx_payments_user_status (user_id, status), INDEX idx_payments_stripe (stripe_payment_intent_id), INDEX idx_payments_created (created_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE students (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, parent_id INT NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, date_of_birth DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', place_of_birth VARCHAR(100) DEFAULT NULL, nationality VARCHAR(100) DEFAULT NULL, address LONGTEXT DEFAULT NULL, allergies LONGTEXT DEFAULT NULL, health_issues LONGTEXT DEFAULT NULL, family_notes LONGTEXT DEFAULT NULL, image_rights_granted TINYINT(1) DEFAULT 1 NOT NULL, gdpr_consent TINYINT(1) DEFAULT 1 NOT NULL, insurance_policy_number VARCHAR(100) DEFAULT NULL, insurance_doc_path VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_A4698DB2A76ED395 (user_id), INDEX idx_students_parent (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE teachers (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, full_name VARCHAR(100) NOT NULL, specialities JSON NOT NULL, bio LONGTEXT DEFAULT NULL, phone VARCHAR(50) DEFAULT NULL, UNIQUE INDEX UNIQ_ED071FF6A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, locale VARCHAR(5) DEFAULT \'fr\' NOT NULL, is2fa_enabled TINYINT(1) DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email), INDEX idx_users_email (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE attendances ADD CONSTRAINT FK_9C6B8FD4CB944F1A FOREIGN KEY (student_id) REFERENCES students (id)');
        $this->addSql('ALTER TABLE audit_logs ADD CONSTRAINT FK_D62F2858A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE bulletins ADD CONSTRAINT FK_83F7923ECB944F1A FOREIGN KEY (student_id) REFERENCES students (id)');
        $this->addSql('ALTER TABLE enrollments ADD CONSTRAINT FK_CCD8C132CB944F1A FOREIGN KEY (student_id) REFERENCES students (id)');
        $this->addSql('ALTER TABLE grades ADD CONSTRAINT FK_3AE36110CB944F1A FOREIGN KEY (student_id) REFERENCES students (id)');
        $this->addSql('ALTER TABLE grades ADD CONSTRAINT FK_3AE3611041807E1D FOREIGN KEY (teacher_id) REFERENCES teachers (id)');
        $this->addSql('ALTER TABLE invoices ADD CONSTRAINT FK_6A2F2F954C3A3BB FOREIGN KEY (payment_id) REFERENCES payments (id)');
        $this->addSql('ALTER TABLE parents ADD CONSTRAINT FK_FD501D6AA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE payment_schedules ADD CONSTRAINT FK_7212F3A68F7DB25B FOREIGN KEY (enrollment_id) REFERENCES enrollments (id)');
        $this->addSql('ALTER TABLE payments ADD CONSTRAINT FK_65D29B32A40BC2D5 FOREIGN KEY (schedule_id) REFERENCES payment_schedules (id)');
        $this->addSql('ALTER TABLE payments ADD CONSTRAINT FK_65D29B328F7DB25B FOREIGN KEY (enrollment_id) REFERENCES enrollments (id)');
        $this->addSql('ALTER TABLE payments ADD CONSTRAINT FK_65D29B32A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE payments ADD CONSTRAINT FK_65D29B32753DB13F FOREIGN KEY (agent_user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE students ADD CONSTRAINT FK_A4698DB2A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE students ADD CONSTRAINT FK_A4698DB2727ACA70 FOREIGN KEY (parent_id) REFERENCES parents (id)');
        $this->addSql('ALTER TABLE teachers ADD CONSTRAINT FK_ED071FF6A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE attendances DROP FOREIGN KEY FK_9C6B8FD4CB944F1A');
        $this->addSql('ALTER TABLE audit_logs DROP FOREIGN KEY FK_D62F2858A76ED395');
        $this->addSql('ALTER TABLE bulletins DROP FOREIGN KEY FK_83F7923ECB944F1A');
        $this->addSql('ALTER TABLE enrollments DROP FOREIGN KEY FK_CCD8C132CB944F1A');
        $this->addSql('ALTER TABLE grades DROP FOREIGN KEY FK_3AE36110CB944F1A');
        $this->addSql('ALTER TABLE grades DROP FOREIGN KEY FK_3AE3611041807E1D');
        $this->addSql('ALTER TABLE invoices DROP FOREIGN KEY FK_6A2F2F954C3A3BB');
        $this->addSql('ALTER TABLE parents DROP FOREIGN KEY FK_FD501D6AA76ED395');
        $this->addSql('ALTER TABLE payment_schedules DROP FOREIGN KEY FK_7212F3A68F7DB25B');
        $this->addSql('ALTER TABLE payments DROP FOREIGN KEY FK_65D29B32A40BC2D5');
        $this->addSql('ALTER TABLE payments DROP FOREIGN KEY FK_65D29B328F7DB25B');
        $this->addSql('ALTER TABLE payments DROP FOREIGN KEY FK_65D29B32A76ED395');
        $this->addSql('ALTER TABLE payments DROP FOREIGN KEY FK_65D29B32753DB13F');
        $this->addSql('ALTER TABLE students DROP FOREIGN KEY FK_A4698DB2A76ED395');
        $this->addSql('ALTER TABLE students DROP FOREIGN KEY FK_A4698DB2727ACA70');
        $this->addSql('ALTER TABLE teachers DROP FOREIGN KEY FK_ED071FF6A76ED395');
        $this->addSql('DROP TABLE attendances');
        $this->addSql('DROP TABLE audit_logs');
        $this->addSql('DROP TABLE bulletins');
        $this->addSql('DROP TABLE cms_content');
        $this->addSql('DROP TABLE enrollments');
        $this->addSql('DROP TABLE grades');
        $this->addSql('DROP TABLE invoices');
        $this->addSql('DROP TABLE parents');
        $this->addSql('DROP TABLE payment_schedules');
        $this->addSql('DROP TABLE payments');
        $this->addSql('DROP TABLE students');
        $this->addSql('DROP TABLE teachers');
        $this->addSql('DROP TABLE users');
    }
}
