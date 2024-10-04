<?php

namespace PrintServer;

$file = 'database.json';

// Leer datos del archivo
function readDatabase($file) {
    if (!file_exists($file)) {
        file_put_contents($file, json_encode([]));  // Crear archivo si no existe
    }
    $json = file_get_contents($file);
    return json_decode($json, true);
}

// Guardar datos en el archivo
function saveDatabase($file, $data) {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}

// Insertar documento
function insertDocument($file, $document) {
    $data = readDatabase($file);
    $data[] = $document;
    saveDatabase($file, $data);
}

// Buscar documentos
function findDocuments($file, $criteria) {
    $data = readDatabase($file);
    return array_filter($data, function($doc) use ($criteria) {
        foreach ($criteria as $key => $value) {
            if (!isset($doc[$key]) || $doc[$key] != $value) {
                return false;
            }
        }
        return true;
    });
}