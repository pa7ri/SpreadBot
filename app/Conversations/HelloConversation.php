<?php

namespace App\Conversations;

use Illuminate\Foundation\Inspiring;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\BotMan\Messages\Outgoing\Message;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Conversations\Conversation;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class HelloConversation extends Conversation
{
  protected $place;
  protected $date;
  protected $day;
  protected $month;
  protected $year;
  protected $time;

  protected $title;
  protected $description;
  protected $show = 0;

    public function initConversation()
    {
      $question = Question::create('Hola, ¿qué quieres hacer?')
                  ->fallback('Vaya, se ha producido un error, vuelve a intentarlo')
                  ->callbackId('init_conver')
                  ->addButtons([
                      Button::create('Crear nuevo evento')->value('create'),
                      Button::create('Consultar eventos')->value('show'),
                      Button::create('Cancelar')->value('cancel'),
                    ]);
      return $this->ask($question, function (Answer $answer) {
          if ($answer->isInteractiveMessageReply()) {
              if ($answer->getValue() === 'create') {
                  $this->askDate();
              } else if ($answer->getValue() === 'show') {
                  $this->getEventsFromDB();
              } else {
                $this->say('Hasta pronto 😄');
              }
          }
      });
    }

    public function askDate()
      {
        $question = Question::create('Primero elige una fecha para tu evento : ')
                ->fallback('Vaya, se ha producido un error, vuelve a intentarlo')
                ->callbackId('ask_year')
                ->addButtons([
                  Button::create('2019')->value('2019'),
                  Button::create('2020')->value('2020'),
                  Button::create('2021')->value('2021')
                 ]);

            $this->ask($question, function (Answer $answer) {
                if ($answer->isInteractiveMessageReply()) {
                  if($answer->getValue() === 'cancel'){
                    $this->say('Hasta pronto 😄');
                  } else {
                    $this->year = $answer->getText();
                    $this->askMonth();
                  }
                }
            });
      }
      public function askMonth()
        {
          $keyboard = [
                      ['Enero', 'Febrero', 'Marzo', 'Abril'],
                      ['Mayo', 'Junio', 'Julio', 'Agosto'],
                      ['Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
                  ];

                  $this->ask('Mes : ',
                      function (Answer $answer) {
                        $this->month = $answer->getText();
                        $this->say($this->month);
                          $this->askDay();
                      }, ['reply_markup' => json_encode([
                          'keyboard' => $keyboard,
                          'one_time_keyboard' => true,
                          'resize_keyboard' => true
                      ])]
                  );
        }
        public function askDay()
          {

              $keyboard = [
                          ['1', '2', '3', '4', '5', '6', '7'],
                          ['8', '9', '10', '11', '12', '13', '14'],
                          ['15', '16', '17', '18', '19', '20', '21'],
                          ['22', '23', '24', '25', '26', '27', '28'],
                          [' ', ' ', '29', '30', '31', ' ', ' '],
                      ];

                $this->ask('Día : ',
                    function (Answer $answer) {
                      $this->day = $answer->getText();
                      $this->say($this->day);
                      $this->date = $this->day.'/'.$this->month.'/'.$this->year;
                      $this->askTitle();
                    }, ['reply_markup' => json_encode([
                        'keyboard' => $keyboard,
                        'one_time_keyboard' => true,
                        'resize_keyboard' => true
                    ])]
                );
          }

    public function askTitle()
        {
            $this->ask('Vamos a darle forma al evento, empieza por ponerle un título :', function(Answer $answer) {
                $this->title = $answer->getText();

                $this->askDescription();
            });
        }

    public function askDescription()
        {
            $this->ask('Ahora describe en qué consiste el evento : ', function(Answer $answer) {
                $this->description = $answer->getText();
                $this->askplace();
            });
        }


    public function askplace()
        {
            $this->ask('Ya casi está 👏 ¿dónde va a ocurrir el evento? : ', function(Answer $answer) {
                $this->place = $answer->getText();
                $this->askTime();
            });
        }

    public function askTime()
        {
            $this->ask('Finalmente, añade una hora : ', function(Answer $answer) {
                $this->time = $answer->getText();
                $this->showEventResult();
            });
        }

    public function showEventResult()
        {
          $question = Question::create("Perfecto, así quedaría tu evento : \n".
                  "----------------------------------------------------- \n".
                  "<b> Título </b>             : ".$this->title."\n".
                  "<b> Descripción </b>  : ".$this->description."\n".
                  "<b> Lugar </b>             : ".$this->place."\n".
                  "<b> Fecha </b>            : ".$this->date."\n".
                  "<b> Hora </b>              : ".$this->time."\n".
                  "----------------------------------------------------- \n".
                  "Está listo para ser compartido con el mundo.")
                  ->fallback('Vaya, se ha producido un error, vuelve a intentarlo')
                  ->callbackId('show_result')
                  ->addButtons([
                      Button::create('Publicar')->value('register'),
                      Button::create('Cancelar')->value('cancel')
                    ]);

              $this->ask($question, function (Answer $answer) {
                  if ($answer->isInteractiveMessageReply()) {
                    if ($answer->getValue() === 'register') {
                      $this->addEventToDB();
                      $this->say('✅ Evento publicado con éxito ');
                    } else {
                      $this->say('❌ Evento no publicado');
                    }
                  }
              }, [ 'parse_mode' => 'HTML']);
        }

    public function addEventToDB()
    {
      DB::table('events')->insert([
            'title' => $this->title,
            'description' => $this->description,
            'place' => $this->place,
            'date' => $this->date,
            'time' => $this->time,
        ]);
    }

    public function getEventsFromDB(){
      $events = DB::table('events')->paginate(7);
      foreach ($events as $ev ){
        $message="<b> $ev->title </b> \n".
        "----------------------------------------------------- \n".
        "<b> Descripción </b>  : ".$ev->description."\n".
        "<b> Lugar </b>             : ".$ev->place."\n".
        "<b> Fecha </b>            : ".$ev->date."\n".
        "<b> Hora </b>              : ".$ev->time."\n".
        "----------------------------------------------------- \n";
        $this->say($message, [ 'parse_mode' => 'HTML']);
      }
    }

    /**
     * Start the conversation
     */
    public function run()
    {
        $this->initConversation();
    }
}
