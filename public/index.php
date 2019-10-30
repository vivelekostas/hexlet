<?php

// Подключение автозагрузки через composer (использование __DIR__ весьма полезно!)
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Slim\Middleware\MethodOverrideMiddleware;
use DI\Container;

//Это, видимо, подключение/создание шаблонизатора
$container = new Container();
$container->set('renderer', function() {
    // Параметром передается базовая директория в которой будут храниться шаблоны
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});

// подключение флеша?
$container->set('flash', function () {
    return new \Slim\Flash\Messages();
});

AppFactory::setContainer($container); //так понимаю - это подключение шаблонизатора и флеша?)

$app = AppFactory::create();

//Поддержка переопределения метода в самом Slim (для patch '/users/{id}').
//Что бы работали другие методы помимо post и get.
$app->add(MethodOverrideMiddleware::class); 

$app->addErrorMiddleware(true, true, true);

$router = $app->getRouteCollector()->getRouteParser(); // Получаем роутер.

session_start(); // Запускаю сессию для аутентификации.

// Подключаю другие контроллеры.
require_once __DIR__ . '/../routers/users.php';
require_once __DIR__ . '/../routers/courses.php';
require_once __DIR__ . '/../routers/posts.php';
require_once __DIR__ . '/../routers/session.php';

//Корневой маршрут.
$app->get('/', function ($request, $response) use ($router) {   
    // при переходе по этому маршруту, при успешном создании курса или ещё чего,   
    // сработает заданный в обработчике флеш.   
    $messages = $this->get('flash')->getMessages();
    $params = ['messages' => $messages,
               'urlUsers' => $router->urlFor('users'),
               'urlCourses' => $router->urlFor('courses'),
               'urlPosts' => $router->urlFor('posts'),
               'currentUser' => isset($_SESSION['user']) ? $_SESSION['user'] : null];
    return $this->get('renderer')->render($response, 'index.phtml', $params);
})->setName('main');

$app->run();
//=============================================================================





// Простейший обработчик
//$app->get('/', function ($request, $response) {
//    // при переходе по этому маршруту, при успешном создании курса(!),   
//    // сработает заданный в обработчике создания курса флеш.   
//    $messages = $this->get('flash')->getMessages();
//    return $response->write('Благодарен Богу за Алёнку!' . print_r($messages));
//});

//пример динамического запроса
//$app->get('/courses/{id}', function ($request, $response, array $args) {
//    $id = $args['id'];
//    return $response->write("Course id: {$id}");
//});
//пример динамического маршрута
//$app->get('/users/{id}', function ($request, $response, $args) {
//    $params = ['id' => $args['id'], 'nickname' => 'user-' . $args['id'], 'age' => 'возраст ' . 23];
//    // Указанный путь считается относительно базовой директории для шаблонов, заданной на этапе конфигурации
//    // $this доступен внутри анонимной функции благодаря http://php.net/manual/ru/closure.bindto.php
//    return $this->get('renderer')->render($response, 'users/show1.phtml', $params);
//});
//$arr = array('girl' => 'Murcha', 'God' => 'Jesus Christ', 'friend' => 'Tadj');
//// пример замыкания и возвращения масива в виде json из обработчика 
//$app->get('/array', function ($request, $response) use($arr) {
//    return $response->write(json_encode($arr));
//});

