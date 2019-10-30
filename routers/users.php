<?php
//CRUD юзера.

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \App\{UserValidator, UsersManager};

$usersManager = new UsersManager();

//1) первый обработчик (общий) Выводит Список пользователей и предлагает создать нового.
$app->get('/users', function (Request $request, Response $response) use ($usersManager, $router) {
    $usersArray = $usersManager->getUsers(); //возвращает массив с массивами юзеров)
//    $cart = json_decode($request->getCookieParam('cart', json_encode([])), true); //Данные куки.
    $newArray = [];
    foreach ($usersArray as $user) {
        $newArray[$user['id']] = $user;
    }
    $messages = $this->get('flash')->getMessages();
    $params = ['users' => $usersArray, 
               'messages' => $messages,
//               'cart' => $cart,
               'urlMain' => $router->urlFor('main'),
               'urlFind' => $router->urlFor('find'),
               'url' => $router->urlFor('newUsers')
               ]; // Задаёт в праметры шаблона массив с юзерами.
    return $this->get('renderer')->render($response, 'users/index.phtml', $params); // Рендерит шаблон.
})->setName('users'); //Метод setName задаёт имя маршрута

//3) Отображение формы создания нового юзера(сущности).
$app->get('/users/new', function (Request $request, Response $response) use ($router) {
    $params = [
        'userData' => [],
        'errors' => [],
        'url' => $router->urlFor('users')
    ];
    return $this->get('renderer')->render($response, 'users/new.phtml', $params);
})->setName('newUsers');

//8) Обработчик для поиска. По хорошему, весь кривоватый функционал этого обработчика
// нужно поместить в класс usersManager, но времени на это нет, а включить его в 
// приложение хотелось, чтоб не забыть, ато он валялся в черновиках. 
$app->get('/users/find', function ($request, $response) use ($usersManager, $router) {
    $term = $request->getQueryParam('term'); //Извлекает из get запроса формы значение term
    $needUsers = []; //пустой массив для $params
    $usersArray = $usersManager->getUsers();
    if ($term !== null) { //проверка отправки формы
        foreach ($usersArray as $value) { //перебирает значения массива
            $pos = strpos($value['firstName'], $term); //и ищет в них значение $term (искомое значение из формы)
            if ($pos === false) { //if ради else)))
                
            } else {
                array_push($needUsers, $value); //все найденные значения записываются в массив
            }
        }
        if (empty($needUsers) === true) { //проверка на случай если не найденно было ни одного совпадения
        $needUsers [] = "Увы, тут нет таких имён!";
        }
    }
    $params = ['userslist' => $usersArray,
               'term' => $term,
               'urlFind' => $router->urlFor('find'),
               'urlMain' => $router->urlFor('main'),
               'searchResult' => $needUsers
            ];
    return $this->get('renderer')->render($response, 'users/find.phtml', $params);
})->setName('find');

//2) Динамический маршрут к конкретному юзеру по id.
$app->get('/users/{id}', function (Request $request, Response $response, array $args) use ($usersManager, $router) {
    $id = (int) $args['id']; // записывает в $id запрос пользователя, преобразуя его в int      
    $user = $usersManager->find($id);
    $messages = $this->get('flash')->getMessages();
    if ($user === null) { // если в массиве нет такого id, то:
        return $response->withStatus(404)
                        ->withHeader('Content-Type', 'text/html')
                        ->write('Страница пользователя не найдена! (0_0)');
    }
    $params = ['user' => $user, 
               'messages' => $messages,
               'urlUsers' => $router->urlFor('users') ]; //передаёт параметры из искомого массива локальным переменным для шаблона
    return $this->get('renderer')->render($response, 'users/show.phtml', $params);
})->setName('user'); //Метод setName задаёт имя маршрута

//4)Контроллер для обработки данных формы создания юзера(сохраняет в файл).
$app->post('/users', function (Request $request, Response $response) use ($usersManager, $router) {
    $userData = $request->getParsedBodyParam('user'); // Извлекаем данные формы.
    $validator = new UserValidator();
    $errors = $validator->validate($userData); // Проверяем корректность данных.
    if (count($errors) === 0) {
        // Если данные корректны, то сохраняем, добавляем флеш и выполняем редирект.
        $usersManager->save($userData);
        $this->get('flash')->addMessage('success', 'Запилен новый юзер!');
        // Обратите внимание на использование именованного роутинга
        $url = $router->urlFor('main');
        return $response->withRedirect($url);
    }
    $params = ['user' => $userData, 'errors' => $errors];
    // Если возникли ошибки, то устанавливаем код ответа в 422 и рендерим форму с указанием ошибок
    $response = $response->withStatus(422);
    return $this->get('renderer')->render($response, 'users/new.phtml', $params);
});

