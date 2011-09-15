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
 * This is a convenience class for easily implementing custom commands for your
 * IRC bot. Any methods defined by child classes of the form cmdName() are
 * executed by "botNick[:,-]? <name> <params>" or by the shortcut
 * "@<name> <params>". The prefix can be changed by overriding
 * getCommandPrefix(), and commands may be aliased using getCommandAliases().
 */
abstract class SquealCommandListener {

  public function getCommandPrefix() {
    return '@';
  }

  public function getCommandAliases() {
    return array();
  }

  public function onMessage($bot, $args) {
    return $this->handleMessage($bot, $args, false);
  }

  public function onPrivMessage($bot, $args) {
    return $this->handleMessage($bot, $args, true);
  }

  private function handleMessage($bot, $args, $private) {
    $message = $args['message'];

    list($command, $params) = $this->getCommandFromMessage(
      $bot,
      $message,
      $private
    );

    if ($command) {
      $command = strtolower($command);
      $aliases = $this->getCommandAliases();
      if (array_key_exists($command, $aliases)) {
        $command = strtolower($command);
      }

      $method = 'cmd' . ucfirst($command);

      if (method_exists($this, $method)) {
        return $this->$method($bot, $args, $private, $params);
      }
    }

    return false;
  }

  private function getCommandFromMessage($bot, $message, $private) {
    $match = array();
    $command = null;
    $params = null;

    $prefix = $this->getCommandPrefix();
    $re = '/^' . preg_quote($prefix, '/') . '(\S+)(?:\s+(.*))?$/';
    $re2 = '/^' . preg_quote($bot->getNick(), '/')
      . '[^a-zA-Z0-9_\s]?\s+(\S+)(?:\s+(.*))?$/';
    if (preg_match($re, $message, $match)) {
      reset($match);
      $command = next($match);
      $params = next($match);
    } else if (preg_match($re2, $message, $match)) {
      reset($match);
      $command = next($match);
      $params = next($match);
    } else if ($private) {
      $parts = preg_split('/\s+/', $message, 2);
      $command = reset($parts);
      $params = next($parts);
    }

    return array($command, $params);
  }
}
