<?php

class SquealConfig {
  public $host;
  public $port = 6667;
  public $password = null;
  public $ssl = false;
  public $name = 'SquealBot';
  public $login = 'SquealBot';
  public $version = 'SquealBot 1.2-dev';
  public $finger = "I'm SquealBot";
  public $autoNickChange = true;
  public $messageDelay = 20;
  public $tick = 200;
  public $channelPrefixes = '#&';
  public $listeners = array();
  public $joinChannels = array();

  public $userFactory;
  public $channelFactory;

  public function __construct($host) {
    $this->host = $host;
    $this->userFactory = new SquealUserFactory();
    $this->channelFactory = new SquealChannelFactory();
  }
}