//5) Обработчик формы обновления юзера.
$app->get('/users/{id}/edit', function (Request $request, Response $response, array $args) use ($usersManager, $router) {
    $id = (int) $args['id']; // Записывает в $id запрос по конкретному юзеру.
    $user = $usersManager->find($id); //Ищет необходимого юзера по id.
    $params = [
        'user' => $user, // заполнит данные формы обновления уже существующими данными.
        'errors' => []
    ];
    return $this->get('renderer')->render($response, 'users/edit.phtml', $params);
})->setName('editUser');

//6) Контроллер для обработки данных формы обновления юзера.
$app->patch('/users/{id}', function (Request $request, Response $response, array $args) use ($usersManager, $router) {
    $id = (int) $args['id']; // Записывает в $id запрос пользователя.
    $user = $usersManager->find($id);//Ищет необходимого юзера по id.
    $data = $request->getParsedBodyParam('user'); //Извлекаем данные update формы.
    $validator = new UserValidator();
    $errors = $validator->validate($data); // Проверяем корректность данных.
    if (count($errors) === 0) {
        $user['firstName'] = $data['firstName'];
        $user['lastName'] = $data['lastName'];
        $user['email'] = $data['email'];// Ручное копирование(обновление) данных из формы в нашу сущность.
        $this->get('flash')->addMessage('success', 'Милорд, ваш вассал был обновлён!');
        $usersManager->save($user); //Сохранение обновлённых данных
        $url = $router->urlFor('user', ['id' => $user['id']]); // Адрес для перехода на страницу обновленного юзера.
        return $response->withRedirect($url);
    }
    $params = [
        'userData' => $data,
        'user' => $user,
        'errors' => $errors
    ];
    $response = $response->withStatus(422);
    return $this->get('renderer')->render($response, 'users/edit.phtml', $params);
});

//7) Контроллер для удаления неугодного юзера.
$app->delete('/users/{id}', function (Request $request, Response $response, array $args) use ($usersManager, $router) {
    $id = $args['id'];
    $usersManager->destroy($id);
    $this->get('flash')->addMessage('success', 'Милорд, неугодный юзер удалён!');
    return $response->withRedirect($router->urlFor('users'));
});

//8) Обработчик для поиска.
//$app->get('/users/userslist', function ($request, $response) use ($usersManager) {
//    $term = $request->getQueryParam('term'); //Извлекает из get запроса формы значение term
//    dd($term);
//    $needUsers = []; //пустой массив для $params
//    $usersArray = $usersManager->getUsers();
//    if ($term !== null) { //проверка отправки формы
//        foreach ($usersArray as $value) { //перебирает значения массива
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
//  =================================Черновики==================================

