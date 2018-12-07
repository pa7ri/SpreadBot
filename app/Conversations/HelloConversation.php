<?php

namespace App\Conversations;

use Illuminate\Foundation\Inspiring;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\BotMan\Messages\Outgoing\Message;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Conversations\Conversation;

class HelloConversation extends Conversation
{
  protected $location = 'Madrid';
  protected $date = '22/09/2019';
  protected $day;
  protected $month;
  protected $year;
  protected $time = '19:30';

  protected $title = 'titulo';
  protected $description= 'descr';
  protected $tag = 0;

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
                  //TODO : store event into
              } else {
                $this->say('Hasta pronto');
              }
          }
      });
    }

    public function askDate()
      {

        $keyboard = [
            ['7', '8', '9'],
            ['4', '5', '6'],
            ['1', '2', '3'],
            ['0']
        ];

        $question = Question::create('Primero elige una fecha para tu evento : ')
                ->fallback('Vaya, se ha producido un error, vuelve a intentarlo')
                ->callbackId('ask_date')
                ->addButtons([ //TODO : use my calendar
                  Button::create('Enero')->value('Enero'),
                  Button::create('Febrero')->value('Febrero'),
                  Button::create('Marzo')->value('Marzo'),
                  Button::create('Abril')->value('Abril'),
                  Button::create('Mayo')->value('Mayo')
                ]);

            $this->ask($question, function (Answer $answer) {
                if ($answer->isInteractiveMessageReply()) {
                  if($answer->getValue() === 'cancel'){
                    $this->say('Hasta pronto');
                  } else {
                    $this->say($answer->getText());
                      //TODO : Manage date button
                    $this->askTitle();
                  }
                }
            } ,['reply_markup' => json_encode([
                'keyboard' => $keyboard,
                'one_time_keyboard' => true,
                'resize_keyboard' => true
                ])
            ]);
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
          $question = Question::create('Perfecto, así quedaría tu evento : '.'<br>'.
                  '-------------------------------------- <br>'.
                  '<b> Título </b>       : '.$this->title.'<br>'.
                  '<b> Descripción </b>  : '.$this->description.'<br>'.
                  '<b> Lugar </b>        : '.$this->location.'<br>'.
                  '<b> Fecha y hora </b> : '.$this->date.' - '.$this->time.'<br>'.
                  '-------------------------------------- <br>'.
                  'Está listo para ser compartido con el mundo.')
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
                      //TODO : Manage date button
                      $this->say('Evento publicado, nos vemos pronto 👋');
                    } else {
                      $this->say('Hasta pronto');
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
