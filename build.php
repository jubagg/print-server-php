<?php

$pharFile = 'printer-server.phar';

// Si ya existe, lo borramos
if (file_exists($pharFile)) {
    unlink($pharFile);
}

// Creamos el archivo .phar
$phar = new Phar($pharFile);
$phar->startBuffering();

$defaultStub = $phar->createDefaultStub('src/index.php');

// Empaquetamos todos los archivos del directorio del proyecto (src y vendor)
$phar->buildFromDirectory(__DIR__ . '/src');
$phar->buildFromDirectory(__DIR__ . '/vendor');

$stub = "#!/usr/bin/env php \n" . $defaultStub;
// Establecemos el archivo de entrada (index.php en este caso)
$phar->setStub($stub);  // Cambia la ruta si no está en src/

$phar->stopBuffering();

chmod(__DIR__ . "/{$pharFile}", 0770);

echo "$pharFile successfully created" . PHP_EOL;

echo "Archivo .phar creado exitosamente\n";