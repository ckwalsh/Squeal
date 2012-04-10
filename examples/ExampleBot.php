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

require_once('../SquealCallbackSchedule.php');
require_once('../SquealChannel.php');
require_once('../SquealIrcBot.php');
require_once('../SquealConfig.php');
require_once('../SquealReplyConstants.php');
require_once('../SquealUser.php');
require_once('../listeners/SquealCommandListener.php');
require_once('../listeners/SquealAnnounceListener.php');
require_once('../listeners/SquealHelpListener.php');
require_once('../listeners/SquealPingListener.php');
require_once('../listeners/SquealDebugListener.php');

error_reporting(E_ALL | E_STRICT);

$config = new SquealConfig('chat.freenode.net');
$config->port = 6667;
$config->name = 'ExampleBot';
$config->joinChannels['#testing'] = null;
$config->listeners[] = new SquealHelpListener();
$config->listeners[] = new SquealAnnounceListener();
$config->listeners[] = new SquealPingListener();
$config->listeners[] = new SquealDebugListener();

$bot = new SquealIrcBot($config);
$bot->run();
