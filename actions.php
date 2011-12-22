<?php
/**
 * actions.php
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
    require(dirname(__FILE__) . '/sources/Rainbow.class.php');

    if (RAINBOW_DEBUG)
        error_reporting(E_ALL | E_STRICT);
    else
        error_reporting(0);

    session_start();

    try
    {
        $pdo = new PDO(RAINBOW_DB_ENGINE . ':host=' . RAINBOW_DB_HOST . ';dbname=' . RAINBOW_DB_NAME . ';charset=UTF-8', RAINBOW_DB_USER, RAINBOW_DB_PASS);
        if (RAINBOW_DEBUG) { $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); }
        $pdo->exec('SET NAMES utf8');
    }
    catch(PDOException $e) { die('Database Error!'); }

    // Needed information
    $myColor  = ipColor(detectIp());
    $rainbow  = new Rainbow($pdo);

    // Security Check
    if (empty($_GET['token']) || empty($_GET['do']) || empty($_SESSION['token']) || $_SESSION['token'] != $_GET['token'])
        redirect_to('index.php');

    // Delegate Actions
    switch ($_GET['do'])
    {
        // Create a new message
        case 'create':
            $id = 0;
            if (isset($_POST['quote']) && trim(RAINBOW_LANG_CREATE) != trim($_POST['quote']))
                $id = $rainbow->create($_POST['quote'], $myColor);

             redirect_to('index.php?view=' . (int) $id);
        break;

        // Send a reply to a message
        case 'reply':
            if (isset($_POST['reply']) && isset($_POST['id']) && $rainbow->exists($_POST['id']))
                $rainbow->reply($_POST['id'], $_POST['reply'], $myColor);

                redirect_to('index.php?view=' . (int) $_POST['id']);
        break;

        // Delete a message (if enabled on the config file)
        case 'delete':
            if (RAINBOW_ENABLE_DELETE && isset($_GET['id']) && $rainbow->exists($_GET['id']))
                $rainbow->delete($_GET['id']);
        break;

        // Manage Bookmark Actions
        case 'favorite':
            if (!empty($_GET['subaction']) && !empty($_GET['id']))
            {
                require(dirname(__FILE__) . '/sources/RainbowBookmarks.class.php');
                $cookie = (!empty($_COOKIE['rainbow_bookmarks']) ? $_COOKIE['rainbow_bookmarks'] : array());
                $bookmarks = new RainbowBookmarks($cookie);

                if ($_GET['subaction'] == 'add')
                {
                    $bookmarks->setBookmark($_GET['id']);
                    redirect_to('index.php?view=' . (int) $_GET['id']);
                }
                else
                    $bookmarks->deleteBookmark($_GET['id']);

                redirect_to('index.php?favorite');
            }
        break;

        // Fetches all the messages
        case 'getAll':
            header('Content-type: application/json');
            die(json_encode($rainbow->fetchAll()));
        break;

        // Fetches all the last modified
        case 'getActive':
            header('Content-type: application/json');
            die(json_encode($rainbow->fetchLastModified()));
        break;

        // Fetches all bookmarked messages
        case 'getFavorite':
            require(dirname(__FILE__) . '/sources/RainbowBookmarks.class.php');
            $cookie = (!empty($_COOKIE['rainbow_bookmarks']) ? $_COOKIE['rainbow_bookmarks'] : array());
            $bookmarks = new RainbowBookmarks($cookie);

            header('Content-type: application/json');
            die(json_encode($rainbow->fetchFavorite($bookmarks->getBookmarks())));
        break;

        // Fetches all the messages by a color
        case 'getColor':
            header('Content-type: application/json');
            if (!empty($_GET['color']))
                die(json_encode($rainbow->fetchByColor($_GET['color'])));
            else
                die(json_encode(array()));
        break;

        // Fetches all the messages for an id
        case 'view':
            header('Content-type: application/json');
            $replies = $rainbow->view($_GET['id']);
            if (!empty($replies) && !empty($_GET['shift']) && $_GET['shift'] == 1)
                array_shift($replies);

            die(json_encode($replies));
        break;
    }

    // If we get to this point, redirect to index!
    redirect_to('index.php');
?>