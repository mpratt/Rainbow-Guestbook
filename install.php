<?php
/**
 * install.php
 * Creates the messages table in the database.
 *
 * @package This file is part of the Rainbow Guestbook
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

define('RAINBOW', 'Install');
require(dirname(__FILE__) . '/config.php');

if (RAINBOW_DEBUG)
    error_reporting(E_ALL | E_STRICT);
else
    error_reporting(0);

try
{
    $pdo = new PDO(RAINBOW_DB_ENGINE . ':host=' . RAINBOW_DB_HOST . ';dbname=' . RAINBOW_DB_NAME . ';charset=UTF-8', RAINBOW_DB_USER, RAINBOW_DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('SET NAMES utf8');
    $pdo->query("CREATE TABLE IF NOT EXISTS `rainbow_messages` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                  `parent_id` int(10) unsigned NOT NULL DEFAULT '0',
                  `message` text NOT NULL,
                  `color` varchar(8) NOT NULL,
                  `date` date NOT NULL,
                  PRIMARY KEY (`id`),
                  KEY `parent_id` (`parent_id`),
                  KEY `color` (`color`),
                  KEY `date` (`date`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;");
}
catch(PDOException $e)
{
    header('HTTP/1.1 500 Internal Server Error');
    die('Instalattion Error - DB error!');
}

redirect_to();
?>