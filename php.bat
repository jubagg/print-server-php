@echo off
setlocal enabledelayedexpansion

:: Verificar si se está ejecutando como administrador
openfiles >nul 2>&1
if %errorlevel% neq 0 (
    echo Por favor, ejecute este script como administrador.
    exit /b
)

:: Definir la versión de PHP y la carpeta de destino
set PHP_VERSION=8.2.24
set PHP_FOLDER=C:\php
set PHP_URL=https://windows.php.net/downloads/releases/php-%PHP_VERSION%-win64-vs16.zip
set PHP_ZIP=php-%PHP_VERSION%-win64.zip

:: Descargar PHP si no existe
if not exist "%PHP_ZIP%" (
    echo Descargando PHP %PHP_VERSION%...
    powershell -Command "Invoke-WebRequest -Uri '%PHP_URL%' -OutFile '%PHP_ZIP%'"
)

:: Crear carpeta de destino
if not exist "%PHP_FOLDER%" (
    echo Creando la carpeta de PHP en %PHP_FOLDER%...
    mkdir %PHP_FOLDER%
)

:: Descomprimir PHP
echo Descomprimiendo PHP...
powershell -Command "Expand-Archive -Path '%PHP_ZIP%' -DestinationPath '%PHP_FOLDER%' -Force"

:: Copiar archivos descomprimidos al directorio principal de PHP
echo Configurando PHP...
move "%PHP_FOLDER%\php-%PHP_VERSION%-win64-vs16\*" "%PHP_FOLDER%\"

:: Eliminar archivos temporales
rd /s /q "%PHP_FOLDER%\php-%PHP_VERSION%-win64-vs16"
del "%PHP_ZIP%"

:: Configurar php.ini
echo Creando archivo php.ini...
copy "%PHP_FOLDER%\php.ini-development" "%PHP_FOLDER%\php.ini"

:: Añadir PHP al PATH del sistema
echo Añadiendo PHP al PATH del sistema...
setx PATH "%PHP_FOLDER%;%PATH%" /M

:: Verificar la instalación de PHP
echo Verificando la instalación de PHP...
php -v

echo PHP ha sido instalado y configurado correctamente.
pause