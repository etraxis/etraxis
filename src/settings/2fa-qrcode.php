<?php

//------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system
//  Copyright (C) 2005-2011  Artem Rodygin
//
//  This program is free software: you can redistribute it and/or modify
//  it under the terms of the GNU General Public License as published by
//  the Free Software Foundation, either version 3 of the License, or
//  (at your option) any later version.
//
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU General Public License for more details.
//
//  You should have received a copy of the GNU General Public License
//  along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
//------------------------------------------------------------------------------

/**
 * @package eTraxis
 * @ignore
 */

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require __DIR__ . '/../../google2fa/vendor/autoload.php';
/**#@-*/

$username = try_request('username');
$secret   = try_request('secret');

$google2fa = new \PragmaRX\Google2FA\Google2FA();

$url = $google2fa->getQRCodeUrl('eTraxis', $username, $secret);

$renderer = new \BaconQrCode\Renderer\Image\Svg();
$renderer->setWidth(200);
$renderer->setHeight(200);

$bacon = new \BaconQrCode\Writer($renderer);
echo $bacon->writeString($url);

?>
