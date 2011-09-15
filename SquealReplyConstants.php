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
 * Constants for numeric IRC commands
 */
class SquealReplyConstants {
  // Error Replies.
  const ERR_NOSUCHNICK = 401;
  const ERR_NOSUCHSERVER = 402;
  const ERR_NOSUCHCHANNEL = 403;
  const ERR_CANNOTSENDTOCHAN = 404;
  const ERR_TOOMANYCHANNELS = 405;
  const ERR_WASNOSUCHNICK = 406;
  const ERR_TOOMANYTARGETS = 407;
  const ERR_NOORIGIN = 409;
  const ERR_NORECIPIENT = 411;
  const ERR_NOTEXTTOSEND = 412;
  const ERR_NOTOPLEVEL = 413;
  const ERR_WILDTOPLEVEL = 414;
  const ERR_UNKNOWNCOMMAND = 421;
  const ERR_NOMOTD = 422;
  const ERR_NOADMININFO = 423;
  const ERR_FILEERROR = 424;
  const ERR_NONICKNAMEGIVEN = 431;
  const ERR_ERRONEUSNICKNAME = 432;
  const ERR_NICKNAMEINUSE = 433;
  const ERR_NICKCOLLISION = 436;
  const ERR_TARGETTOOFAST = 439;
  const ERR_USERNOTINCHANNEL = 441;
  const ERR_NOTONCHANNEL = 442;
  const ERR_USERONCHANNEL = 443;
  const ERR_NOLOGIN = 444;
  const ERR_SUMMONDISABLED = 445;
  const ERR_USERSDISABLED = 446;
  const ERR_NOTREGISTERED = 451;
  const ERR_NEEDMOREPARAMS = 461;
  const ERR_ALREADYREGISTRED = 462;
  const ERR_NOPERMFORHOST = 463;
  const ERR_PASSWDMISMATCH = 464;
  const ERR_YOUREBANNEDCREEP = 465;
  const ERR_KEYSET = 467;
  const ERR_CHANNELISFULL = 471;
  const ERR_UNKNOWNMODE = 472;
  const ERR_INVITEONLYCHAN = 473;
  const ERR_BANNEDFROMCHAN = 474;
  const ERR_BADCHANNELKEY = 475;
  const ERR_NOPRIVILEGES = 481;
  const ERR_CHANOPRIVSNEEDED = 482;
  const ERR_CANTKILLSERVER = 483;
  const ERR_NOOPERHOST = 491;
  const ERR_UMODEUNKNOWNFLAG = 501;
  const ERR_USERSDONTMATCH = 502;

  // Command Responses.
  const RPL_TRACELINK = 200;
  const RPL_TRACECONNECTING = 201;
  const RPL_TRACEHANDSHAKE = 202;
  const RPL_TRACEUNKNOWN = 203;
  const RPL_TRACEOPERATOR = 204;
  const RPL_TRACEUSER = 205;
  const RPL_TRACESERVER = 206;
  const RPL_TRACENEWTYPE = 208;
  const RPL_STATSLINKINFO = 211;
  const RPL_STATSCOMMANDS = 212;
  const RPL_STATSCLINE = 213;
  const RPL_STATSNLINE = 214;
  const RPL_STATSILINE = 215;
  const RPL_STATSKLINE = 216;
  const RPL_STATSYLINE = 218;
  const RPL_ENDOFSTATS = 219;
  const RPL_UMODEIS = 221;
  const RPL_STATSLLINE = 241;
  const RPL_STATSUPTIME = 242;
  const RPL_STATSOLINE = 243;
  const RPL_STATSHLINE = 244;
  const RPL_LUSERCLIENT = 251;
  const RPL_LUSEROP = 252;
  const RPL_LUSERUNKNOWN = 253;
  const RPL_LUSERCHANNELS = 254;
  const RPL_LUSERME = 255;
  const RPL_ADMINME = 256;
  const RPL_ADMINLOC1 = 257;
  const RPL_ADMINLOC2 = 258;
  const RPL_ADMINEMAIL = 259;
  const RPL_TRACELOG = 261;
  const RPL_NONE = 300;
  const RPL_AWAY = 301;
  const RPL_USERHOST = 302;
  const RPL_ISON = 303;
  const RPL_UNAWAY = 305;
  const RPL_NOWAWAY = 306;
  const RPL_WHOISUSER = 311;
  const RPL_WHOISSERVER = 312;
  const RPL_WHOISOPERATOR = 313;
  const RPL_WHOWASUSER = 314;
  const RPL_ENDOFWHO = 315;
  const RPL_WHOISIDLE = 317;
  const RPL_ENDOFWHOIS = 318;
  const RPL_WHOISCHANNELS = 319;
  const RPL_LISTSTART = 321;
  const RPL_LIST = 322;
  const RPL_LISTEND = 323;
  const RPL_CHANNELMODEIS = 324;
  const RPL_NOTOPIC = 331;
  const RPL_TOPIC = 332;
  const RPL_TOPICINFO = 333;
  const RPL_INVITING = 341;
  const RPL_SUMMONING = 342;
  const RPL_VERSION = 351;
  const RPL_WHOREPLY = 352;
  const RPL_NAMREPLY = 353;
  const RPL_LINKS = 364;
  const RPL_ENDOFLINKS = 365;
  const RPL_ENDOFNAMES = 366;
  const RPL_BANLIST = 367;
  const RPL_ENDOFBANLIST = 368;
  const RPL_ENDOFWHOWAS = 369;
  const RPL_INFO = 371;
  const RPL_MOTD = 372;
  const RPL_ENDOFINFO = 374;
  const RPL_MOTDSTART = 375;
  const RPL_ENDOFMOTD = 376;
  const RPL_YOUREOPER = 381;
  const RPL_REHASHING = 382;
  const RPL_TIME = 391;
  const RPL_USERSSTART = 392;
  const RPL_USERS = 393;
  const RPL_ENDOFUSERS = 394;
  const RPL_NOUSERS = 395;

  // Reserved Numerics.
  const RPL_TRACECLASS = 209;
  const RPL_STATSQLINE = 217;
  const RPL_SERVICEINFO = 231;
  const RPL_ENDOFSERVICES = 232;
  const RPL_SERVICE = 233;
  const RPL_SERVLIST = 234;
  const RPL_SERVLISTEND = 235;
  const RPL_WHOISCHANOP = 316;
  const RPL_KILLDONE = 361;
  const RPL_CLOSING = 362;
  const RPL_CLOSEEND = 363;
  const RPL_INFOSTART = 373;
  const RPL_MYPORTIS = 384;
  const ERR_YOUWILLBEBANNED = 466;
  const ERR_BADCHANMASK = 476;
  const ERR_NOSERVICEHOST = 492;
}
