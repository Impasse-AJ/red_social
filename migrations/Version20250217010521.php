<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250217010521 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE amistad MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE amistad DROP FOREIGN KEY amistad_ibfk_2');
        $this->addSql('ALTER TABLE amistad DROP FOREIGN KEY amistad_ibfk_1');
        $this->addSql('DROP INDEX usuarioB_id ON amistad');
        $this->addSql('DROP INDEX usuarioA_id ON amistad');
        $this->addSql('DROP INDEX `primary` ON amistad');
        $this->addSql('ALTER TABLE amistad ADD usuario_a_id INT NOT NULL, ADD usuario_b_id INT NOT NULL, DROP id, DROP usuarioA_id, DROP usuarioB_id, CHANGE aceptada aceptada TINYINT(1) NOT NULL, CHANGE fecha_solicitud fecha_solicitud DATETIME NOT NULL');
        $this->addSql('ALTER TABLE amistad ADD CONSTRAINT FK_8CAEA1CE52A42948 FOREIGN KEY (usuario_a_id) REFERENCES usuarios (id)');
        $this->addSql('ALTER TABLE amistad ADD CONSTRAINT FK_8CAEA1CE401186A6 FOREIGN KEY (usuario_b_id) REFERENCES usuarios (id)');
        $this->addSql('CREATE INDEX IDX_8CAEA1CE52A42948 ON amistad (usuario_a_id)');
        $this->addSql('CREATE INDEX IDX_8CAEA1CE401186A6 ON amistad (usuario_b_id)');
        $this->addSql('ALTER TABLE amistad ADD PRIMARY KEY (usuario_a_id, usuario_b_id)');
        $this->addSql('ALTER TABLE comentarios DROP FOREIGN KEY comentarios_ibfk_1');
        $this->addSql('ALTER TABLE comentarios DROP FOREIGN KEY comentarios_ibfk_2');
        $this->addSql('ALTER TABLE comentarios CHANGE id_publicacion id_publicacion INT DEFAULT NULL, CHANGE id_usuario id_usuario INT DEFAULT NULL, CHANGE contenido contenido LONGTEXT NOT NULL, CHANGE fecha_creacion fecha_creacion DATETIME NOT NULL');
        $this->addSql('DROP INDEX id_publicacion ON comentarios');
        $this->addSql('CREATE INDEX IDX_F54B3FC07AE0C5A8 ON comentarios (id_publicacion)');
        $this->addSql('DROP INDEX id_usuario ON comentarios');
        $this->addSql('CREATE INDEX IDX_F54B3FC0FCF8192D ON comentarios (id_usuario)');
        $this->addSql('ALTER TABLE comentarios ADD CONSTRAINT comentarios_ibfk_1 FOREIGN KEY (id_publicacion) REFERENCES publicaciones (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE comentarios ADD CONSTRAINT comentarios_ibfk_2 FOREIGN KEY (id_usuario) REFERENCES usuarios (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE publicaciones DROP FOREIGN KEY publicaciones_ibfk_1');
        $this->addSql('ALTER TABLE publicaciones CHANGE id_usuario id_usuario INT DEFAULT NULL, CHANGE contenido contenido LONGTEXT NOT NULL, CHANGE fecha_creacion fecha_creacion DATETIME NOT NULL');
        $this->addSql('DROP INDEX id_usuario ON publicaciones');
        $this->addSql('CREATE INDEX IDX_A3A706C0FCF8192D ON publicaciones (id_usuario)');
        $this->addSql('ALTER TABLE publicaciones ADD CONSTRAINT publicaciones_ibfk_1 FOREIGN KEY (id_usuario) REFERENCES usuarios (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reacciones DROP FOREIGN KEY reacciones_ibfk_1');
        $this->addSql('ALTER TABLE reacciones DROP FOREIGN KEY reacciones_ibfk_2');
        $this->addSql('ALTER TABLE reacciones CHANGE id_publicacion id_publicacion INT DEFAULT NULL, CHANGE id_usuario id_usuario INT DEFAULT NULL');
        $this->addSql('DROP INDEX id_publicacion ON reacciones');
        $this->addSql('CREATE INDEX IDX_4B15D9B67AE0C5A8 ON reacciones (id_publicacion)');
        $this->addSql('DROP INDEX id_usuario ON reacciones');
        $this->addSql('CREATE INDEX IDX_4B15D9B6FCF8192D ON reacciones (id_usuario)');
        $this->addSql('ALTER TABLE reacciones ADD CONSTRAINT reacciones_ibfk_1 FOREIGN KEY (id_publicacion) REFERENCES publicaciones (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reacciones ADD CONSTRAINT reacciones_ibfk_2 FOREIGN KEY (id_usuario) REFERENCES usuarios (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE usuarios CHANGE nombre_usuario nombre_usuario VARCHAR(255) NOT NULL, CHANGE foto_perfil foto_perfil VARCHAR(255) DEFAULT NULL, CHANGE activo activo TINYINT(1) NOT NULL');
        $this->addSql('DROP INDEX email ON usuarios');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EF687F2E7927C74 ON usuarios (email)');
        $this->addSql('DROP INDEX nombre_usuario ON usuarios');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EF687F2D67CF11D ON usuarios (nombre_usuario)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE amistad DROP FOREIGN KEY FK_8CAEA1CE52A42948');
        $this->addSql('ALTER TABLE amistad DROP FOREIGN KEY FK_8CAEA1CE401186A6');
        $this->addSql('DROP INDEX IDX_8CAEA1CE52A42948 ON amistad');
        $this->addSql('DROP INDEX IDX_8CAEA1CE401186A6 ON amistad');
        $this->addSql('ALTER TABLE amistad ADD id INT AUTO_INCREMENT NOT NULL, ADD usuarioA_id INT NOT NULL, ADD usuarioB_id INT NOT NULL, DROP usuario_a_id, DROP usuario_b_id, CHANGE aceptada aceptada TINYINT(1) DEFAULT 0, CHANGE fecha_solicitud fecha_solicitud DATETIME DEFAULT CURRENT_TIMESTAMP, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE amistad ADD CONSTRAINT amistad_ibfk_2 FOREIGN KEY (usuarioB_id) REFERENCES usuarios (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE amistad ADD CONSTRAINT amistad_ibfk_1 FOREIGN KEY (usuarioA_id) REFERENCES usuarios (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX usuarioB_id ON amistad (usuarioB_id)');
        $this->addSql('CREATE INDEX usuarioA_id ON amistad (usuarioA_id)');
        $this->addSql('ALTER TABLE comentarios DROP FOREIGN KEY FK_F54B3FC07AE0C5A8');
        $this->addSql('ALTER TABLE comentarios DROP FOREIGN KEY FK_F54B3FC0FCF8192D');
        $this->addSql('ALTER TABLE comentarios CHANGE id_publicacion id_publicacion INT NOT NULL, CHANGE id_usuario id_usuario INT NOT NULL, CHANGE contenido contenido TEXT NOT NULL, CHANGE fecha_creacion fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('DROP INDEX idx_f54b3fc0fcf8192d ON comentarios');
        $this->addSql('CREATE INDEX id_usuario ON comentarios (id_usuario)');
        $this->addSql('DROP INDEX idx_f54b3fc07ae0c5a8 ON comentarios');
        $this->addSql('CREATE INDEX id_publicacion ON comentarios (id_publicacion)');
        $this->addSql('ALTER TABLE comentarios ADD CONSTRAINT FK_F54B3FC07AE0C5A8 FOREIGN KEY (id_publicacion) REFERENCES publicaciones (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE comentarios ADD CONSTRAINT FK_F54B3FC0FCF8192D FOREIGN KEY (id_usuario) REFERENCES usuarios (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE publicaciones DROP FOREIGN KEY FK_A3A706C0FCF8192D');
        $this->addSql('ALTER TABLE publicaciones CHANGE id_usuario id_usuario INT NOT NULL, CHANGE contenido contenido VARCHAR(255) NOT NULL, CHANGE fecha_creacion fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('DROP INDEX idx_a3a706c0fcf8192d ON publicaciones');
        $this->addSql('CREATE INDEX id_usuario ON publicaciones (id_usuario)');
        $this->addSql('ALTER TABLE publicaciones ADD CONSTRAINT FK_A3A706C0FCF8192D FOREIGN KEY (id_usuario) REFERENCES usuarios (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reacciones DROP FOREIGN KEY FK_4B15D9B67AE0C5A8');
        $this->addSql('ALTER TABLE reacciones DROP FOREIGN KEY FK_4B15D9B6FCF8192D');
        $this->addSql('ALTER TABLE reacciones CHANGE id_publicacion id_publicacion INT NOT NULL, CHANGE id_usuario id_usuario INT NOT NULL');
        $this->addSql('DROP INDEX idx_4b15d9b67ae0c5a8 ON reacciones');
        $this->addSql('CREATE INDEX id_publicacion ON reacciones (id_publicacion)');
        $this->addSql('DROP INDEX idx_4b15d9b6fcf8192d ON reacciones');
        $this->addSql('CREATE INDEX id_usuario ON reacciones (id_usuario)');
        $this->addSql('ALTER TABLE reacciones ADD CONSTRAINT FK_4B15D9B67AE0C5A8 FOREIGN KEY (id_publicacion) REFERENCES publicaciones (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reacciones ADD CONSTRAINT FK_4B15D9B6FCF8192D FOREIGN KEY (id_usuario) REFERENCES usuarios (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE usuarios CHANGE nombre_usuario nombre_usuario VARCHAR(50) NOT NULL, CHANGE foto_perfil foto_perfil VARCHAR(255) NOT NULL, CHANGE activo activo TINYINT(1) DEFAULT 0');
        $this->addSql('DROP INDEX uniq_ef687f2e7927c74 ON usuarios');
        $this->addSql('CREATE UNIQUE INDEX email ON usuarios (email)');
        $this->addSql('DROP INDEX uniq_ef687f2d67cf11d ON usuarios');
        $this->addSql('CREATE UNIQUE INDEX nombre_usuario ON usuarios (nombre_usuario)');
    }
}
