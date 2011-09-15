<?php
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
