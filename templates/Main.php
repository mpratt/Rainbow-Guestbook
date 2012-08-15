<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ES" lang="ES" dir="ltr">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title><?php echo RAINBOW_NAME; ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <link rel="stylesheet" href="css/bootstrap.min.css" type="text/css" />
        <link rel="stylesheet" href="css/bootstrap-response.min.css" type="text/css" />
        <link rel="stylesheet" href="css/style.css?<?php echo time(); ?>" type="text/css" />
        <script type="text/javascript">
            var url     = '<?php echo RAINBOW_URL; ?>';
            var token   = '<?php echo $_SESSION['token']; ?>';
            var myColor = '<?php echo ipColor(detectIp()); ?>';
        </script>
        <script type="text/javascript" src="js/jquery-1.7.1.min.js"></script>
        <script type="text/javascript" src="js/underscore.min.js"></script>
        <script type="text/javascript" src="js/backbone.min.js"></script>
        <script type="text/javascript" src="js/showdown.js"></script>
        <script type="text/javascript" src="js/rainbow.js?<?php echo time(); ?>"></script>
    </head>
    <body>
        <div class="navbar navbar-fixed-top">
            <div class="navbar-inner">
                <div class="container">
                <a class="brand" href="#"><?php echo RAINBOW_TITLE ?></a>
                    <ul class="nav">
                        <li><a href="#new">Mensajes Nuevos</a></li>
                        <li><a href="#recent">Mensajes Recientes</a></li>
                        <li><a href="#fav">Mensajes Favoritos</a></li>
                        <li><a href="#mine">Mis Mensajes</a></li>
                        <li><a href="#colors">Todos Los Colores</a></li>
                        <li><a href="https://github.com/mpratt/Rainbow-Guestbook">Modifícame en Github</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="container">
            <div id="context-nav-up"></div>
            <div id="content"></div>
        </div>
    </body>
</html>
