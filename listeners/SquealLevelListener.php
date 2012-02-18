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
 * This is just an example listener to show storing user state
 */
class SquealLevelListener extends SquealCommandListener {
  private $watches = array();

  public function cmdLvlup($bot, $message_args, $private, $cmd_params) {
    if ($private) {
      return false;
    }

    $channel = $message_args['channel'];
    $sender = $message_args['user'];

    if (isset($sender['level'])) {
      $sender['level'] = 1 + (int) $sender['level'];
    } else {
      $sender['level'] = 1;
    }

    $channel->sendMessage(
      $sender->getNick() . ' is now level ' . $sender['level']
    );

    return true;
  }

  public function helpLvlup() {
    return array(
      'params' => '',
      'message' => 'Level Up',
    );
  }
}
