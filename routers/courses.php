<?php

//Офигенный функционал, но какая то проблема с неймспйейсами. Типа постит на сайт новые курсы)) 
//Очень простая реализация принципа регистрации: если форма создания нового курса заполнена правильно, то 
//происходит переход на главную страницу, а курс сохраняется в списке курсов. Конечно, создание новых юзеров 
//больше по смыслу подходит)). Что можно реализовать ещё. 

use \App\{Repository, CourseValidator};

//Запускает новую сессию или возобновляет предыдущию.
$repo = new Repository();

// Показывает список существующих курсов и предлагает создать новый. 
// Принимает в себя об.$repo, из которого извлечёт массив сессии и её значения - 
// существующие курсы.
$app->get('/courses', function ($request, $response) use ($repo, $router) {
    $params = [
        'courses' => $repo->all(),  // Собственно извлекает значения.  
        'urlMain' => $router->urlFor('main'),
        'url' => $router->urlFor('course')  // создаёт маршрут по сет нейму  
    ];
//    dump($_SESSION);
    // Возвращает шаблон в котором указанна ссылка на создание нового курса и 
    // передаёт ему инфо в асоц.массиве $params об уже существующих курсах из массива сессии.  
    return $this->get('renderer')->render($response, 'courses/index.phtml', $params);
})->setName('courses');

// Срабатывает по переходу по ссылке из шаблона 'courses/index.phtml' из обработчика выше и 
// соответсвенно возвращает шаблон по отработанной ссылке. А шаблон сей(страница)есть 
// форма создания нового курса.
$app->get('/courses/new', function ($request, $response) use ($router) {
    // Массив для валидации.  
    $params = [
        'course' => [],
        'errors' => [],
        'url' => $router->urlFor('courses')
    ];
    return $this->get('renderer')->render($response, "courses/new.phtml", $params);
})->setName('course');

// Срабатывает при отправке формы создания нового курса, собственно с 
// новым куром в теле post запроса.
$app->post('/courses', function ($request, $response) use ($repo) {
    $validator = new CourseValidator();
    // Происходит извлечение данных формы из тела post запроса с помощью метода 
    // getParsedBodyParam($name, $defaultValue), 
    // в данном случае извлекается асоц.массив course с ключами title и paid.
    $course = $request->getParsedBodyParam('course');
    // Выполняется проверка массива course(об.класса Validator с помощью своего метода validate). 
    // Конкретно на то, а не пусты ли его ключи?  
    $errors = $validator->validate($course);
    // И если проверка не срабатывает (возвращается пустой массив $errors), то 
    // в массив $_SESSION сохраняются данные о новом курсе. И происходит переход на основную страницу.
    if (count($errors) === 0) {
        $repo->save($course);
        $repo->saveToFile($course);
        // Создаётся флеш, который отработает при редиректе ('Location', '/')
        $this->get('flash')->addMessage('success', 'Курс успешно добавлен!');
        return $response->withHeader('Location', '/')
                        ->withStatus(302);
        // Ещё так редиректнуть можно: return $response->withRedirect('/');
    }
    // А если срабатывает, то в шаблон формы передаются данные об ошибках и переход на 
    // основуню страницу не осуществляется, а пользователь остаётся на
    // странице формы с указанием своих ошибок.
    $params = [
        'course' => $course,
        'errors' => $errors
    ];
    return $this->get('renderer')->render($response->withStatus(422), "courses/new.phtml", $params);
});
