<?php
use App\Http\Controllers\BotManController;

use Illuminate\Foundation\Inspiring;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Conversations\Conversation;
use Illuminate\Support\Facades\Log;



$botman = resolve('botman');


  Log::error('ESTOY ESCUCHANDO');

$botman->hears('hello|/hi', function ($bot) {
  Log::error('ESCUCHO HOLIIII');
    $bot->reply('Hola! 👋');
});

$botman->hears('/hola', BotManController::class.'@helloConversation');

//$botman->hears('hola', BotManController::class.'@helloConversation');

$botman->fallback(function ($bot) {
      $bot->reply("Perdona, no sé a qué te refieres, consulta los comandos escribiendo 'ayuda'");
  });
