<?php

class Database extends PDO {

  function Database() {
    parent::__construct(
      'mysql:host=' . DB_HOST .
      ';dbname=' . DB_NAME .
      ';charset=utf8',
      DB_USER,
      DB_PASS
      );
    $this->exec("SET CHARACTER SET utf8");
  }

};

