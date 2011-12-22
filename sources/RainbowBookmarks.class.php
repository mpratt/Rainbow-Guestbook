<?php
/**
 * RainbowBookmarks.class.php
 * A class that bookmarks rainbow messages in a cookie
 *
 * @package This file is part of the Rainbow Guestbook
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
if (!defined('RAINBOW')) {
     header('Location: index.php');
     die();
}

class RainbowBookmarks
{
    protected $cookie;

    /**
     * Construct
     *
     * @param array $cookie $_COOKIE['rainbow_bookmarks'];
     * @return void
     */
    public function __construct($cookie)
    {
        $this->cookie = $this->cleanCookie(unserialize($cookie));

        if (!is_array($this->cookie))
            $this->cookie = array();
    }

    /**
     * Reads $this->cookie and returns an array with
     * the id of the Messages found.
     *
     * @return array
     */
    public function getBookmarks()
    {
        return $this->cookie;
    }

    /**
     * Recursively sanitizes an array
     *
     * @param array $cookie The cookie that stores the bookmarks
     * @return array
     */
    protected function cleanCookie($cookie = array())
    {
        if (!is_array($cookie))
            return intval(trim($cookie));

        if (!empty($cookie))
        {
            foreach ($cookie as $k => $v)
                $cookie[$k] = $this->cleanCookie($v);
        }
        else
            $cookie = array();

        return array_unique($cookie);
    }

    /**
     * Stores the id of a Message in the favorites cookie
     *
     * @param int $id The Id of the Message
     * @return bool
     */
    public function setBookmark($id)
    {
        if (!is_numeric($id) || in_array($id, $this->cookie))
            return false;

        $this->cookie[] = (int) $id;

        // Store cookies for a year!
        return setcookie('rainbow_bookmarks', serialize(array_reverse($this->cookie)), (time() + 86400 * 365), '/');
    }

    /**
     * Removes a Message from the favorites cookie
     *
     * @param int $id The Id of the Message
     * @return bool
     */
    public function deleteBookmark($id)
    {
        if (!is_numeric($id) || !in_array($id, $this->cookie))
            return false;

        $newCookie = array_flip($this->cookie);
        unset($newCookie[$id]);

        // Store cookies for a year!
        return setcookie('rainbow_bookmarks', serialize(array_flip($newCookie)), (time() + 86400 * 365), '/');
    }
}

?>