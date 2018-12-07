<?php
use App\Http\Controllers\BotManController;

use Illuminate\Foundation\Inspiring;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Conversations\Conversation;



$botman = resolve('botman');


$botman->hears('hello|/hi', function ($bot) {
    $bot->reply('Hola! 👋');
});

$botman->hears('hola', BotManController::class.'@helloConversation');

//$botman->hears('hola', BotManController::class.'@helloConversation');

$botman->fallback(function ($bot) {
      $bot->reply("Perdona, no sé a qué te refieres, consulta los comandos escribiendo 'ayuda'");
  });
