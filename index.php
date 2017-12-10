<?
require __DIR__ . '/vendor/autoload.php';
use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Drivers\DriverManager;

DriverManager::loadDriver(\BotMan\Drivers\Facebook\FacebookDriver::class);

$config = [
    // Your driver-specific configuration
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
