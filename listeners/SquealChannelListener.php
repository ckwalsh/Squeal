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
class SquealChannelListener extends SquealCommandListener {
  public function cmdJoin($bot, $message_args, $private, $cmd_params) {
    $target = $private ? $message_args['user'] : $message_args['channel'];
    $sender = $message_args['user'];

    if (!$cmd_params) {
      $target->sendMessage($sender->getNick() . ': Please specify a channel');
      return true;
    }

    $parts = preg_split('/\s+/', $cmd_params);
    $prefixes = $bot->getChannelPrefixes();

    if (count($parts) > 1 || strpos($prefixes, $cmd_params[0]) === false) {
      $target->sendMessage($sender->getNick() . ': Invalid channel');
      return true;
    }

    $bot->joinChannel($cmd_params);
    $target->sendMessage($sender->getNick() . ': See me in ' . $cmd_params);
  }

  public function cmdPart($bot, $message_args, $private, $cmd_params) {
    if ($private) {
      return false;
    }

    $channel = $message_args['channel'];
    $sender = $message_args['user'];
    $channels = array();

    foreach ($bot->getChannels() as $c) {
      if ($c !== $channel) {
        $channels[] = ($c->getName());
      }
    }

    if (!$channels) {
      $channel->sendMessage(
        $sender->getNick() . ': No can do. This is the only channel I\'m in.'
      );
    } else {
      $channel->sendMessage(
        $sender->getNick() . ': Find me in any of these channels: '
         . implode(' ', $channels)
      );
      $channel->part($cmd_params);
    }

    return true;
  }

  public function helpJoin() {
    return array(
      'params' => '<channel>',
      'message' => 'Join the channel',
    );
  }

  public function helpPart() {
    return array(
      'params' => '<reason>',
      'message' => 'Leave this channel',
    );
  }
}
