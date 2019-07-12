<?php
use App\Http\Controllers\BotManController;

use Illuminate\Foundation\Inspiring;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Conversations\Conversation;
use Illuminate\Support\Facades\Log;



$botman = resolve('botman');

$botman->hears('/hola|hola|/Hola|Hola', BotManController::class.'@helloConversation');

$botman->hears('/getChat|getChat|/getchat|getchat', function($bot) {
    $answer = $bot->getMessage()->getRecipient();
    $bot->reply("Aquí tienes el ID del grupo :");
    $bot->reply($answer);
});

$botman->hears('/ayuda|ayuda|Ayuda|/Ayuda', function ($bot) {
    $bot->reply("Inicia una conversación escribiendo /hola, donde puedes :
       1. Publicar nuevos eventos.
       2. Consultar los eventos semanales.
    -----------------------------------------------------
    O /getChat si quieres obtener el ID del grupo. (Nunca sabes cuando puede serte útil)");
});

$botman->fallback(function ($bot) {
    $bot->reply("Perdona, no sé a qué te refieres, consulta los comandos escribiendo /ayuda");
});
