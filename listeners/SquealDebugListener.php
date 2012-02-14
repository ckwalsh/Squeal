<?php

class SquealDebugListener {
  public function onRaw($bot, $args) {
    echo $args['line'] . "\n";
  }
}
