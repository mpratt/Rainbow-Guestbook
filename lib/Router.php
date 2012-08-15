<?php
/**
 * Router.php
 *
 * @package This file is part of the Rainbow Guestbook
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Router
{
    protected $requestPath;
    protected $requestMethod;
    protected $rules   = array();
    protected $headers = array();

    /**
     * Construct
     * @param string  $path
     * @param string  $method
     * @return void
     */
    public function __construct($path, $method)
    {
        $this->requestPath = '/' . trim(str_replace('index.php', '', $path), '/');
        $this->requestMethod = strtolower($method);
    }

    /**
     * Maps a route in the registry
     * @param mixed  $method A string with the request method for the rule Or an array with all the request methods.
     * @param string $rule The Rule that is going to be used
     * @param callback  $callback the function to call
     * @param array  $headers An array with the headers
     * @return void
     */
    public function map($method, $rule, $callback, $headers = array('Content-Type' => 'text/html; charset=UTF-8'))
    {
        if (empty($rule) || trim($rule) == '')
            return ;

        if (strlen($rule) > 1)
            $rule = rtrim($rule, '/');

        if (is_array($method))
        {
            foreach ($method as $m)
                $this->map($rule, $conditions, $m, $overwrite);
        }
        else
        {
            $method = trim(strtolower($method));
            if (!in_array($method, array('get', 'post', 'put', 'delete', 'head', 'options')))
                throw new Exception('Mapping wrong Request Method ' . $method);

            if (isset($this->rules[$rule][$method]))
                throw new Exception('Mapping Error, The rule ' . $rule . ' with ' . $method . ' was already defined');

            if (!is_callable($callback))
                throw new Exception('The callback function is not callable!');

            $this->rules[$rule][$method] = $callback;
            $this->headers[$rule] = $headers;
        }
    }

    /**
     * Finds a match in the rules and executes the callback function.
     *
     * @return bool
     */
    public function run()
    {
        if (!empty($this->requestPath))
        {
            $rules = array_keys($this->rules);
            foreach ($rules as $rule)
            {
                $regex = preg_replace_callback('~:([a-z_]+)~i', function($matches){
                            list(,$name) = $matches;
                            return '(?P<' . $name . '>[\w0-9\-\_\+\;\.\%]+)';
                         }, $rule);

                if (preg_match('~^' . $regex . '$~', $this->requestPath, $args))
                {
                    if (!empty($this->rules[$rule][$this->requestMethod]))
                    {
                        if (!empty($this->headers[$rule]) && !headers_sent())
                        {
                            foreach ($this->headers[$rule] as $type => $content)
                                header($type . ': ' . $content);
                        }

                        array_shift($args);
                        call_user_func_array($this->rules[$rule][$this->requestMethod], $args);
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
?>
