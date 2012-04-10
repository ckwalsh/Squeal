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

/**
 * During testing I had troubles with idle connections resulting in drops.
 * This class can be used to keep connections alive by pinging every 180
 * seconds.
 */
class SquealPingListener {
  const SECONDS_BETWEEN_PINGS = 180;

  public function onConnect($bot, $args) {
    $bot->scheduleCallback(
      array($this, 'ping'),
      microtime(true) + self::SECONDS_BETWEEN_PINGS
    );
  }

  public function ping($bot, $args = array()) {
    $bot->sendRawLine('PING ' . time());

    $bot->scheduleCallback(
      array($this, 'ping'),
      microtime(true) + self::SECONDS_BETWEEN_PINGS
    );
  }
}
