<?php
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
