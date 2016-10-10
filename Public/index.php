<?php
/**
 * User: Peter Clotworthy
 * Date: 08/10/16
 * Time: 00:16
 */

use Assemble\Server;

// Report the earliest startup errors.
// Ramble will keep this on/turn off based on the DEBUG setting.
ini_set('display_errors', 'On');
error_reporting(E_ALL);

require __DIR__ . "/../src/Assemble/Server.php";

$server = new Server();

session_start();
$server();