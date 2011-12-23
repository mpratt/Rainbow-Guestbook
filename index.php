<?php
/**
 * index.php
 *
 * @package This file is part of the Rainbow Guestbook
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

define('RAINBOW', 1);
require(dirname(__FILE__) . '/config.php');
require(dirname(__FILE__) . '/sources/MainFunctions.php');

session_start();
if (!isset($_SESSION['token']))
    $_SESSION['token'] = sha1(time() . session_id() . mt_rand(1000, 5000));
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo RAINBOW_LANG; ?>" lang="<?php echo RAINBOW_LANG; ?>" dir="ltr">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title><?php echo RAINBOW_LANG_TITLE; ?></title>
        <link rel="stylesheet" href="fashion/style.css?<?php echo time(); ?>" type="text/css" />
        <script type="text/javascript">
            var request = <?php if (!empty($_GET)) { echo json_encode($_GET); } else { echo '{}'; } ?>;
            var token = '<?php echo $_SESSION['token']; ?>';
            var myColor = '<?php echo ipColor(detectIp()); ?>';
            var rainbowLang  = {'language': '<?php echo RAINBOW_LANG; ?>',
                                'empty': '<?php echo RAINBOW_LANG_EMPTY; ?>',
                                'loading': '<?php echo RAINBOW_LANG_LOADING; ?>',
                                'badBrowser': '<?php echo RAINBOW_LANG_BAD_BROWSER; ?>',
                                'view' : '<?php echo RAINBOW_LANG_VIEW; ?>',
                                'reply': '<?php echo RAINBOW_LANG_REPLY; ?>',
                                'create' : '<?php echo RAINBOW_LANG_CREATE_BUTTON; ?>',
                                'createDesc' : '<?php echo RAINBOW_LANG_CREATE; ?>',
                                'replies': '<?php echo RAINBOW_LANG_REPLIES; ?>',
                                'addFavorite': '<?php echo RAINBOW_LANG_ADD_FAVORITE; ?>',
                                'removeFavorite': '<?php echo RAINBOW_LANG_REMOVE_FAVORITE; ?>',
                                'deleteShow': '<?php echo RAINBOW_LANG_DELETE; ?>',
                                'deleteConfirm': '<?php echo RAINBOW_LANG_DELETE_CONFIRM; ?>',
                                'colorConfirm' : '<?php echo RAINBOW_LANG_COLOR_CONFIRM; ?>'};
        </script>
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.5.0/jquery.min.js"></script>
        <script type="text/javascript" src="fashion/rainbow.js?<?php echo time(); ?>"></script>
    </head>
    <body>
        <noscript>
            <p style="font-size: 20px; padding: 5px; text-align: center;">
                <?php echo RAINBOW_LANG_JAVASCRIPT; ?>
            </p>
        </noscript>

        <div id="header">
            <ul>
                <li id="avatar">&nbsp;</li>
                <li><a href="index.php?new" id="new"><?php echo RAINBOW_LANG_NEW; ?></a></li>
                <li><a href="index.php?modified" id="modified"><?php echo RAINBOW_LANG_MODIFIED; ?></a></li>
                <li><a href="index.php?favorite" id="favorite"><?php echo RAINBOW_LANG_FAVORITES; ?></a></li>
                <li><a href="index.php?mine" id="mine"><?php echo RAINBOW_LANG_MINE; ?></a></li>
                <li>
                    <a href="index.php" id="help"><?php echo RAINBOW_LANG_HELP; ?></a>
                    <div class="box"><?php echo RAINBOW_LANG_HELP_DESCRIPTION; ?></div>
                </li>
                <li>
                    <a href="https://github.com/mpratt/Rainbow-Guestbook" style="color: #7D1F1F;"><?php echo RAINBOW_LANG_MODIFY_ME; ?></a>
                </li>
            </ul>

            <div id="create">
                <?php echo RAINBOW_LANG_CREATE; ?>
            </div>
        </div>
        <div class="clearfix"></div>
        <div id="bubbles"></div>
    </body>
</html>