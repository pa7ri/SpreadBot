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
  protected $date_millis;
  protected $day;
  protected $month;
  protected $month_format;
  protected $year;
  protected $time;

  protected $title;
  protected $description;
  protected $show = 0;

  protected $current_time;
  protected $one_week_time = 604800; //1 week in seconds

  protected $chatId;

    public function initConversation()
    {
      $this->current_time = strtotime("now");
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
              } else if ($answer->getValue() === 'chatId') {
                  $this->getChatId();
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
                  Button::create('2021')->value('2021'),
                  Button::create('2022')->value('2022')
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
                        $this->getMonthFormat($answer);
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
                      $this->date = $this->day.' '.$this->month_format.' '.$this->year; //$this->day.' '.$this->month_format.' '.$this->year;
                      $this->date_millis = strtotime($this->date);
                      $this->date = $this->day.' de '.$this->month.' de '.$this->year;
                      if( $this->date_millis < $this->current_time) {
                        $this->say('Parece que la fecha introducida : '.$this->date.' no es válida, inténtelo de nuevo');
                      } else {
                        $this->askTitle();
                      }
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

    public function addEventToDB() {
      DB::table('events')->insert([
            'title' => $this->title,
            'description' => $this->description,
            'place' => $this->place,
            'date' => $this->date_millis,
            'time' => $this->time,
        ]);
    }

    public function getEventsFromDB() {
      $this->deletePastEventsFromDB();
      $events = DB::table('events')
            ->whereBetween('date', [$this->current_time, $this->current_time + $this->one_week_time])
            ->orderBy('date','asc')
            ->get();
      if($events->count() == 0) {
        $this->say("No hay eventos para esta semana 😣");
      } else {
        foreach ($events as $ev ){
          $message="<b> $ev->title </b> \n".
          "----------------------------------------------------- \n".
          "<b> Descripción </b>  : ".$ev->description."\n".
          "<b> Lugar </b>             : ".$ev->place."\n".
          "<b> Fecha </b>             : ".date('d-m-Y', $ev->date)."\n".
          "<b> Hora </b>               : ".$ev->time."\n".
          "----------------------------------------------------- \n";
          $this->say($message, [ 'parse_mode' => 'HTML']);
        }
      }
    }

    public function deletePastEventsFromDB() {
      DB::table('events')->where('date', '<', $this->current_time)->delete();
    }

    public function getMonthFormat(Answer $answer) {
        switch ($answer->getText()) {
            case "Enero":
                 $this->month_format = "January";
                break;
            case "Febrero":
                $this->month_format = "February";
                break;
            case "Marzo":
                $this->month_format = "March";
                break;
            case "Abril":
                $this->month_format = "April";
                break;
            case "Mayo":
                $this->month_format = "May";
                break;
            case "Junio":
                $this->month_format = "June";
                break;
            case "Julio":
                $this->month_format = "July";
                break;
            case "Agosto":
                $this->month_format = "August";
                break;
            case "Septiembre":
                $this->month_format = "September";
                break;
            case "Octubre":
                $this->month_format = "October";
                break;
            case "Noviembre":
                $this->month_format = "November";
                break;
            case "Diciembre":
                $this->month_format = "December";
                break;
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
