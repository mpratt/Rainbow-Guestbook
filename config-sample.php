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

if (!defined('RAINBOW')) {
    header('Location: index.php');
    die();
}

// Database Settings
define('RAINBOW_DB_ENGINE', 'mysql');
define('RAINBOW_DB_HOST', 'your Host');
define('RAINBOW_DB_NAME', 'your DB Name');
define('RAINBOW_DB_USER', 'your User');
define('RAINBOW_DB_PASS', 'your password');

// Enable everyone to delete bubbles ?
define('RAINBOW_ENABLE_DELETE', false);

// Language Settings
define('RAINBOW_LANG', 'es');
define('RAINBOW_LANG_TITLE', 'Rainbow Guestbook - El arcoiris en tu navegador');
define('RAINBOW_LANG_LOADING', 'Cargando..');
define('RAINBOW_LANG_CREATE', 'Escribe tu Mensaje');
define('RAINBOW_LANG_CREATE_BUTTON', 'Enviar Mensaje');
define('RAINBOW_LANG_NEW', 'Nuevas');
define('RAINBOW_LANG_MODIFIED', 'Activas');
define('RAINBOW_LANG_FAVORITES', 'Favoritas');
define('RAINBOW_LANG_VIEW', 'Ver');
define('RAINBOW_LANG_MINE', 'Mis Mensajes');
define('RAINBOW_LANG_HELP', 'Ayuda');
define('RAINBOW_LANG_MODIFY_ME', 'Modificame!');
define('RAINBOW_LANG_HELP_DESCRIPTION', 'Puedes crear un mensaje nuevo o hacer click sobre los mensajes para responderlos');
define('RAINBOW_LANG_REPLY', 'Enviar Respuesta');
define('RAINBOW_LANG_REPLIES', 'Respuestas');
define('RAINBOW_LANG_DELETE', 'Borrar');
define('RAINBOW_LANG_DELETE_CONFIRM', 'Estas seguro que quieres borrar este Mensaje? ' . (RAINBOW_ENABLE_DELETE ? '' : '(Desactivado)'));
define('RAINBOW_LANG_JAVASCRIPT', 'Es Necesario tener Javascript Activado!!');
define('RAINBOW_LANG_EMPTY', 'No se encontraron Mensajes');
define('RAINBOW_LANG_ADD_FAVORITE', 'Guardar en mis mensajes favoritos');
define('RAINBOW_LANG_REMOVE_FAVORITE', 'Remover de mis mensajes favoritos');
define('RAINBOW_LANG_COLOR_CONFIRM', 'Quieres ver todos los mensajes recientes por este color?');
define('RAINBOW_LANG_ERROR_BACK', 'Volver al inicio');
define('RAINBOW_LANG_BAD_BROWSER', 'Estas usando un navegador obsoleto. Esta página necesita uno más moderno! (chrome/Firefox/IE9/Opera)');

// Debug, should always be false!
define('RAINBOW_DEBUG', false);
?>