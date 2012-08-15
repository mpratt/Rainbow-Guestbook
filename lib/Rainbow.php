<?php
/**
 * Rainbow.php
 *
 * @package This file is part of the Rainbow Guestbook
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Rainbow
{
    // PDO instance container
    protected $pdo;

    /**
     * Construct
     *
     * @param object $pdo Instance of a PDO Object
     * @return void
     */
    public function __construct($pdo) { $this->pdo = $pdo; }

    /**
     * Stores a new Message in the Database
     *
     * @param string $text The body of the Message
     * @param string $color The color of the Message (Hexadecimal)
     * @return int
     */
    public function create($text, $color)
    {
        $text = $this->normalize($text);
        if (!empty($text))
        {
            $stmt = $this->pdo->prepare('INSERT INTO rainbow_messages (message, color, date) VALUES (?, ?, ?)');
            $stmt->execute(array($text, $color, date('Y-m-d')));

            return $this->pdo->lastInsertId();
        }

        return 0;
    }

    /**
     * Stores a reply to a Message in the Database
     *
     * @param int $id The id of the parent Message
     * @param string $text The body of the Message
     * @param string $color The color of the Message (Hexadecimal)
     * @return bool
     */
    public function reply($id, $text, $color)
    {
        $text = $this->normalize($text);
        if (!empty($text) && $this->exists($id))
        {
            $stmt = $this->pdo->prepare('INSERT INTO rainbow_messages (parent_id, message, color, date) VALUES (?, ?, ?, ?)');
            $stmt->execute(array($id, $text, $color, date('Y-m-d')));
            unset($stmt);

            return true;
        }

        return false;
    }

    /**
     * Checks if a parent Message exists
     *
     * @param int $id The id of the Message
     * @return bool
     */
    public function exists($id)
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(m.id) AS total
                                     FROM rainbow_messages AS m
                                     WHERE m.id = ?
                                     AND m.parent_id = 0');

        $stmt->execute(array($id));
        $rows = $stmt->fetchColumn();
        unset($stmt);

        return ($rows == 1);
    }

    /**
     * Deletes a Message and all its replies
     *
     * @param int $id Id of the Parent Message
     * @return bool
     */
    public function delete($id)
    {
        if ($this->exists($id))
        {
            $stmt = $this->pdo->prepare('DELETE FROM rainbow_messages WHERE ? IN (id, parent_id)');
            $stmt->execute(array($id));
            unset($stmt);

            return true;
        }

        return false;
    }

    /**
     * Fetches Messages ordered by recent the most recent.
     *
     * @param int $limit
     * @return array
     */
    public function fetchAll($limit = 1000)
    {
        $stmt = $this->pdo->query('SELECT m.id, m.message, m.color, m.date, COUNT(r.parent_id) AS total
                                   FROM rainbow_messages AS m
                                   LEFT JOIN rainbow_messages AS r ON (m.id = r.parent_id)
                                   WHERE m.parent_id = 0
                                   GROUP BY m.id
                                   ORDER BY m.id DESC
                                   LIMIT ' . (int) $limit);

        $messages = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
        {
            $messages[] = array('text'  => $this->output($row['message']),
                                'id'     => $row['id'],
                                'color' => $row['color'],
                                'date'  => $row['date'],
                                'replies' => $row['total']);
        }
        unset($stmt);

        return $messages;
    }

    /**
     * Fetches favorite Messages
     *
     * @param array $favorite Array with the ids of the favorite Messages
     * @param int $limit
     * @return array
     */
    public function fetchFavorite($favorites = array(), $limit = 300)
    {
        $favorites = implode(',', array_map('intval', $favorites));
        if (!empty($favorites))
            $where = ' AND m.id IN (' . $favorites . ')';
        else
            $where = ' AND m.id = 0';

        $stmt = $this->pdo->query('SELECT m.id, m.message, m.color, m.date, COUNT(r.parent_id) AS total
                                   FROM rainbow_messages AS m
                                   LEFT JOIN rainbow_messages AS r ON (m.id = r.parent_id)
                                   WHERE m.parent_id = 0 ' . $where . '
                                   GROUP BY m.id
                                   ORDER BY m.id DESC
                                   LIMIT ' . (int) $limit);

        $messages = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
        {
            $messages[] = array('text'  => $this->output($row['message']),
                                'id'     => $row['id'],
                                'color' => $row['color'],
                                'date'  => $row['date'],
                                'replies' => $row['total']);
        }
        unset($stmt);

        return $messages;
    }

    /**
     * Fetches Messages that were sent by $color
     *
     * @param string $color The color of the Message (Hexadecimal)
     * @param int $limit
     * @return array
     */
    public function fetchByColor($color, $limit = 500)
    {
        if (strlen($color) != 6 || !ctype_xdigit($color))
            return array();

        $stmt = $this->pdo->prepare('SELECT m.id, m.message, m.color, m.date
                                     FROM rainbow_messages AS m
                                     WHERE m.color = ?
                                     ORDER BY m.id DESC
                                     LIMIT ' . (int) $limit);

        $stmt->execute(array($color));
        $messages = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
        {
            $messages[] = array('text'  => $this->output($row['message']),
                                'id'     => $row['id'],
                                'color' => $row['color'],
                                'date'  => $row['date']);
        }
        unset($stmt);

        return $messages;
    }

    /**
     * Fetches recent Messages that were modified
     *
     * @param int $limit
     * @return array
     */
    public function fetchLastModified($limit = 500)
    {
        $stmt = $this->pdo->query('SELECT m.id, m.message, m.color, m.date, COUNT(r.parent_id) AS total, r.date AS rdate
                                   FROM rainbow_messages AS m
                                   LEFT JOIN (SELECT *
                                              FROM rainbow_messages
                                              WHERE parent_id > 0
                                              ORDER BY id DESC) AS r ON (m.id = r.parent_id)
                                   WHERE m.parent_id = 0
                                   GROUP BY m.id
                                   ORDER BY r.id DESC
                                   LIMIT ' . (int) $limit);

        $messages = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
        {
            $messages[] = array('text'   => $this->output($row['message']),
                                'id'     => $row['id'],
                                'color'  => $row['color'],
                                'date'   => $row['rdate'],
                                'replies' => $row['total']);
        }
        unset($stmt);

        return $messages;
    }

    /**
     * Fetches The different colors
     *
     * @param int $limit
     * @return array
     */
    public function fetchColors($limit = 500)
    {
        $stmt = $this->pdo->query('SELECT m.id, m.message, m.color, m.date, COUNT(r.parent_id) AS total, r.date AS rdate
                                   FROM rainbow_messages AS m
                                   LEFT JOIN (SELECT *
                                              FROM rainbow_messages
                                              WHERE parent_id > 0
                                              ORDER BY id DESC) AS r ON (m.id = r.parent_id)
                                   WHERE m.parent_id = 0
                                   GROUP BY m.color
                                   ORDER BY r.id DESC
                                   LIMIT ' . (int) $limit);

        $messages = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
        {
            $messages[] = array('text'   => $this->output($row['message']),
                                'id'     => $row['id'],
                                'color'  => $row['color'],
                                'date'   => $row['rdate'],
                                'replies' => $row['total']);
        }
        unset($stmt);

        return $messages;
    }

    /**
     * Fetches a Message and all his answers
     *
     * @param int $id The id of the parent Message
     * @return array
     */
    public function view($id)
    {
        $stmt = $this->pdo->prepare('SELECT m.id, m.message, m.color, m.date
                                     FROM rainbow_messages AS m
                                     WHERE ? IN (m.id, m.parent_id)
                                     ORDER BY m.id ASC');

        $messages = array();
        $stmt->execute(array($id));
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
        {
            $messages[] = array('text'  => $this->output($row['message'], true),
                                'id'     => $row['id'],
                                'color' => $row['color'],
                                'date'  => $row['date']);
        }
        unset($stmt);

        return $messages;
    }

    /**
     * Sanitizes the $text for output in the browser
     *
     * @param string $text
     * @param bool   $newLines Converts new lines to br
     * @return string
     */
    protected function output($text, $newLines = false)
    {
        $text = stripslashes(htmlspecialchars(strip_tags($text), ENT_QUOTES, 'UTF-8', false));

        if ($newLines)
            $text = nl2br($text);

        return $text;
    }

    /**
     * Normalizes/sanitizes the body of a Message.
     *
     * @param string $text
     * @return string
     */
    protected function normalize($text)
    {
        $text = trim($text);
        if (strlen($text) > 1000)
            $text = substr($text, 0, 1000);

        return $text;
    }
}

?>
