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

    require(dirname(__FILE__) . '/config.php');
    require(dirname(__FILE__) . '/lib/Utils.php');
    require(dirname(__FILE__) . '/lib/Router.php');
    require(dirname(__FILE__) . '/lib/Rainbow.php');
    require(dirname(__FILE__) . '/lib/RainbowBookmarks.php');

    try
    {
        $pdo = new PDO(RAINBOW_DB_ENGINE . ':host=' . RAINBOW_DB_HOST . ';dbname=' . RAINBOW_DB_NAME . ';charset=UTF-8',
                       RAINBOW_DB_USER, RAINBOW_DB_PASS);

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec('SET NAMES utf8');

        // Instantiate the Rainbow Object
        $rainbow = new Rainbow($pdo);
    }
    catch(PDOException $e) { die('Could not connect to Database'); }

    session_start();
    if (!isset($_SESSION['token']))
        $_SESSION['token'] = md5(time() . session_id() . mt_rand(1000, 5000));

    $path = str_replace(parse_url(RAINBOW_URL, PHP_URL_PATH), '', $_SERVER['REQUEST_URI']);
    $router = new Router($path, $_SERVER['REQUEST_METHOD']);

    // Map the index controller.
    $router->map('GET', '/', function (){
        ob_start();
        require_once(dirname(__FILE__) . '/templates/Main.php');
        ob_end_flush();
    });

    // Get all the messages.
    $router->map('GET', '/api/getAll/', function () use (&$rainbow) {
        echo json_encode($rainbow->fetchAll());
    }, array('Content-type' => 'application/json'));

    // Get the recent modified
    $router->map('GET', '/api/getModified/', function () use (&$rainbow) {
        echo json_encode($rainbow->fetchLastModified());
    }, array('Content-type' => 'application/json'));

    // Get the Favorites
    $router->map('GET', '/api/getBookmarks/', function () use (&$rainbow) {
        $cookie = (!empty($_COOKIE['rainbow_bookmarks']) ? $_COOKIE['rainbow_bookmarks'] : array());
        $bookmarks = new RainbowBookmarks($cookie);
        echo json_encode($rainbow->fetchFavorite($bookmarks->getBookmarks()));
    }, array('Content-type' => 'application/json'));

    // Gets all the messages of the thread with $id
    $router->map('GET', '/api/getThread/:id/', function ($id) use (&$rainbow) {
        echo json_encode($rainbow->view($id));
    }, array('Content-type' => 'application/json'));

    // Gets all the colors
    $router->map('GET', '/api/getColors/', function () use (&$rainbow) {
        echo json_encode($rainbow->fetchColors());
    }, array('Content-type' => 'application/json'));

    // Gets all the messages with $color
    $router->map('GET', '/api/getColor/:color/', function ($color) use (&$rainbow) {
        echo json_encode($rainbow->fetchByColor($color));
    }, array('Content-type' => 'application/json'));

    // Adds a message to the bookmark cookie
    $router->map('GET', '/api/bookmark/add/:id', function ($id) {
        $cookie = (!empty($_COOKIE['rainbow_bookmarks']) ? $_COOKIE['rainbow_bookmarks'] : array());
        $bookmarks = new RainbowBookmarks($cookie);
        $result = $bookmarks->setBookmark($id);
        echo json_encode(array('status' => (bool) $result));
    }, array('Content-type' => 'application/json'));

    // Removes a message from the bookmark cookie
    $router->map('GET', '/api/bookmark/delete/:id', function ($id) {
        $cookie = (!empty($_COOKIE['rainbow_bookmarks']) ? $_COOKIE['rainbow_bookmarks'] : array());
        $bookmarks = new RainbowBookmarks($cookie);
        $result = $bookmarks->deleteBookmark($id);
        echo json_encode(array('status' => (bool) $result));
    }, array('Content-type' => 'application/json'));

    // Creates a new Message
    $router->map('POST', '/api/new/', function () use (&$rainbow) {
        $id = 0;
        $color  = ipColor(detectIp());
        $data = json_decode(file_get_contents('php://input'), true);
        if (!empty($data['message']))
        {
            if (!empty($data['message']) && $data['token'] == $_SESSION['token'])
                $id = $rainbow->create($data['message'], $color);
        }

        echo json_encode(array('id' => $id));

    }, array('Content-type' => 'application/json'));

    // Replies a Message
    $router->map('POST', '/api/reply/:id', function ($id) use (&$rainbow) {
        $color  = ipColor(detectIp());
        $data = json_decode(file_get_contents('php://input'), true);
        if (!empty($data['message']))
        {
            if (!empty($data['message']) && $data['token'] == $_SESSION['token'])
                $rainbow->reply($id, $data['message'], $color);
        }

        echo json_encode(array('id' => (int) $id));

    }, array('Content-type' => 'application/json'));

    // Deletes a Message with an Id
    $router->map('POST', '/api/delete/:id/', function ($id) use (&$rainbow) {
        $result = false;
        if (!empty($_POST['pass']) && $_POST['pass'] == RAINBOW_DELETE_PASSWORD)
            $result = $rainbow->delete($id);

        echo json_encode(array('status' => (bool) $result));
    }, array('Content-type' => 'application/json'));

    // Kickstart this shit
    $router->run();
?>
