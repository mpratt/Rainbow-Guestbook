Rainbow Guestbook
=================
Rainbow Guestbook es un Guestbook anónimo con algunas funcionalidades tipicas de un foro, como
crear mensajes nuevos y responderlos. Por que anónimo? Porque Rainbow no guarda ningún dato
comprometedor, en la base de datos solo quedá la fecha y el mensaje que se haya escrito.

Su nombre se basa en el hecho de que cada mensaje que el visitante escriba tiene un color único,
pues color es determinado por el hash sha1 del Ip del visitante.

Rainbow usa Backbone.js, (Jquery) y solo funciona con navegadores modernos, por ejemplo Internet explorer 7
tiene algunas dificultades mostrando correctamente el contenido de los mensajes. En otros navegadores como
Google Chrome, Firefox y Opera no se han encontrado mayores dificultades.

Esta aplicación permite:

- Crear y responder Mensajes
- Guardar una lista de tus mensajes favoritos
- Ver los mensajes mas recientes y activos

Los últimos mensajes aparecen directamente al iniciar la aplicación, crear y responder mensajes es
una tarea fácil e intuitiva.

Un demo vale más que mil palabras, asi que pruebelo:

- [DEMO](http://www.michael-pratt.com/Lab/rainbow/index.php)

Requerimientos
==============
- PHP 5 (con PDO)
- MySQL >= 5

Instalación
===========
- Abrir el archivo config-sample.php y modificarlo segun sus necesidades.
- Renombrar config-sample.php a config.php
- Ejecutar el archivo install.php en el browser
- ???
- Profit!

Para desinstalar solo hay que borrar la tabla 'rainbow_messages' de la base de datos.

Licencia
========
Esta aplicación esta protegida bajo la licencia MIT.
El archivo LICENSE contiene la licencia completa.

Autor
=====
Michael Pratt <pratt@hablarmierda.net>


[Página Personal](http://www.michael-pratt.com)
