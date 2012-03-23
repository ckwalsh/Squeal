<?php
class SquealSQLiteInfoListener extends SquealInfoListener {

  private $dbFile;

  public function __construct($db_file) {
    parent::__construct();
    $this->dbFile = $db_file;
    $db = new SQLite3();
    $db->open($this->dbFile);
    $db->exec('CREATE TABLE IF NOT EXISTS squeal_info (key TEXT PRIMARY KEY, value TEXT)');
    $db->close();
  }

  protected function get($key) {
    $key = strtolower($key);
    $db = new SQLite3();
    $db->open($this->dbFile);
    $query = $db->prepare('SELECT value FROM squeal_info WHERE key=? LIMIT 1');
    $query->bindValue(1, $key, SQLITE3_TEXT);
    $result = $query->execute();
    $row = $result->fetchArray(SQLITE3_NUM);
    $result->finalize();
    $query->close();
    $db->close();
    if ($row) {
      return $row[0];
    } else {
      return null;
    }
  }

  protected function set($key, $value) {
    $db = new SQLite3();
    $db->open($this->dbFile);
    $query = $db->prepare('REPLACE INTO squeal_info VALUES ( ? , ? )');
    $query->bindValue(1, $key, SQLITE3_TEXT);
    $query->bindValue(2, $value, SQLITE3_TEXT);
    $query->execute();
    $query->close();
    $db->close();
  }

  protected function remove($key) {
    $db = new SQLite3();
    $db->open($this->dbFile);
    $query = $db->prepare('DELETE FROM squeal_info WHERE key=?');
    $query->bindValue(1, $key, SQLITE3_TEXT);
    $query->execute();
    $query->close();
    $db->close();
  }

  protected function getKeys() {
    $db = new SQLite3();
    $db->open($this->dbFile);
    $query = $db->prepare('SELECT key FROM squeal_info');
    $keys = array();
    $result = $query->execute();
    while ($row = $result->fetchArray(SQLITE3_NUM)) {
      $keys[] = $row[0];
    }
    $result->finalize();
    $query->close();
    $db->close();
  }
}
