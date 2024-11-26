<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePostsTable extends Migration
{
    public function up()
    {
        // Define the 'posts' table schema
        $this->forge->addField([
            'id'          => [
                'type'           => 'SERIAL', // SERIAL is auto-incrementing in PostgreSQL
                'unsigned'       => true,
            ],
            'title'       => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'category'    => [  // Add the 'category' field
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,  // Allowing null if not provided
            ],
            'body'        => [
                'type' => 'TEXT',
            ],
            'image'       => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'created_at'  => [
                'type'    => 'TIMESTAMP',
                'null'    => false,
                'default' => null, // Remove default value here
            ],
            'updated_at'  => [
                'type'    => 'TIMESTAMP',
                'null'    => true,
            ],
        ]);
        $this->forge->addKey('id', true);  // Primary key on 'id'
        $this->forge->createTable('posts');

        // Execute custom SQL for setting DEFAULT CURRENT_TIMESTAMP
        $db = \Config\Database::connect();
        $db->query("ALTER TABLE posts ALTER COLUMN created_at SET DEFAULT CURRENT_TIMESTAMP");
    }

    public function down()
    {
        // Drop the 'posts' table if the migration is rolled back
        $this->forge->dropTable('posts');
    }
}
