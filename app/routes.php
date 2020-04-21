<?php


$app->group('', function() use ($app, $container) {
	// site
	$app->get('/', 'HomeController:index');		
	$app->post('/registrar_usuario', 'HomeController:registrar_usuario');
	$app->get('/ver_usuarios', 'HomeController:ver_usuarios');
	$app->delete('/borrar_usuario', 'HomeController:borrar_usuario');
	$app->put('/editar_usuario', 'HomeController:editar_usuario');
});
