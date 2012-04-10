<?php
/**
 * This file is part of SquealIrcBot.
 *
 * SquealIrcBot is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * SquealIrcBot is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with SquealIrcBot.  If not, see <http://www.gnu.org/licenses/>.
 */
class SquealSQLiteWatchListener extends SquealWatchListener {

  private $dbFile;

  public function __construct($db_file) {
    parent::__construct();
    $this->dbFile = $db_file;
    $db = new SQLite3();
    $db->open($this->dbFile);
    $db->exec('CREATE TABLE IF NOT EXISTS squeal_watch (key TEXT, nick TEXT)');
    $db->close();
  }

  protected function add($key, $nick) {
    $key = strtolower($key);
    $db = new SQLite3();
    $db->open($this->dbFile);
    $query = $db->prepare('SELECT nick FROM squeal_watch WHERE key=? AND nick=? LIMIT 1');
    $query->bindValue(1, $key, SQLITE3_TEXT);
    $query->bindValue(2, $key, SQLITE3_TEXT);
    $result = $query->execute();
    $row = $result->fetchArray(SQLITE3_NUM);
    $result->finalize();
    $query->close();
    if ($row) {
      // Do nothing
    } else {
      $query = $db->prepare('INSERT INTO squeal_watch VALUES ( ? , ? )');
      $query->bindValue(1, $key, SQLITE3_TEXT);
      $query->bindValue(2, $nick, SQLITE3_TEXT);
      $query->execute();
      $query->close();
    }

    $db->close();
  }

  protected function remove($key, $nick) {
    $db = new SQLite3();
    $db->open($this->dbFile);
    $query = $db->prepare('DELETE FROM squeal_watch WHERE key=? AND nick=?');
    $query->bindValue(1, $key, SQLITE3_TEXT);
    $query->bindValue(2, $nick, SQLITE3_TEXT);
    $query->execute();
    $query->close();
    $db->close();
  }

  protected function getNicks($key) {
    $key = strtolower($key);
    $db = new SQLite3();
    $db->open($this->dbFile);
    $query = $db->prepare('SELECT nick FROM squeal_watch WHERE key=?');
    $query->bindValue(1, $key, SQLITE3_TEXT);
    $result = $query->execute();
    $nicks = array();
    while ($row = $result->fetchArray(SQLITE3_NUM)) {
      $nicks[$row[0]] = $row[0];
    }
    $result->finalize();
    $query->close();
    $db->close();
    return $nicks;
  }
}
