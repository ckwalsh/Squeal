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
 * This represents a user in an IRC channel and can be used to perform actions
 * such as sending messages. SquealUser objects should NOT be stored between
 * requests, as SquealIRC bot keeps track of users across channels and it may
 * break consistency or introduce memory leaks. If you need to confirm if a
 * user is the same, you can use the getID() function.
 *
 * An important note is that this object implements ArrayAccess: You can set
 * arbitrary key/value pairs and expect them to stick around for the duration
 * of the user. This may be particularly useful for authentication to a
 * listener. These variables are maintained while the user is visible to the
 * bot; if they leave all the channels the bot is currently in, their state is
 * lost and they will need to be reauthenticated.
 */
final class SquealUser implements ArrayAccess {
  private
    $id,
    $bot,
    $nick,
    $channels,
    $vals;

  private static $counter = 0;

  public function __construct($bot, $nick) {
    $this->id = 'u' . self::$counter++;
    $this->bot = $bot;
    $this->nick = $nick;
    $this->channels = array();
    $this->vals = array();
  }

  public function getID() {
    return $this->id;
  }

  public function getNick() {
    return $this->nick;
  }

  public function setNick($nick) {
    $this->nick = $nick;
  }

  public function sendMessage($message) {
    $this->bot->sendRawLine('PRIVMSG ' . $this->getNick() . ' :' . $message);
  }

  public function sendAction($action) {
    $this->sendMessage("\001ACTION " . $action . "\001");
  }

  public function sendNotice($notice) {
    $this->bot->sendRawLine('NOTICE ' . $this->getNick() . ' :' . $notice);
  }

  public function getChannels() {
    return $this->channels;
  }

  public function addChannel($channel) {
    $this->channels[$channel->getName()] = $channel;
  }

  public function removeChannel($channel) {
    if (array_key_exists($channel->getName(), $this->channels)) {
      unset($this->channels[$channel->getName()]);
    }
  }

  public function offsetExists($key) {
    return array_key_exists($key, $this->vals);
  }

  public function offsetGet($key) {
    return $this->vals[$key];
  }

  public function offsetSet($key, $value) {
    $this->vals[$key] = $value;
  }

  public function offsetUnset($key) {
    unset($this->vals[$key]);
  }
}
