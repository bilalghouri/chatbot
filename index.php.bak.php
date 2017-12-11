<?
require __DIR__ . '/vendor/autoload.php';
use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Drivers\DriverManager;

DriverManager::loadDriver(\BotMan\Drivers\Facebook\FacebookDriver::class);

$config = [
    'facebook' => [
    'token' => 'EAAbQa3rgGcMBAP5MS089F0SStU4pf4qbCc6amMjnSdlwmkS9zZB7uL1IYModgP7HxVY0ZAUfXNk3Hp9PjT2ofnfQZAIznZCGRCCVwZB276ahoRdbv1ZAYOUBWEX9LC9lP8EZC7GIDzLNowXZCiALgM0F4ZC4CP1uyBZA9DIiZByuEYHAQZDZD',
    'app_secret' => 'ed659387efd74af7694fbe5b02fc7fd1',
    'verification'=>'lololol#adasd4SAdasdaasdadsad#',
]
];

// create an instance
$botman = BotManFactory::create($config);

// give the bot something to listen for.
$botman->hears('hello', function (BotMan $bot) {
    $bot->reply('Hello yourself.');
});

// start listening
$botman->listen();
?>
