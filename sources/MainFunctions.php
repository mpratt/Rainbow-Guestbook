<?php
/**
 * MainFunctions.php
 * Important helper functions
 *
 * @package This file is part of the Rainbow Guestbook
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
if (!defined('RAINBOW')) {
    header('Location: ../index.php');
    die();
}

/**
 * Redirects to $page
 *
 * @param string $page
 * @return void
 */
function redirect_to($page = '')
{
    if (empty($page))
        $page = 'index.php';

    header('Location:' . $page);
    die();
}

/**
 * Gets the ip of the current user
 *
 * @return string
 */
function detectIp()
{
    if  (!empty($_SERVER['HTTP_CLIENT_IP']))
        return $_SERVER['HTTP_CLIENT_IP'];
    else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if (!empty($_SERVER['REMOTE_ADDR']))
        return $_SERVER['REMOTE_ADDR'];
    else
        return 'unknown';
}

/**
 * Calculates a hexadecimal color for $ip
 *
 * @param string $ip
 * @return string
 */
function ipColor($ip)
{
    $hash = sha1($ip);
    $hash = substr($hash, 0, strlen($hash) % 34);

    // test just in case, but this condition should be met ALWAYS!
    if (ctype_xdigit($hash) && strlen($hash) == 6)
        return $hash;

    return sprintf('%02X%02X%02X', mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
}
?>