<?php

namespace App\Http\Controllers;

use BotMan\BotMan\BotMan;
use Illuminate\Http\Request;
use App\Conversations\ExampleConversation;
use App\Conversations\HelloConversation;
use Illuminate\Support\Facades\Log;

class BotManController extends Controller
{
    /**
     * Place your BotMan logic here.
     */
    public function handle()
    {
        $botman = app('botman');

        $botman->listen();
        Log::error('EL BOT ESTA ESCUCHANDO');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function tinker()
    {
        return view('tinker');
    }

    /**
     * Loaded through routes/botman.php
     * @param  BotMan $bot
     */
    public function startConversation(BotMan $bot)
    {

        Log::error('ESCUCHO hi');
        $bot->startConversation(new ExampleConversation());
    }


    /**
     * Loaded through routes/botman.php
     * @param  BotMan $bot
     */
    public function helloConversation(BotMan $bot)
    {

        Log::error('ESCUCHO CORRECTAMEMTE');
        $bot->startConversation(new HelloConversation());
    }
}
