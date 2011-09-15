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
 * __________________________________
 * / Beware all ye who enter. Here be \
 * \ dragons.                         /
 *  ----------------------------------
 *       \                    / \  //\
 *        \    |\___/|      /   \//  \\
 *             /0  0  \__  /    //  | \ \
 *            /     /  \/_/    //   |  \  \
 *            @_^_@'/   \/_   //    |   \   \
 *            //_^_/     \/_ //     |    \    \
 *         ( //) |        \///      |     \     \
 *       ( / /) _|_ /   )  //       |      \     _\
 *     ( // /) '/,_ _ _/  ( ; -.    |    _ _\.-~        .-~~~^-.
 *   (( / / )) ,-{        _      `-.|.-~-.           .~         `.
 *  (( // / ))  '/\      /                 ~-. _ .-~      .-~^-.  \
 *  (( /// ))      `.   {            }                   /      \  \
 *   (( / ))     .----~-.\        \-'                 .~         \  `. \^-.
 *              ///.----..>        \             _ -~             `.  ^-`  ^-_
 *                ///-._ _ _ _ _ _ _}^ - - - - ~                     ~-- ,.-~
 *                                                                   /.-~
 * This is the big ugly class that powers everything. I don't recommand looking
 * at it too closely. You have been warned.
 *
 */
class SquealIrcBot {

  protected
    $host,
    $port,
    $password,
    $ssl,
    $socket,
    $autoNickChange,
    $output,
    $name,
    $nick,
    $login,
    $version,
    $finger,
    $messageDelay,
    $tick,
    $lastSend,
    $lastRecieve,
    $ping,
    $channels,
    $users,
    $channelPrefixes,
    $listeners,
    $eventCache,
    $schedule,
    $lineBuffer;

  public function __construct() {
    $this->autoNickChange = true;
    $this->name = 'squeal_bot';
    $this->login = 'squeal_bot';
    $this->version = 'SquealBot 1.0';
    $this->finger = "I'm SquealBot";
    $this->messageDelay = 20;
    $this->tick = 200;
    $this->channelPrefixes = '#&';
    $this->listeners = array();
    $this->lineBuffer = '';
  }

  // Connect to an IRC server
  // Does NOT start the event loop
  public function connect($host, $port = 6667, $password = null, $ssl = false) {
    if ($this->isConnected()) {
      throw new Exception('Already Connected');
    }

    $this->host = $host;
    $this->port = $port;
    $this->password = $password;
    $this->ssl = $ssl;
    $this->output = array();
    $this->channels = array();
    $this->users = array();
    $this->schedule = new SquealCallbackSchedule();

    $hostname = ($ssl ? 'ssl://' : '') . $host;
    $errno = null;
    $errstr = null;
    $this->socket = fsockopen($hostname, $port, $errno, $errstr, 60);

    if (!$this->socket) {
      $this->socket = null;
      throw new Exception("Socket Connection error $errno: $errstr");
    }

    if ($password) {
      $this->sendRawLine('PASS ' . $password, true);
    }

    $nick = $this->getName();
    $this->sendRawLine('NICK ' . $nick, true);
    $this->sendRawLine(
      'USER ' . $this->getLogin() . ' 8 * :' . $this->getVersion(),
      true
    );

    $tries = 1;

    while (true) {
      $line = fgets($this->socket);
      if ($line !== null) {
        $line = substr($line, 0, -2);
        $parts = explode(' ', $line);
        if (sizeof($parts) > 2) {
          $code = $parts[1];
          if ($code === '004') {
            // Success!
            break;
          } else if ($code == SquealReplyConstants::ERR_NICKNAMEINUSE) {
            if ($this->getAutoNickChange()) {
              $nick = $this->getName() . $tries;
              $this->sendRawLine('NICK ' . $nick, true);
              $tries++;
            } else {
              fclose($this->socket);
              $this->socket = null;
              throw new Exception('Nick already in use.');
            }
          } else if ($code == SquealReplyConstants::ERR_TARGETTOOFAST) {
            // Do nothing
          } else if ($code[0] === '4' || $code[0] === '5') {
            fclose($this->socket);
            $this->socket = null;
            throw new Exception('Unable to connect: ' . $line);
          }
        }
        $this->setNick($nick);
      } else {
        fclose($this->socket);
        $this->socket = null;
        throw new Exception('Disconnected');
      }
    }

    $this->event('Connect');

    return true;
  }

  // Returns whether the bot is connected to a server
  public function isConnected() {
    return $this->socket !== null;
  }

  // Throws an exception if we are not connected to the server
  public function enforceConnected() {
    if (!$this->isConnected()) {
      throw new Exception('Must be connected to server');
    }
  }

  // Disconnect from the server
  public function disconnect() {
    $this->quitServer();
  }

  // Sets the tick value for the event loop
  public function setTick($tick) {
    $this->tick = $tick;
  }

  // Gets the tick value for the event loop
  public function getTick() {
    return $this->tick;
  }

  // Runs the main event look. Will continue until disconnected from the server
  public function runBot() {
    stream_set_blocking($this->socket, 0);
    stream_set_timeout($this->socket, 0, 0);
    $max_length = $this->getMaxLineLength();
    while (true) {
      $this->enforceConnected();
      $tick = $this->getTick();
      $sec = (int) ($tick / 1000);
      $usec = ($tick * 1000) % 1000000;

      // So this next bit of code is ugly... sorry
      $lines = array();
      $read = array($this->socket);
      $write = array();
      $except = array();
      if (stream_select($read, $write, $except, $sec, $usec)) {
        while ($in = fread($this->socket, 8192)) {
          $this->lineBuffer .= $in;
        }
      }

      $lines = explode("\r\n", $this->lineBuffer);
      $this->lineBuffer = array_pop($lines);
      for ($i = 0, $len = sizeof($lines); $i < $len; ++$i) {
        $lines[$i] = substr($lines[$i], 0, $max_length - 2);
      }

      if ($lines) {
        $this->lastRecieve = microtime(true);
        foreach ($lines as $line) {
          $this->handleLine($line);
        }
      } else {
        $meta = stream_get_meta_data($this->socket);
        if ($meta['eof']) {
          // We must have been disconnected somehow
          fclose($this->socket);
          $this->socket = null;
          $this->event('Disconnect');
          break;
        }
      }

      $this->event('Tick');

      while ($current = $this->schedule->getTriggeredCallback()) {
        call_user_func($current[0], $this, $current[1]);
      }

      $this->processQueue();
    }
  }

  // Parses a line and executes the correct events
  public function handleLine($line) {
    $this->event('Raw', array(
      'line' => $line,
    ));
    $tokens = preg_split('/[ \t\n\r\f]/', $line);
    $pos = 0;

    // Server ping
    if (strpos($line, 'PING ') === 0) {
      $this->sendRawLine('PONG ' . substr($line, 5));
      return;
    }

    $sender_info = $tokens[$pos++];
    $command = $tokens[$pos++];

    $nick = '';
    $login = '';
    $hostname = '';
    $target_name = null;

    if ($sender_info[0] === ':') {
      $bang = strpos($sender_info, '!');
      $at = strpos($sender_info, '@');
      if ($bang && $at && $bang < $at) {
        // Message from a user
        $nick = substr($sender_info, 1, $bang - 1);
        $login = substr($sender_info, $bang + 1, $at - $bang);
        $hostname = substr($sender_info, $at + 1);
      } else {
        if ($pos < sizeof($tokens)) {
          $code = (int) $command;
          if ($code) {
            // This is a server response
            $response = substr($line, strpos($sender_info, $command) + 4);
            $this->handleServerResponse($code, $response);
            return;
          }
          else {
            // Probably a nick without a login or hostname
            $nick = $sender_info;
            $target_name = $command;
          }
        } else {
          // We have no clue what this is...
          $this->event('Unknown', array(
            'line' => $line,
          ));
          return;
        }
      }
    }

    $command = strtoupper($command);

    if (strlen($nick) && $nick[0] === ':') {
      $nick = substr($nick, 1);
    }

    if ($target_name === null) {
      $target_name = $tokens[$pos++];
    }

    if ($target_name[0] === ':') {
      $target_name = substr($target_name, 1);
    }

    $target = null;
    if (strpos($this->getChannelPrefixes(), $target_name[0]) !== false) {
      // This is a channel
      $target = $this->getChannel($target_name);
      if (!$target) {
        // A channel is added when we get our join command from the server
        if ($command === 'JOIN' && $nick === $this->getNick()) {
          $target = new SquealChannel($this, $target_name);
          $this->addChannel($target);
        } else {
          $this->event('Unknown', array(
            'line' => $line,
          ));
          return;
        }
      }
    }

    $sender = $this->getUser($nick);

    if (!$sender) {
      $sender = new SquealUser($this, $nick);
    }

    switch ($command) {
      case 'PRIVMSG':
        if (strpos($line, ":\001") && substr($line, -1) === "\001") {
          // CTCP Request
          $request = substr($line, strpos($line, ":\001") + 2, -1);
          $parts = explode(' ', $request, 2);
          switch ($parts[0]) {
            case 'VERSION':
              $sender->sendNotice(
                "\001VERSION " . $this->getVersion() . "\001"
              );
              $this->event('Version', array(
                'target'  => $target,
                'user'  => $sender,
              ));
              break;
            case 'ACTION':
              $this->event('Action', array(
                'target' => $target,
                'user'  => $sender,
                'message' => $parts[1],
              ));
              break;
            case 'PING':
              $sender->sendNotice("\001PING " . $parts[1] . "\001");
              $this->event('Ping', array(
                'target'  => $target,
                'user'  => $sender,
                'value' => $parts[1],
              ));
              break;
            case 'TIME':
              $sender->sendNotice("\001TIME " . date('D M d H:i:s Y') . "\001");
              $this->event('Time', array(
                'target'  => $target,
                'user'  => $sender,
              ));
              break;
            case 'FINGER':
              $this->sendNotice("\001VERSION " . $this->getFinger() . "\001");
              $this->event('Finger', array(
                'target'  => $target,
                'user'  => $sender,
              ));
              break;
            default:
              $this->event('Unknown', array(
                'line' => $line,
              ));
              break;
          }
        } else if ($target instanceof SquealChannel) {
          $this->event('Message', array(
            'channel' => $target,
            'user'  => $sender,
            'message' => substr($line, strpos($line, ' :') + 2),
          ));
        } else {
          $this->event('PrivMessage', array(
            'user'  => $sender,
            'message' => substr($line, strpos($line, ' :') + 2),
          ));
        }
        break;

      case 'JOIN':
        if ($target instanceof SquealChannel) {
          $target->addUser($sender);
          $sender->addChannel($target);
          // We may not know about this user
          $this->addUser($sender);

          $this->event('Join', array(
            'channel' => $target,
            'user'  => $sender,
          ));
        } else {
          $this->event('Unknown', array(
            'line' => $line,
          ));
        }
        break;

      case 'PART':
        if ($target instanceof SquealChannel) {
          $target->removeUser($sender);
          $sender->removeChannel($target);
          if (!$sender->getChannels()) {
            // This person is going out of scope. Forget them
            $this->removeUser($sender);
          }

          if ($sender->getNick() === $this->getNick()) {
            $this->removeChannel($target);
          }

          $this->event('Part', array(
            'channel' => $target,
            'user'  => $sender,
          ));
        } else {
          $this->event('Unknown', array(
            'line' => $line,
          ));
        }
        break;

      case 'NICK':
        $old_nick = $sender->getNick();
        $this->removeUser($sender);
        $sender->setNick($target_name);
        $this->addUser($sender);

        if ($old_nick === $this->getNick()) {
          $this->setNick($target_name);
        }

        $this->event('Nick', array(
          'user' => $sender,
          'old_nick' => $old_nick,
        ));
        break;

      case 'NOTICE':
        $this->event('Notice', array(
          'target' => $target,
          'user'  => $sender,
          'notice' => substr($line, strpos($line, ' :') + 2),
        ));
        break;

      case 'QUIT':
        foreach ($sender->getChannels() as $channel) {
          $channel->removeUser($sender);
        }
        $this->removeUser($sender);
        $this->event('Quit', array(
          'user' => $sender,
          'reason' => substr($line, strpos($line, ' :') + 2),
        ));
        break;

      case 'KICK':
        if ($target instanceof SquealChannel) {
          $leaver = $this->getUser($tokens[$pos++]);
          if ($leaver) {
            $target->removeUser($leaver);
            $leaver->removeChannel($target);
            if (!$leaver->getChannels()) {
              // This person is going out of scope. Forget them
              $this->removeUser($leaver);
            }
          }
          $this->event('Kick', array(
            'channel' => $target,
            'kicker' => $sender,
            'recipient' => $leaver,
            'reason' => substr($line, strpos($line, ' :') + 2),
          ));
        } else {
          $this->event('Unknown', array(
            'line' => $line,
          ));
        }
        break;

      case 'MODE':
        $mode = substr(
          $line,
          strpos($line, $target_name, 2) + strlen($target_name) + 1
        );
        if ($mode[0] === ':') {
          $mode = substr($mode, 1);
        }
        // @TODO(cwalsh) Add some logic for handling modes
        break;

      case 'TOPIC':
        if ($target instanceof SquealChannel) {
          $topic = substr($line, strpos($line, ' :') + 2);
          $old_topic = $target->getTopic();
          $target->setTopic($topic);
          $this->event('Topic', array(
            'channel' => $target,
            'user' => $sender,
            'old_topic' => $old_topic,
          ));
        } else {
          $this->event('Unknown', array(
            'line' => $line,
          ));
        }
        break;

      case 'INVITE':
          $this->event('Invite', array(
            'user' => $sender,
            'channel' => substr($line, strpos($line, ' :') + 2),
          ));
        break;

      default:
        $this->event('Unknown', array(
          'line' => $line,
        ));
    }
  }

  // Handles numerical responses from the server
  public function handleServerResponse($code, $response) {
    switch ($code) {
      case SquealReplyConstants::RPL_LIST:
        $tokens = explode(' ', $response, 4);
        $channel = $this->getChannel($tokens[1]);
        if ($channel) {
          $count = (int) $tokens[2];
          $topic = substr(strstr($tokens[3], ':'), 1);
          $channel->setUserCount($count);
          $channel->setTopic($count);
        }
        break;

      case SquealReplyConstants::RPL_TOPICINFO:
        $tokens = preg_split('/[ \t\n\r\f]+/', $response, 5);
        $channel = $this->getChannel($tokens[1]);
        if ($channel) {
          $user = $this->getUser($tokens[2]);
          if (!$user) {
            $user = new SquealUser($this, $tokens[2]);
          }
          $old_topic = $channel->getTopic();
          $date = (int) $tokens[3];
          $this->event('Topic', array(
            'channel' => $channel,
            'user' => $user,
            'old_topic' => $old_topic,
            'date' => $date,
          ));
        }
        break;

      case SquealReplyConstants::RPL_NAMREPLY:
        $tokens = preg_split('/[ \t\n\r\f]+/', $response);
        $pos = 4;
        $name = $tokens[$pos++];
        $channel = $this->getChannel($name);

        $pos++;

        if ($channel) {
          $user_prefixes = '@+.';
          while ($pos < sizeof($tokens)) {
            $nick = $tokens[$pos++];
            $prefix = null;
            if (strpos($user_prefixes, $nick[0]) !== false) {
              $prefix = $nick[0];
              $nick = substr($nick, 1);
            }

            $user = $this->getUser($nick);
            if (!$user) {
              $user = new SquealUser($this, $nick);
              $this->addUser($user);
            }
            $channel->addUser($user);
            $user->addChannel($channel);
          }
        }
        break;
    }
  }

  // If the bot is unable to connect due to a nick conflict, should the bot
  // append a number to the end of the current nick and try again?
  public function setAutoNickChange($auto_nick_change) {
    $this->autoNickChange = $auto_nick_change;
  }

  public function getAutoNickChange() {
    return $this->autoNickChange;
  }

  // Join a channel on the server
  public function joinChannel($name, $key = null) {
    $this->sendRawLine('JOIN ' . $name . ($key ? ' ' . $key : ''));
  }

  // Quit the IRC server
  public function quitServer($reason = null) {
    $this->enforceConnected();
    $this->sendRawLine('QUIT :' . $reason, true);
  }

  // Puts a line in the output queue to send to the IRC server. If the second
  // parameter is true, it flushes the queue immediately. Shouldn't really be
  // used, but is available in case you are about to execute a long function
  // and need persistent updates
  public function sendRawLine($line, $immediately = false) {
    $this->output[] = $line;
    if ($immediately) {
      $this->processQueue();
    }
  }

  // Process the output queue and send it to the server
  public function processQueue() {
    $this->enforceConnected();
    $max_length = $this->getMaxLineLength() - 2;
    $count = 0;
    foreach ($this->output as $line) {
      $line = substr($line, 0, $max_length);
      if (!fwrite($this->socket, $line . "\r\n")) {
        fclose($this->socket);
        $this->socket = null;
        throw new Exception('Error Writing to socket. Disconnected');
      }
      $this->lastSend = microtime(true);
      if (++$count < count($this->output)) {
        usleep($this->getMessageDelay() * 1000);
      }
    }

    $this->output = array();
  }

  // Change the nickname of the bot. The nick change is only successful once we
  // get confirmation from the server
  public function changeNick($nick) {
    $this->sendRawLine('NICK ' . $nick);
  }

  // Identify to nickserv using the given password
  public function identify($password) {
    $this->sendRawLine('NICKSERV IDENTIFY ' . $password);
  }

  // Sets the name of the bot. Set this before you connect to the IRC server to
  // set your initial nick;
  public function setName($name) {
    $this->name = $name;
  }

  public function getName() {
    return $this->name;
  }

  public function getNick() {
    return $this->nick;
  }

  // This actually performs the nick change after we get confirmation from the
  // server.
  private function setNick($nick) {
    $this->nick = $nick;
  }

  public function getLogin() {
    return $this->login;
  }

  public function getVersion() {
    return $this->version;
  }

  public function getFinger() {
    return $this->finger;
  }

  // How much time in miliseconds should we wait between sending messages, in
  // order to not flood the server?
  public function setMessageDelay($delay) {
    $this->messageDelay = $delay;
  }

  public function getMessageDelay() {
    return $this->messageDelay;
  }

  // This is set by the IRC spec, probably shouldn't touch it
  public function getMaxLineLength() {
    return 512;
  }

  public function getServer() {
    return $this->host;
  }

  public function getPort() {
    return $this->port;
  }

  public function getPassword() {
    return $this->password;
  }

  // Get all the channels this bot is currently in
  public function getChannels() {
    return $this->channels;
  }

  // Sets the valid prefixes, such as '#', for channels on this server
  public function setChannelPrefixes($prefixes) {
    $this->channelPrefixes = $prefixes;
  }

  public function getChannelPrefixes() {
    return $this->channelPrefixes;
  }

  // Triggers the given event and calls the appropriate listeners. See HOWTO
  // for a listing of valid events and the args that are passed this them. This
  // is public so that listeners can implement their own events; please don't
  // abuse it.
  public function event($event, $args = array()) {
    if ($this->eventCache === null) {
      $this->eventCache = array('All' => array());

      foreach ($this->listeners as $listener) {
        $methods = get_class_methods($listener);
        foreach ($methods as $method) {
          if (strncmp('on', $method, 2) === 0) {
            $ev = substr($method, 2);

            if (!array_key_exists($ev, $this->eventCache)) {
              $this->eventCache[$ev] = array();
            }

            $this->eventCache[$ev][] = array($listener, $method);
          }
        }
      }
    }

    if (array_key_exists($event, $this->eventCache)) {
      foreach ($this->eventCache[$event] as $callback) {
        $break = call_user_func($callback, $this, $args);
        if ($break) {
          break;
        }
      }
    }

    foreach ($this->eventCache['All'] as $callback) {
      call_user_func($callback, $this, $event, $args);
    }
  }

  // Schedules the callback to be executed after the timestamp $time
  public function scheduleCallback($callback, $time, $args = array()) {
    // Negative time since it's a max heap
    $this->schedule->insert(array($callback, $args), -$time);
  }

  // Pass an object that implements listener methos (onXXX) and it will be run.
  public function addEventListener($listener) {
    $this->listeners[] = $listener;
    $this->eventCache = null;
  }

  public function getEventListeners() {
    return $this->listeners;
  }

  private function addUser($user) {
    if (!array_key_exists($user->getNick(), $this->users)) {
      $this->users[$user->getNick()] = $user;
    }
  }

  private function removeUser($user) {
    foreach ($user->getChannels() as $channel) {
      $channel->removeUser($user);
    }

    if (array_key_exists($user->getNick(), $this->users)) {
      unset($this->users[$user->getNick()]);
    }
  }

  public function getUser($nick) {
    if (array_key_exists($nick, $this->users)) {
      return $this->users[$nick];
    }

    return null;
  }

  private function addChannel($channel) {
    $this->channels[$channel->getName()] = $channel;
  }

  // Don't use this
  public function removeChannel($channel) {
    foreach ($channel->getUsers() as $user) {
      $user->removeChannel($channel);
      if (!$user->getChannels()) {
        $this->removeUser($user);
      }
    }
    if (array_key_exists($channel->getName(), $this->channels)) {
      unset($this->channels[$channel->getName()]);
    }
  }

  public function getChannel($name) {
    if (array_key_exists($name, $this->channels)) {
      return $this->channels[$name];
    }

    return null;
  }
}