////=========================================================================START
//Что бы понять что тут происходит и в многих других обработчиках - просто раскоментируй)
//Очень сложная задача со скриптами для нескольких отдельных файлов (и я таки сдела её за пять часов!)
//index.php (основной)
//$userss = [
//    ['id'=>'1', 'firstName' => 'Adams', 'lastName' => 'Reichel', 'email' => 'Adams@gmail.com'],
//    ['id'=>'2', 'firstName' => 'Dibbert', 'lastName' => 'Morissette', 'email' => 'Dibbert@gmail.com'],
//    ['id'=>'3', 'firstName' => 'Ledner', 'lastName' => 'Sons', 'email' => 'Ledner@gmail.com']
//];
////1) первый обработчик (общий)
//$app->get('/users', function (Request $request, Response $response) use($userss){
//    $params = ['userss' => $userss];
//    return $this->get('renderer')->render($response, 'users/index.phtml', $params);
//})->setName('users'); //Метод setName задаёт имя маршрута
//
////2) динамический маршрут к конкретному юзеру по id
//$app->get('/users/{id}', function (Request $request, Response $response, $args) use ($userss) {
//    $id = (int) $args['id']; // записывает в $id запрос пользователя, преобразуя его в int
//    $user = collect($userss)->firstWhere('id', $id); // находит необходимый массив
//    $params = ['user' => $user]; //передаёт параметры из искомого массива локальным переменным для шаблона
//    return $this->get('renderer')->render($response, 'users/show.phtml', $params);
//})->setName('user'); //Метод setName задаёт имя маршрута
//===========================================================================END
//=========================================================================START
//$companies1 = [
//    ['id'=>'1', 'name' => 'Adams-Reichel', 'phone' => '1-986-987-9109 x56053'],
//    ['id'=>'2', 'name' => 'Dibbert-Morissette', 'phone' => '439.584.3132 x735'],
//    ['id'=>'3', 'name' => 'Ledner and Sons', 'phone' => '979-539-4173 x048']
//];
//// пример маршрута, который отдаёт json представление компании по get запросу и динамического запроса
//$app->get('/companies/{id}', function ($request, $response, array $args) use($companies1) {
//    $id = $args['id']; // записывает в $id запрос пользователя
//    $collection = collect($companies1); // создаёт объект класса Сollection (подключал специальную библиотеку)
//    if (($collection->firstWhere('id', $id)) === null) { // возвращает соответсиве значения ключа 'id' и $id
//        return $response->withStatus(404)
//                        ->write('Page not found.');
//    }
//    return $response->write(json_encode($collection->firstWhere('id', $id)));
//});
//===========================================================================END
//=========================================================================START
//Пример поисковой формы по данным:
// Обработчик для работы с простым массивом
//$users = ['mike', 'mishel', 'adel', 'keks', 'kamila'];
//$app->get('/userslist', function ($request, $response) use($users) {
//    $term = $request->getQueryParam('term');
//    $needUsers = [];
//    if ($term !== null) {
//        foreach ($users as $value) {
//            $pos = strpos($value, $term);
//            if ($pos === false) {
//                
//            } else {
//                array_push($needUsers, $value);
//            }
//        }
//        if (empty($needUsers) === true) {
//        $needUsers [] = "Увы, тут нет таких имён!";
//        }
//    }
//    $params = ['userslist' => $users, 'term' => $term, 'searchResult' => $needUsers];
//    return $this->get('renderer')->render($response, 'users/userslist.phtml', $params);
//});
//Такой же обработчик, только для работы с вложенным масивом
//$users = [
//    ['id' => 1, 'firstName' => 'Adams', 'lastName' => 'Reichel', 'email' => 'Adams@gmail.com'],
//    ['id' => 2, 'firstName' => 'Dibbert', 'lastName' => 'Morissette', 'email' => 'Dibbert@gmail.com'],
//    ['id' => 3, 'firstName' => 'Ledner', 'lastName' => 'Sons', 'email' => 'Ledner@gmail.com']
//];
//
//$app->get('/userslist', function ($request, $response) use($users) {
//    $term = $request->getQueryParam('term'); //Извлекает из get запроса формы значение term
//    $needUsers = []; //пустой массив для $params
//    if ($term !== null) { //проверка отправки формы
//        foreach ($users as $value) { //перебирает значения массива
//            $pos = strpos($value['firstName'], $term); //и ищет в них значение $term (искомое значение из формы)
//            if ($pos === false) { //if ради else)))
//                
//            } else {
//                array_push($needUsers, $value); //все найденные значения записываются в массив
//            }
//        }
//        if (empty($needUsers) === true) { //проверка на случай если не найденно было ни одного совпадения
//        $needUsers [] = "Увы, тут нет таких имён!";
//        }
//    }
//    $params = ['userslist' => $users, 'term' => $term, 'searchResult' => $needUsers];
//    return $this->get('renderer')->render($response, 'users/usersListHard.phtml', $params);
//});
// А вот так решили они. Красиво конечно, но мне не понятно. Я бы так не смог,
// присутсвуют незнакомые мне методы.
//$app->get('/users', function ($request, $response) use ($users) {
//    $term = $request->getQueryParam('term');
//    $result = collect($users)->filter(function ($user) use ($term) {
//        return s($user['firstName'])->startsWith($term, false);
//    });
//    $params = [
//        'users' => $result,
//        'term' => $term
//    ];
//    return $this->get('renderer')->render($response, 'users/index.phtml', $params);
//});
// и пример шаблона под него
/*
  <form action="/users">
  <input type="search" name="term" value="<?= htmlspecialchars($term) ?>">
  <input type="submit" value="Search">
  </form>

  <?php foreach ($users as $user): ?>
  <div>
  <?= htmlspecialchars($user['firstName']) ?>
  </div>
  <?php endforeach ?>
 */
//===========================================================================END





