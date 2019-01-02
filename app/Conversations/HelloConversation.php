<?php

namespace App\Conversations;

use Illuminate\Foundation\Inspiring;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\BotMan\Messages\Outgoing\Message;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Conversations\Conversation;
use Illuminate\Support\Facades\Log;

class HelloConversation extends Conversation
{
  protected $location;
  protected $date;
  protected $day;
  protected $month;
  protected $year;
  protected $time;

  protected $title;
  protected $description;
  protected $tag = 0;

    public function initConversation()
    {
      Log::error('INICIO LA CONVERSACION!');
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
                  //TODO : show the closest 5 events
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
          $question = Question::create('Mes : ')
                  ->fallback('Vaya, se ha producido un error, vuelve a intentarlo')
                  ->callbackId('ask_month')
                  ->addButtons([
                    Button::create('Enero')->value('Enero'),
                    Button::create('Febrero')->value('Febrero'),
                    Button::create('Marzo')->value('Marzo'),
                    Button::create('Abril')->value('Abril')
                   ])
                   ->addButtons([
                    Button::create('Mayo')->value('Mayo'),
                    Button::create('Junio')->value('Junio'),
                    Button::create('Julio')->value('Julio'),
                    Button::create('Agosto')->value('Agosto')
                   ]/*)
                   ->addButtons([
                    Button::create('Septiembre')->value('Septiembre'),
                    Button::create('Octubre')->value('Octubre'),
                    Button::create('Noviembre')->value('Noviembre'),
                    Button::create('Diciembre')->value('Diciembre')
                  ]*/);

              $this->ask($question, function (Answer $answer) {
                  if ($answer->isInteractiveMessageReply()) {
                    if($answer->getValue() === 'cancel'){
                      $this->say('Hasta pronto 😄');
                    } else {
                      $this->month = $answer->getText();
                      $this->say($this->month);
                      $this->askDay();
                    }
                  }
              });
        }
        public function askDay()
          {
            $question = Question::create('Día : ')
                    ->fallback('Vaya, se ha producido un error, vuelve a intentarlo')
                    ->callbackId('ask_day')
                    ->addButtons([
                      Button::create('1')->value('1'),
                      Button::create('2')->value('2'),
                      Button::create('3')->value('3'),
                      Button::create('4')->value('4'),
                      Button::create('5')->value('5'),
                      Button::create('6')->value('6'),
                      Button::create('7')->value('7')
                     ]/*)
                     ->addButtons([
                       Button::create('8')->value('8'),
                       Button::create('9')->value('2'),
                       Button::create('10')->value('10'),
                       Button::create('11')->value('11'),
                       Button::create('12')->value('12'),
                       Button::create('13')->value('13'),
                       Button::create('14')->value('14')
                     ])
                     ->addButtons([
                       Button::create('15')->value('15'),
                       Button::create('16')->value('16'),
                       Button::create('17')->value('17'),
                       Button::create('18')->value('18'),
                       Button::create('19')->value('19'),
                       Button::create('20')->value('20'),
                       Button::create('21')->value('21')
                    ]
                    ->addButtons([
                      Button::create('22')->value('22'),
                      Button::create('23')->value('23'),
                      Button::create('24')->value('24'),
                      Button::create('25')->value('25'),
                      Button::create('26')->value('26'),
                      Button::create('27')->value('27'),
                      Button::create('28')->value('28')
                   ]
                   ->addButtons([
                     Button::create('')->value(''),
                     Button::create('')->value(''),
                     Button::create('29')->value('29'),
                     Button::create('30')->value('30'),
                     Button::create('31')->value('31'),
                     Button::create('')->value(''),
                     Button::create('')->value('')
                  ]*/);

                $this->ask($question, function (Answer $answer) {
                    if ($answer->isInteractiveMessageReply()) {
                      if($answer->getValue() === 'cancel'){
                        $this->say('Hasta pronto 😄');
                      } else {
                        $this->day = $answer->getText();
                        $this->say($this->day);
                        $this->date = $this->day.'/'.$this->month.'/'.$this->year;
                        $this->askTitle();
                      }
                    }
                });
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
                $this->askLocation();
            });
        }



    public function askLocation()
        {
            $this->ask('¿Dónde va a ocurrir el evento? : ', function(Answer $answer) {
                $this->location = $answer->getText();
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
                  "-------------------------------------------------- \n".
                  "<b> Título </b>       : ".$this->title."\n".
                  "<b> Descripción </b>  : ".$this->description."\n".
                  "<b> Lugar </b>        : ".$this->location."\n".
                  "<b> Fecha </b>        : ".$this->date."\n".
                  "<b> Hora </b>          : ".$this->time."\n".
                  "-------------------------------------------------- \n".
                  "Está listo para ser compartido con el mundo.")
                  ->fallback('Vaya, se ha producido un error, vuelve a intentarlo')
                  ->callbackId('show_result')
                  ->addButtons([
                    //TODO : print date
                      Button::create('Cancelar')->value('cancel'),
                      Button::create('Publicar')->value('register')
                ]);

              $this->ask($question, function (Answer $answer) {
                  if ($answer->isInteractiveMessageReply()) {
                    if ($answer->getValue() === 'register') {
                      //TODO : store data
                      $this->say('Evento publicado con éxito 👏');
                    } else {
                      $this->say('Hasta pronto 😄');
                    }
                  }
              }, [ 'parse_mode' => 'HTML']);
        }


    /*private function buildCalendar(){
      $calendar = array();
      $year = date('Y', time());
      $month = date('m', time());
      $monthName = date('F', mktime(0, 0, 0, $month, 10));

      $daysInMonth = date('t',strtotime($year.'-'.$month.'-01'));
      $weeksInMonth = ($daysInMonths%7==0?0:1) + intval($daysInMonths/7);

      $monthEndingDay= date('N',strtotime($year.'-'.$month.'-'.$daysInMonths));
      $monthStartDay = date('N',strtotime($year.'-'.$month.'-01'));
      if($monthEndingDay<$monthStartDay){
          $weeksInMonth++;
      }

      array_push($calendar, $monthName.' '.$year);
      array_push($calendar, array('L.','M.','Mi.','J.','V.','S.','D.'));

      $week = array();
      for ($i=0; $i < $weeksInMonth; $i++) {
        for ($j=0; $j < 7; $j++) {
          if((i == 0 && j<$monthStartDay) //first and las row may contains empty elements
                  || (i == $weeksInMonth-1  && j>=$monthEndingDay)) {
            array_push($week, ' ');
          }
          else {
            array_push($week, (i*7) + j+1);
          }
        }
        array_push($calendar,$week);
        $week = array();
      }
      array_push($calendar, array('<<',' ','>>');

      return $calendar;
    }*/

    /**
     * Start the conversation
     */
    public function run()
    {
        $this->initConversation();
    }
}
