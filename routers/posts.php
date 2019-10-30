<?php

use App\PostsRepo;

//пример Пейджинга — это механизм, позволяющий итерироваться по большим коллекциям 
//небольшими порциями. Очень часто встречается в Интернете, например, 
//в результатах запросов поисковых систем. Пейджинг с точки зрения пользователя 
//выглядит как параметры запроса: page определяет текущую страницу, 
//а per — количество элементов на страницу. 
//Создаёт массив с постами.
$posts = new PostsRepo();

// BEGIN (write your solution here)
//1) Выводит с учётом пейджинга список постов.
$app->get('/posts', function ($request, $response) use ($posts, $router) {
    $postsArray = $posts->all(); // Извлекаем список из хранилища (DB). Обычно с учетом пейджинга.
    $page = $request->getQueryParam('page', 1); // Задаём по умолчанию вывод первой страницы.
    $per = $request->getQueryParam('per', 5); // Задаём по умолчанию кол-во отображаемых данных на одной странице.
    $offset = ($page - 1) * $per; // Отсуп для дальнейшего среза в массиве с данными.
    // Определяет последовательный вывод данных (срез) для каждой страницы. 
    $sliceOfPosts = array_slice($postsArray, $offset, $per); 
    $params = ['posts' => $sliceOfPosts, 'page' => $page,
               'urlMain' => $router->urlFor('main'),
               'style' => "background-color: #00c9ff;
                           padding: 5 10;
                           color: #f0fffa;
                           font-family: monospace;
                           text-decoration: none;
                           border-radius: 10px;"]; // Передаем данные в шаблон (срез, страницу и стиль)
    return $this->get('renderer')->render($response, 'posts/index.phtml', $params);
})->setName('posts');

//2) Выводит страницу конкретного поста.
$app->get('/posts/{id}', function ($request, $response, $args) use ($posts) {
    $id = $args['id']; // Из адреса извлекается идентификатор сущности.
    $post = $posts->find($id); // Выполняется поиск сущности в хранилище.
    if (!$post) { //возвращает ошибку если искомого поста не существует.
        return $response->write('Page not found')
                        ->withStatus(404);
    }
    $params = ['post' => $post]; // Она передается в шаблон.
    return $this->get('renderer')->render($response, 'posts/show.phtml', $params);
});
// END

// Маленький тестовый массив под пример пейджинга
//$companies = [
//    ['name' => 'Adams-Reichel', 'phone' => '1-986-987-9109 x56053'],
//    ['name' => 'Dibbert-Morissette', 'phone' => '439.584.3132 x735'],
//    ['name' => 'Ledner and Sons', 'phone' => '979-539-4173 x048'],
//    ['name' => 'Kiehn-Mann', 'phone' => '972-379-1995 x61054'],
//    ['name' => 'Bosco, Pouros and Larson', 'phone' => '887-919-2730 x49977']
//];

//$app->get('/posts', function ($request, \Slim\Http\Response $response) use ($posts) {
//    $page = $request->getQueryParam('page', 1);
//    $per = $request->getQueryParam('per', 5);
//    $offset = ($page - 1) * $per;
//    $postsArray = $posts->all();
//    $sliceOfPosts = array_slice($postsArray, $offset, $per);
//    return $response->write(json_encode($sliceOfPosts));
//});

