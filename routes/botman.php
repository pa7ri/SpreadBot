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

$botman->hears('ayuda|/ayuda|Ayuda|/Ayuda', function ($bot) {
  $bot->reply("Este bot informa de eventos de caracter feminista. Para empezar la interacción, saludale '/hola'");
});

$botman->fallback(function ($bot) {
   $bot->reply("Perdona, no sé a qué te refieres, consulta los comandos escribiendo '/ayuda'");
});
