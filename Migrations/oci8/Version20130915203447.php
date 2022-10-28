<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Migrations\oci8;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/15 08:34:48
 */
class Version20130915203447 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config
            DROP CONSTRAINT FK_D301C70782D40A1F
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config
            ADD CONSTRAINT FK_D301C70782D40A1F FOREIGN KEY (workspace_id)
            REFERENCES claro_workspace (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_widget_display
            DROP CONSTRAINT FK_2D34DB3A76ED395
        ");
        $this->addSql("
            ALTER TABLE claro_widget_display
            ADD CONSTRAINT FK_2D34DB3A76ED395 FOREIGN KEY (user_id)
            REFERENCES claro_user (id)
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config
            DROP CONSTRAINT FK_D301C70782D40A1F
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config
            ADD CONSTRAINT FK_D301C70782D40A1F FOREIGN KEY (workspace_id)
            REFERENCES claro_workspace (id)
        ");
        $this->addSql("
            ALTER TABLE claro_widget_display
            DROP CONSTRAINT FK_2D34DB3A76ED395
        ");
        $this->addSql("
            ALTER TABLE claro_widget_display
            ADD CONSTRAINT FK_2D34DB3A76ED395 FOREIGN KEY (user_id)
            REFERENCES claro_user (id)
        ");
    }
}
