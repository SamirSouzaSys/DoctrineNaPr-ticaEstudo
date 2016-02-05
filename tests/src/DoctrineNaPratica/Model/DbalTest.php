<?php

namespace DoctrineNaPratica\Model;

use DoctrineNaPratica\Test\TestCase;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;

/**
* @group Model
*/
class DbalTest extends TestCase{

  private $conn;
  private $schema;

  public function setup(){
    $connectionParams = array(
                              'dbname' => 'sqlite:memory',
                              'driver' => 'pdo_sqlite',
                              );

//conectar na base
    $this->conn = DriverManager::getConnection($connectionParams);
//cria uma nova base de dados e uma tabela
    $this->schema = new Schema();
    $usersTable = $this->schema->createTable("users");
    $usersTable->addColumn("id", "integer", array("unsigned" => true));
    $usersTable->addColumn("name", "string", array("length" => 100));
    $usersTable->addColumn("login", "string", array("length" => 100));
    $usersTable->addColumn("email", "string", array("length" => 256));
    $usersTable->addColumn("avatar", "string", array("length" => 256));
    $usersTable->setPrimaryKey(array("id"));
//busca a plataforma de banco de dados configurada
    $platform = $this->conn->getDatabasePlatform();
    $queries = $this->schema->toSql($platform);
    foreach ($queries as $q) {
      $stmt = $this->conn->query($q);
    }
  }

  public function tearDown(){
    $this->schema->dropTable("users");
  }

//testa a consulta dos Users
  public function testUser()
  {
//inicia uma transação
    $this->conn->beginTransaction();
    $this->conn->insert('users', array(
                        'name' => 'Steve Jobs', 'login' => 'steve', 'email' => 'steve@apple.c\
                        om', 'avatar' => 'steve.png'
                        ));

    $this->conn->insert('users', array(
                        'name' => 'Bill Gates', 'login' => 'bill', 'email' => 'bill@microsoft\
                        .com', 'avatar' => 'bill.png'
                        ));
//commit da transação
    $this->conn->commit();

    $sql = "select * from users";
    $stmt = $this->conn->query($sql);
    $result = $stmt->fetchAll();
    $this->assertEquals(2, count($result));
    $this->assertEquals('steve', $result[0]['login']);
    $this->assertEquals('bill', $result[1]['login']);

  }

//testa a consulta dos Users com parametros
  public function testUserParameters()
  {
//inicia uma transação
    $this->conn->beginTransaction();
    $this->conn->insert('users', array(
                        'name' => 'Steve Jobs', 'login' => 'steve', 'email' => 'steve@apple.c\
                        om', 'avatar' => 'steve.png'
                        ));

    $this->conn->insert('users', array(
                        'name' => 'Bill Gates', 'login' => 'bill', 'email' => 'bill@microsoft\
                        .com', 'avatar' => 'bill.png'
                        ));
//commit da transação
    $this->conn->commit();

//usando placeholders
    $sql = 'SELECT * FROM users u WHERE u.login = ? or u.email = ?';
    $stmt = $this->conn->prepare($sql);
    $stmt->bindValue(1, 'steve');
    $stmt->bindValue(2, 'steve@apple.com');
    $stmt->execute();
    $result = $stmt->fetchAll();
    $this->assertEquals(1, count($result));
    $this->assertEquals('steve', $result[0]['login']);

//usando Named parameters
    $sql = 'SELECT * FROM users u WHERE u.login = :login or u.email = :email';
    $stmt = $this->conn->prepare($sql);
    $stmt->bindValue('login', 'steve');
    $stmt->bindValue('email', 'steve@apple.com');
    $stmt->execute();
    $result = $stmt->fetchAll();

    $this->assertEquals(1, count($result));
    $this->assertEquals('steve', $result[0]['login']);

  }

}