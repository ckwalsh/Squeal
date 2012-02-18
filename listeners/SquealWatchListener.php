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

class SquealWatchListener extends SquealCommandListener {
  private $watches = array();

  public function cmdWatch($bot, $message_args, $private, $cmd_params) {
    $target = $private ? $message_args['user'] : $message_args['channel'];
    $sender = $message_args['user'];
    $key = $cmd_params;

    if (!preg_match('/#?[A-Za-z0-9]+/', $key)) {
      $target->sendMessage('Invalid Tag');
      return true;
    }

    if (strlen($key) && $key[0] === '#') {
      $key = substr($key, 1);
    }

    $this->add(strtolower($key), $sender->getNick());
    $target->sendMessage(
      $sender->getNick() . ' is watching #' . $key
    );

    return true;
  }

  public function cmdIgnore($bot, $message_args, $private, $cmd_params) {
    $target = $private ? $message_args['user'] : $message_args['channel'];
    $sender = $message_args['user'];
    $key = $cmd_params;

    if (!preg_match('/#?[A-Za-z0-9]+/', $key)) {
      $target->sendMessage('Invalid Tag');
      return true;
    }

    if (strlen($key) && $key[0] === '#') {
      $key = substr($key, 1);
    }

    $this->remove(strtolower($key), $sender->getNick());
    $target->sendMessage(
      $sender->getNick() . ' is ignoring #' . $key
    );

    return true;
  }

  public function onMessage($bot, $args) {
    if (!parent::onMessage($bot, $args)) {
      $channel = $args['channel'];
      $message = $args['message'];
      $tags = array();

      if (preg_match_all('/#[A-Za-z0-9]+/', $message, $tags)) {
        $channel_nicks = array();

        foreach ($channel->getUsers() as $user) {
          $nick = $user->getNick();
          $channel_nicks[$nick] = $nick;
        }

        foreach ($tags[0] as $tag) {
          $key = substr($tag, 1);
          $nicks = $this->getNicks(strtolower($key));
          $nicks = array_intersect_key($nicks, $channel_nicks);
          if ($nicks) {
            $channel->sendMessage(
              'Pinging ' . $tag . ': ' . implode(' ', $nicks)
            );
          }
        }
      }
    }

    return false;
  }

  public function helpWatch() {
    return array(
      'params' => '<tag>',
      'message' => 'Highlight me if "#<tag>" is mentioned in the chat',
    );
  }

  public function helpIgnore() {
    return array(
      'params' => '<tag>',
      'message' => 'Do not highlight me if "#<tag>" is mentioned in the chat',
    );
  }

  protected function add($key, $nick) {
    if (!array_key_exists($key, $this->watches)) {
      $this->watches[$key] = array();
    }

    $this->watches[$key][$nick] = $nick;
  }

  protected function remove($key, $nick) {
    if (array_key_exists($key, $this->watches)) {
      if (array_key_exists($nick, $this->watches[$key])) {
        unset($this->info[$key]);
      }
    }
  }

  protected function getNicks($key) {
    if (array_key_exists($key, $this->watches)) {
      return $this->watches[$key];
    }

    return array();
  }
}
