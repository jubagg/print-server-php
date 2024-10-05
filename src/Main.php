<?php

namespace PrintServer;

use DateTime;
use Exception;
use DateTimeZone;
use Mike42\Escpos\Printer;
use React\Http\HttpServer;
use React\Socket\SocketServer;
use React\Http\Message\Response;
use Psr\Http\Message\ServerRequestInterface;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;

const DEFAULT_PORT = 8080;
const DEFAULT_IP = "127.0.0.1";
class Main
{
    public ?string $ip = null; 
    public ?int $port = null; 

    public function start_server(){

        $this->ip = readline("Ingrese IP del servidor(default=". DEFAULT_IP ."):");
        $this->port = (int) readline("Ingrese puerto del servidor(default=". DEFAULT_PORT ."):");

        $full_base_path = $this->ip ? $this->ip :  DEFAULT_IP;
        $full_base_path .= ":";
        $full_base_path .= $this->port ? $this->port : DEFAULT_PORT;
        
        $http = new HttpServer(function (ServerRequestInterface $request) {
                    
            $body = json_decode((string)$request->getBody());
            
            $lineCharacters = $body->pos->printer_length_line ?? null;
            
            $row = function ($cantidad, $precioUnitario, $total, $lineCharacters)  {
            
                // Crear la parte izquierda: "cantidad x precio"
                $leftPart = $cantidad . ' x $' . number_format($precioUnitario, 2);
            
                // Crear la parte derecha: total
                $rightPart = '$' . number_format($total, 2);
            
                // Calcular los espacios intermedios necesarios
                $spaces = $lineCharacters - strlen($leftPart) - strlen($rightPart);
            
                // Asegurarse de que haya suficiente espacio para las partes
                if ($spaces < 0) {
                    throw new Exception('El texto es demasiado largo para caber en una línea de 32 caracteres.');
                }
            
                // Rellenar con espacios y concatenar
                $line = $leftPart . str_repeat(' ', $spaces) . $rightPart;
            
                return "{$line}\n";
            };

            if($body != null){

                $time = new DateTime('now', new DateTimeZone('America/Argentina/Buenos_Aires'));
                
                $connector = new FilePrintConnector($body->pos->name_printer);
                $printer = new Printer($connector);
                $printer->setTextSize(3, 2);
                
                $printer->setJustification(Printer::JUSTIFY_CENTER);
                $printer->text($body->company."\n");
                $printer->text("\n");
                $printer->setTextSize(2, 1);
                $printer->setJustification(Printer::JUSTIFY_LEFT);
                $printer->text("Tienda: ".$body->shop."\n");
                $printer->text("PV: ".$body->pos->point_number."\n");
                $printer->setTextSize(1, 1);
                $printer->text($time->format('H:m:i d-M-y'));
                $printer->text("\n");
                $printer->setTextSize(1, 1);
                foreach($body->details as $detail){
                    $printer->setFont(Printer::FONT_B);
                    $printer->text($detail->name."\n");
                    $printer->setFont(Printer::FONT_A);
                    $printer->text($row($detail->quantity, $detail->amount, $detail->final_price, $lineCharacters)."\n");
                }

                $printer->setJustification(Printer::JUSTIFY_RIGHT);
                $printer->setTextSize(2, 1);
                $printer->text("TOTAL: $" .round($body->total, 2)."\n");
                $printer->text("\n");
                $printer->text("\n");
                $printer->text("\n");
                $printer->feed();
                $printer->close();
            }

            $corsHeaders = [
                'Access-Control-Allow-Origin' => '*',  // Puedes limitarlo a un dominio específico
                'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
                'Access-Control-Allow-Headers' => 'Content-Type, Access-Control-Allow-Origin, Access-Control-Allow-Methods, Access-Control-Allow-Headers',
            ];

            return new Response(
                204,
                $corsHeaders,
                null 
            );
        });

        echo "Server listen on {$full_base_path}";

        $socket = new SocketServer($full_base_path);
        $http->listen($socket);
    }
    
}



