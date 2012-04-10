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

if (class_exists('SplPriorityQueue')) {

final class SquealCallbackSchedule {
  private $queue;
  public function __construct() {
    $this->queue = new SplPriorityQueue();
    $this->queue->setExtractFlags(SplPriorityQueue::EXTR_BOTH);
  }

  public function add($callback, $time, $args = array()) {
    $this->queue->insert(array($callback, $args), -$time);
  }

  public function getTriggeredCallback() {
    if ($this->queue->valid()) {
      $top = $this->queue->top();
      if (-$top['priority'] < microtime(true)) {
        $this->queue->extract();
        return $top['data'];
      }
    }

    return null;
  }

  public function getTimeToNextCallback() {
    if ($this->queue->valid()) {
      $top = $this->queue->top();
      return -$top['priority'] - microtime(true);
    } else {
      return false;
    }
  }
}

} else {

final class SquealCallbackSchedule {
  private $heap;

  public function __construct() {
    $this->heap = array();
  }

  public function add($callback, $time, $args = array()) {
    $this->heap[] = array(array($callback, $args), $time);
    $this->bubbleUp();
  }

  public function getTriggeredCallback() {
    if ($this->heap && $this->heap[0][1] < microtime(true)) {
      $el = $this->heap[0];
      $new_top = array_pop($this->heap);
      if (sizeof($this->heap)) {
        $this->heap[0] = $new_top;
        $this->bubbleDown();
      }

      return $el[0];
    }

    return null;
  }

  public function getTimeToNextCallback() {
    if ($this->heap) {
      return $this->heap[0][1] - microtime(true);
    } else {
      return false;
    }
  }

  private function bubbleUp() {
    $id = sizeof($this->heap) - 1;
    $el = $this->heap[$id];
    while ($id) {
      $parent_id = (int) (($id - 1) / 2);
      if ($el[1] < $this->heap[$parent_id][1]) {
        $this->heap[$id] = $this->heap[$parent_id];
      } else {
        break;
      }
    }

    $this->heap[$id] = $el;
  }

  private function bubbleDown() {
    $id = 0;
    $el = $this->heap[0];
    $size = sizeof($this->heap);
    while (true) {
      $min = $id;
      $left = $id * 2 + 1;
      $right = $id * 2 + 2;
      if ($left < $size && $this->heap[$left][1] < $this->heap[$min][1]) {
        $min = $left;
      }
      if ($right < $size && $this->heap[$right][1] < $this->heap[$min][1]) {
        $min = $right;
      }

      if ($min == $id) {
        break;
      } else {
        $this->heap[$id] = $this->heap[$min];
        $id = $min;
      }
    }

    $this->heap[$id] = $el;
  }
}

}
