<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260708104514 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE amistades (id INT AUTO_INCREMENT NOT NULL, id_solicitante INT NOT NULL, id_receptor INT NOT NULL, estado VARCHAR(10) NOT NULL, INDEX IDX_64F7488B6FE5CFB8 (id_solicitante), INDEX IDX_64F7488BB91944F2 (id_receptor), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE comentarios (id INT AUTO_INCREMENT NOT NULL, id_publicacion INT DEFAULT NULL, id_usuario INT DEFAULT NULL, contenido LONGTEXT NOT NULL, fecha_creacion DATETIME NOT NULL, INDEX IDX_F54B3FC07AE0C5A8 (id_publicacion), INDEX IDX_F54B3FC0FCF8192D (id_usuario), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE publicaciones (id INT AUTO_INCREMENT NOT NULL, id_usuario INT DEFAULT NULL, contenido LONGTEXT NOT NULL, fecha_creacion DATETIME NOT NULL, INDEX IDX_A3A706C0FCF8192D (id_usuario), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE usuarios (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, nombre_usuario VARCHAR(255) NOT NULL, contrasena VARCHAR(255) NOT NULL, foto_perfil VARCHAR(255) DEFAULT NULL, fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, codigo_recuperacion VARCHAR(255) DEFAULT NULL, codigo_recuperacion_expira DATETIME DEFAULT NULL, token_activacion VARCHAR(64) DEFAULT NULL, activo TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_EF687F2E7927C74 (email), UNIQUE INDEX UNIQ_EF687F2D67CF11D (nombre_usuario), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE amistades ADD CONSTRAINT FK_64F7488B6FE5CFB8 FOREIGN KEY (id_solicitante) REFERENCES usuarios (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE amistades ADD CONSTRAINT FK_64F7488BB91944F2 FOREIGN KEY (id_receptor) REFERENCES usuarios (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE comentarios ADD CONSTRAINT FK_F54B3FC07AE0C5A8 FOREIGN KEY (id_publicacion) REFERENCES publicaciones (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE comentarios ADD CONSTRAINT FK_F54B3FC0FCF8192D FOREIGN KEY (id_usuario) REFERENCES usuarios (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE publicaciones ADD CONSTRAINT FK_A3A706C0FCF8192D FOREIGN KEY (id_usuario) REFERENCES usuarios (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE amistades DROP FOREIGN KEY FK_64F7488B6FE5CFB8');
        $this->addSql('ALTER TABLE amistades DROP FOREIGN KEY FK_64F7488BB91944F2');
        $this->addSql('ALTER TABLE comentarios DROP FOREIGN KEY FK_F54B3FC07AE0C5A8');
        $this->addSql('ALTER TABLE comentarios DROP FOREIGN KEY FK_F54B3FC0FCF8192D');
        $this->addSql('ALTER TABLE publicaciones DROP FOREIGN KEY FK_A3A706C0FCF8192D');
        $this->addSql('DROP TABLE amistades');
        $this->addSql('DROP TABLE comentarios');
        $this->addSql('DROP TABLE publicaciones');
        $this->addSql('DROP TABLE usuarios');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
