<?php

class SquealPingListener {
  const SECONDS_BETWEEN_PINGS = 180;

  public function onConnect($bot, $args) {
    $bot->scheduleCallback(
      array($this, 'ping'),
      microtime(true) + self::SECONDS_BETWEEN_PINGS
    );
  }

  public function ping($bot, $args = array()) {
    $bot->sendRawLine('PING ' . time());

    $bot->scheduleCallback(
      array($this, 'ping'),
      microtime(true) + self::SECONDS_BETWEEN_PINGS
    );
  }
}
