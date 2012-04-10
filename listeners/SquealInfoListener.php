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
class SquealInfoListener extends SquealCommandListener {
  private $info = array();

  public function getCommandAliases() {
    return array(
      'i' => 'info',
    );
  }

  public function cmdLearn($bot, $message_args, $private, $cmd_params) {
    $target = $private ? $message_args['user'] : $message_args['channel'];

    $parts = preg_split('/\s+/', $cmd_params, 2);

    $key = reset($parts);
    $value = next($parts);

    if (!$key) {
      $target->sendMessage('No topic specified');
    } else if (!$value) {
      $target->sendMessage('No text specified.');
    } else {
      $this->set($key, $value);
      $target->sendMessage($key . ': ' . $value);
    }

    return true;
  }

  public function cmdInfo($bot, $message_args, $private, $topic) {
    $target = $private ? $message_args['user'] : $message_args['channel'];

    $text = $this->get($topic);

    if ($text) {
      $target->sendMessage($topic . ': ' . $text);
    } else {
      $target->sendMessage('Huh? What\'s "' . $topic . '"');
    }

    return true;
  }

  public function cmdForget($bot, $message_args, $private, $topic) {
    $target = $private ? $message_args['user'] : $message_args['channel'];

    $text = $this->get($topic);

    if ($text) {
      $this->remove($topic);
      $target->sendMessage('Forgot about "' . $topic . '"');
    } else {
      $target->sendMessage('I never knew anything about "' . $topic . '"');
    }

    return true;
  }

  public function helpLearn() {
    return array(
      'params' => '<topic> <text>',
      'message' => 'Store the text for lookup with the "info" command',
    );
  }

  public function helpInfo() {
    return array(
      'params' => '<topic>',
      'message' => 'Looks up the topic and displays information about it',
    );
  }

  public function helpForget() {
    return array(
      'params' => '<topic>',
      'message' => 'Forgets information stored by "learn"',
    );
  }

  protected function get($key) {
    $key = strtolower($key);
    if (array_key_exists($key, $this->info)) {
      return $this->info[$key];
    }

    return null;
  }

  protected function set($key, $value) {
    $key = strtolower($key);
    $this->info[$key] = $value;
  }

  protected function remove($key) {
    $key = strtolower($key);
    if (array_key_exists($key, $this->info)) {
      unset($this->info[$key]);
    }
  }

  protected function getKeys() {
    return array_keys($this->info);
  }
}