//4)Контроллер для обработки данных формы создания юзера, с сохранением и в файл и в куки!
//$app->post('/users', function (Request $request, Response $response) use ($usersManager, $router) {
//    $userData = $request->getParsedBodyParam('user'); // Извлекаем данные формы.
//    $validator = new UserValidator();
//    $errors = $validator->validate($userData); // Проверяем корректность данных.
//    if (count($errors) === 0) {
//        // Если данные корректны, то сохраняем, добавляем флеш и выполняем редирект.
//        // Сохранение в куки размещенно здесь, чтоб обеспечить сохаренние валидных данных.
//        $usersManager->save($userData);
//        $this->get('flash')->addMessage('success', 'Запилен новый юзер!');
//        //========================Cookie========================================       
//        $cart = json_decode($request->getCookieParam('cart', json_encode([]))); // Данные "корзины". Которая создаётся в моменте?
//        $cart[] = $userData; // Добавление нового юзера в корзину.
//        $encodedCart = json_encode($cart);  // Кодирование корзины.
//        //========================/Cookie========================================
//        // Обратите внимание на использование именованного роутинга
//        $url = $router->urlFor('main');
//        // Установка обновлённой корзины в куку. Это: 'Set-Cookie', "cart={$encodedCart}") - установка кук, 
//        // а withHeader, как бы обеспечит их транспортировку браузеру в ответе.
//        return $response->withHeader('Set-Cookie', "cart={$encodedCart}")
//                        ->withRedirect($url);
//    }
//    $params = ['user' => $userData, 'errors' => $errors];
//    // Если возникли ошибки, то устанавливаем код ответа в 422 и рендерим форму с указанием ошибок
//    $response = $response->withStatus(422);
//    return $this->get('renderer')->render($response, 'users/new.phtml', $params);
//});
//------------------------------------------------------------------------------
//Контроллер, который сохраняет данные в куки
//$app->post('/users', function (Request $request, Response $response) use ($usersManager, $router) {
//    $userData = $request->getParsedBodyParam('user'); // Извлекаем данные формы.
//    $cart = json_decode($request->getCookieParam('cart', json_encode([]))); // Данные "корзины". Которая создаётся в моменте?
//    $cart[] = $userData; // Добавление нового юзера в корзину.
//    $encodedCart = json_encode($cart);  // Кодирование корзины.
//    // Установка обновлённой корзины в куку. Это: 'Set-Cookie', "cart={$encodedCart}") - установка кук, 
//    // а withHeader, как бы обеспечит их транспортировку браузеру в ответе.
//    return $response->withHeader('Set-Cookie', "cart={$encodedCart}")
//                    ->withRedirect('users');
//});
//------------------------------------------------------------------------------
//define("PATH_TO_USER", __DIR__ . '/../data/users.json');
////dd(PATH);
//
////dump($userss); // вывод массива
//$json = json_encode($userss); // 1) превращает массив в json строку
////dump($json); // выводит эту сроку
//
//// 2) Пишет данные в файл 
//file_put_contents(PATH_TO_USER, $json);
//
//// 3) Читает содержимое файла в строку
//$jsonFromFile = file_get_contents(PATH);
//dump($jsonFromFile); //выводит эту строку
////
//// 4) Декодирует строку JSON в массив
//$arraySYuzerami= json_decode($jsonFromFile, true);
////
//dd($arraySYuzerami);           

//Щётчик для корзины на куки-----------------------------------------------Start
//$app->post('/cart-items', function ($request, $response) {
//    $item = $request->getParsedBodyParam('item'); // Информация о добавляемом товаре
//    $item['count'] = 1;
//    $cart = json_decode($request->getCookieParam('cart', json_encode([]))); // Данные корзины
//    foreach ($cart as $value) {
//        if ($value->id == $item['id']) {
//            $i = $value->count;
//            $i++;
//            $value->count = $i;
//            $encodedCart = json_encode($cart);
//            return $response->withHeader('Set-Cookie', "cart={$encodedCart}") // Установка новой корзины в куку
//                            ->withRedirect('/');
//        }
//    }
//    $cart[] = $item; // Добавление нового товара
//    $encodedCart = json_encode($cart); // Кодирование корзины
//    return $response->withHeader('Set-Cookie', "cart={$encodedCart}") // Установка новой корзины в куку
//        ->withRedirect('/');
//});
//
////Отчистка корзины
//$app->delete('/cart-items', function ($request, $response) {
//    // $item = $request->getParsedBodyParam('item'); // Информация о добавляемом товаре
//    $cart = json_decode($request->getCookieParam('cart', json_encode([]))); // Данные корзины
//    $cart = []; // Удаление всего товара из корзины
//    $encodedCart = json_encode($cart); // Кодирование корзины
//    return $response->withHeader('Set-Cookie', "cart={$encodedCart}") // Установка новой корзины в куку
//        ->withRedirect('/');
//});
//
//Тоже, только вариант хекслета:
//$app->post('/cart-items', function ($request, $response) {
//    $item = $request->getParsedBodyParam('item');
//    $cart = json_decode($request->getCookieParam('cart', json_encode([])), true);
//
//    $id = $item['id'];
//    if (!isset($cart[$id])) {
//        $cart[$id] = ['name' => $item['name'], 'count' => 1];
//    } else {
//        $cart[$id]['count'] += 1;
//    }
//
//    $encodedCart = json_encode($cart);
//    return $response->withHeader('Set-Cookie', "cart={$encodedCart}")
//        ->withRedirect('/');
//});
//
//$app->delete('/cart-items', function ($request, $response) {
//    $encodedCart = json_encode([]);
//    return $response->withHeader('Set-Cookie', "cart={$encodedCart}")
//        ->withRedirect('/');
//});
//------------------------------------------------------------------------Finish
