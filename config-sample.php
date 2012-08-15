<?php
/**
 * config.php
 *
 * @package This file is part of the Rainbow Guestbook
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// Important Settings
define('RAINBOW_NAME', 'Rainbow Guestbook - Html Title');
define('RAINBOW_TITLE', 'Rainbow guestbook - Menu Title');
define('RAINBOW_URL', 'http://www.your-url.com/');

// Database Settings
define('RAINBOW_DB_ENGINE', 'mysql');
define('RAINBOW_DB_HOST', 'your-host');
define('RAINBOW_DB_NAME', 'your-database-name');
define('RAINBOW_DB_USER', 'your-db-user');
define('RAINBOW_DB_PASS', 'your-db-password');

// General Settings
define('RAINBOW_DELETE_PASSWORD', 'a random string');  // The password for deleting messages
define('RAINBOW_DEBUG', false);         // Debug, should always be false!
?>
