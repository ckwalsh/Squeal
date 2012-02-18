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

class SquealAnnounceListener extends SquealCommandListener {
  public function cmdAnnounce($bot, $message_args, $private, $cmd_params) {
    if ($private) {
      return false;
    }

    $channel = $message_args['channel'];
    $sender = $message_args['user'];
    $users = $channel->getUsers();

    $nicks = array();

    foreach ($users as $user) {
      $nicks[] = $user->getNick();
    }

    sort($nicks);

    $channel->sendMessage('Listen up ' . implode(' ', $nicks));
    if ($cmd_params) {
      $channel->sendMessage($sender->getNick() . ' says ' . $cmd_params);
    } else {
      $channel->sendMessage($sender->getNick() . ' has something to say');
    }

    return true;
  }

  public function helpAnnounce() {
    return array(
      'params' => '<message>',
      'message' => 'Get the attention of the channel and share the message',
    );
  }
}
