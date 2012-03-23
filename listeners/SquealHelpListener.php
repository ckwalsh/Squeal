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
 * This is a simple command listener for providing help messages for command
 * listeners.
 */
class SquealHelpListener extends SquealCommandListener {

  public function getCommandAliases() {
    return array(
      '?' => 'help',
    );
  }

  public function cmdHelp($bot, $message_args, $private, $cmd_params) {
    $target = $private ? $message_args['user'] : $message_args['channel'];

    $parts = preg_split('/\s+/', $cmd_params, 2);
    $command = strtolower(reset($parts));
    $params = next($parts);

    $listeners = $bot->getConfig()->listeners;
    $listeners = array_filter(
      $listeners,
      array($this, 'filterCommandListeners')
    );

    if ($command) {
      // Look for the specified command
      foreach ($listeners as $listener) {
        $aliases = $listener->getCommandAliases();
        $prefix = $listener->getCommandPrefix();

        $cmd = $command;
        if (array_key_exists($cmd, $aliases)) {
          $cmd = strtolower($aliases[$cmd]);
        }

        $method = 'cmd' . ucfirst($cmd);

        if (method_exists($listener, $method)) {
          $method = 'help' . ucfirst($cmd);
          $params = '';
          $msg = 'No help available';

          if (method_exists($listener, $method)) {
            $help_info = $listener->$method();
            $params = ' ' . $help_info['params'];
            $msg = $help_info['message'];
          }

          $target->sendMessage($prefix . $cmd . $params . ': ' . $msg);
          return true;
        }
      }
      $target->sendMessage('No help available');
    } else {
      // List all the available commands
      $commands = array();
      $prefix = '';

      foreach ($listeners as $listener) {
        $methods = get_class_methods($listener);
        $aliases = $listener->getCommandAliases();
        $prefix = $listener->getCommandPrefix();

        foreach ($methods as $method) {
          if (preg_match('/cmd[A-Z][a-z]*/', $method)) {
            $cmd = strtolower(substr($method, 3));

            $commands[$cmd] = true;
          }
        }

        foreach ($aliases as $alias => $cmd) {
          if (method_exists($listener, 'cmd' . ucfirst(strtolower($cmd)))) {
            $cmd = $alias;

            $commands[$cmd] = true;
          }
        }
      }

      ksort($commands);

      $target->sendMessage(
        'All commands: ' . implode(', ', array_keys($commands))
      );
      $target->sendMessage(
        'Commands should start with ' . $bot->getNick() . ' or ' . $prefix
      );
    }

    return true;
  }

  public function helpHelp() {
    return array(
      'params' => '<command>',
      'message' => 'Show help information about specified command',
    );
  }

  public function filterCommandListeners($listener) {
    return ($listener instanceof SquealCommandListener);
  }
}
