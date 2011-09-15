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
 * This class represents an IRC channel, and may be used to send messages and
 * perform other channel related actions. SquealChannel objects should NOT be
 * stored between event calls, as that may cause them to come out of sync or
 * leak memory. Instead, save the channel name and retrieve them each time
 * using SquealIrcBot::getChannel().
 */
final class SquealChannel {
  private
    $bot,
    $name,
    $users;

  public function __construct($bot, $name) {
    $this->bot = $bot;
    $this->name = $name;
    $this->users = array();
  }

  public function getName() {
    return $this->name;
  }

  public function getUsers() {
    return $this->users;
  }

  public function addUser($user) {
    $this->users[$user->getID()] = $user;
  }

  public function removeUser($user) {
    if (array_key_exists($user->getID(), $this->users)) {
      unset($this->users[$user->getID()]);
    }
  }

  public function sendMessage($message) {
    $this->bot->sendRawLine('PRIVMSG ' . $this->getName() . ' :' . $message);
  }

  public function sendAction($action) {
    $this->sendMessage("\001ACTION " . $action . "\001");
  }

  public function sendNotice($notice) {
    $this->bot->sendRawLine('NOTICE ' . $this->getName() . ' :' . $notice);
  }

  public function part($reason = null) {
    if ($reason) {
      $this->bot->sendRawLine('PART ' . $this->getName() . ' :' . $reason);
    } else {
      $this->bot->sendRawLine('PART ' . $this->getName());
    }
  }

  public function setMode($mode) {
    $this->bot->sendRawLine('MODE ' . $this->getName() . ' ' . $mode);
  }

  public function invite($nick) {
    $this->bot->sendRawLine('INVITE ' . $nick . ' :' . $this->getName());
  }

  public function ban($hostmask) {
    $this->setMode('+b ' . $hostmask);
  }

  public function unban($hostmask) {
    $this->setMode('-b ' . $hostmask);
  }

  public function op($nick) {
    $this->setMode('+o ' . $nick);
  }

  public function deOp($nick) {
    $this->setMode('-o ' . $nick);
  }

  public function voice($nick) {
    $this->setMode('+v ' . $nick);
  }

  public function deVoice($nick) {
    $this->setMode('-v ' . $nick);
  }

  public function setTopic($topic) {
    $this->bot->sendRawLine('TOPIC ' . $this->getName() . ' :' . $topic);
  }

  public function kick($nick, $reason = null) {
    if ($reason) {
      $this->bot->sendRawLine(
        'KICK ' . $this->getName() . ' ' . $nick . ' :' . $reason
      );
    } else {
      $this->bot->sendRawLine('KICK ' . $this->getName() . ' ' . $nick);
    }
  }
}
