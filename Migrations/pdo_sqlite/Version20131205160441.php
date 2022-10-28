<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/12/05 04:04:44
 */
class Version20131205160441 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_api_client (
                id INTEGER NOT NULL,
                random_id VARCHAR(255) NOT NULL,
                redirect_uris CLOB NOT NULL,
                secret VARCHAR(255) NOT NULL,
                allowed_grant_types CLOB NOT NULL,
                name VARCHAR(255) NOT NULL,
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_api_access_token (
                id INTEGER NOT NULL,
                client_id INTEGER NOT NULL,
                user_id INTEGER DEFAULT NULL,
                token VARCHAR(255) NOT NULL,
                expires_at INTEGER DEFAULT NULL,
                scope VARCHAR(255) DEFAULT NULL,
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_CE948285F37A13B ON claro_api_access_token (token)
        ");
        $this->addSql("
            CREATE INDEX IDX_CE9482819EB6921 ON claro_api_access_token (client_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_CE94828A76ED395 ON claro_api_access_token (user_id)
        ");
        $this->addSql("
            CREATE TABLE claro_api_refresh_token (
                id INTEGER NOT NULL,
                client_id INTEGER NOT NULL,
                user_id INTEGER DEFAULT NULL,
                token VARCHAR(255) NOT NULL,
                expires_at INTEGER DEFAULT NULL,
                scope VARCHAR(255) DEFAULT NULL,
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_B1292B905F37A13B ON claro_api_refresh_token (token)
        ");
        $this->addSql("
            CREATE INDEX IDX_B1292B9019EB6921 ON claro_api_refresh_token (client_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_B1292B90A76ED395 ON claro_api_refresh_token (user_id)
        ");
        $this->addSql("
            CREATE TABLE claro_api_auth_code (
                id INTEGER NOT NULL,
                client_id INTEGER NOT NULL,
                user_id INTEGER DEFAULT NULL,
                token VARCHAR(255) NOT NULL,
                redirect_uri CLOB NOT NULL,
                expires_at INTEGER DEFAULT NULL,
                scope VARCHAR(255) DEFAULT NULL,
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_9DFA4575F37A13B ON claro_api_auth_code (token)
        ");
        $this->addSql("
            CREATE INDEX IDX_9DFA45719EB6921 ON claro_api_auth_code (client_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_9DFA457A76ED395 ON claro_api_auth_code (user_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_api_client
        ");
        $this->addSql("
            DROP TABLE claro_api_access_token
        ");
        $this->addSql("
            DROP TABLE claro_api_refresh_token
        ");
        $this->addSql("
            DROP TABLE claro_api_auth_code
        ");
    }
}
