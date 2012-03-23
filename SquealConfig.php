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

class SquealConfig {
  public $host;
  public $port = 6667;
  public $password = null;
  public $ssl = false;
  public $name = 'SquealBot';
  public $nickservPassword = null;
  public $login = 'SquealBot';
  public $version = 'SquealBot 1.2-dev';
  public $finger = "I'm SquealBot";
  public $autoNickChange = true;
  public $messageDelay = 20;
  public $tick = 200;
  public $channelPrefixes = '#&';
  public $listeners = array();
  public $joinChannels = array();

  public $userFactory;
  public $channelFactory;

  public function __construct($host) {
    $this->host = $host;
    $this->userFactory = new SquealUserFactory();
    $this->channelFactory = new SquealChannelFactory();
  }
}
