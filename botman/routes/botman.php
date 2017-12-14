<?php
use App\Http\Controllers\BotManController;

$botman = resolve('botman');
$greeted = false;

$botman->hears('GET_STARTED', function ($bot) use (&$greeted) {
    $user = $bot->getUser();
    $firstname = $user->getFirstName();

    if ($firstname == 'Ayaz')
        $bot->reply('Kaisa he ayaz chutiye?');
    else
        $bot->reply("Hi. I'm trying to learn urdu. Have you met Bilal Ghouri? Pretty cool guy. He's helping me grow up. How may I help you today?");

    $greeted = true;
});

$botman->hears('Hi', function ($bot) use (&$greeted) {
    if ($greeted)
        $bot -> reply("How can I help you?");
    else
    {
        $user = $bot->getUser();
        $firstname = $user->getFirstName();

        if ($firstname == 'Ayaz')
            $bot->reply('Kaisa he ayaz chutiye?');
        else
            $bot->reply("Hi. I'm trying to learn urdu. Have you met Bilal Ghouri? Pretty cool guy. He's helping me grow up. How may I help you today?");

        $greeted = true;
    }
});

$botman->hears('lol', function ($bot) {
    $user       = $bot->getUser();
    $firstname  = $user->getFirstName();

    if ($firstname == 'Ayaz')
        $bot->reply('bohot hasi arahi hay?');
    else
        $bot->reply(":)");
});

$botman->hears('#tum (.*) ho#i', function ($bot, $word) {
    $bot->reply("nahi, tum $word ho!");
});

$botman->hears('#tu (.*) (he|hai|hay|ha|h)#i', function ($bot, $word) {
    $bot->reply("nahi, tu $word hai!");
});

$botman->hears('#bilal (?:ghouri) ([a-zA-Z\s]+) (he|hai|hay|ha|h)#i', function ($bot, $word) {
    $bot->reply("Bilal bohot tight banda hay! Tu $word hai.");
});

$botman->hears('#(lund lelo|loray|gandu|chup|shut up|shut the fuck up) .*#', function ($bot) {
    $user       = $bot->getUser();
    $firstname  = $user->getFirstName();

    if ($firstname == 'Ayaz')
    {
        $bot->reply('.');
        $bot->reply('.');
        $bot->reply('.');
        $bot->reply('chutye.');
    }
    else
        $bot->reply("Please calm down.");
});
