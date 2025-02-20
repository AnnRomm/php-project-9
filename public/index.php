<?php

require __DIR__ . '/../vendor/autoload.php';

use DI\Container;
use Dotenv\Dotenv;
use Hexlet\Code\Connection;
use Hexlet\Code\HttpClient;
use Hexlet\Code\Repository\UrlCheckRepository;
use Hexlet\Code\Repository\UrlRepository;
use Hexlet\Code\UrlValidator;
use Slim\Factory\AppFactory;
use Slim\Flash\Messages;
use Slim\Middleware\MethodOverrideMiddleware;
use Slim\Views\PhpRenderer;
use Slim\Exception\HttpNotFoundException;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();
$dotenv->required(['DATABASE_URL'])->notEmpty();

$conn = Connection::create($_ENV['DATABASE_URL']);

session_start();

$container = new Container();
$app = AppFactory::createFromContainer($container);

$container->set('pdo', fn() => $conn);
$container->set('flash', new Messages());
$container->set('router', $app->getRouteCollector()->getRouteParser());
$container->set('renderer', function () use ($container) {
    $phpView = new PhpRenderer(__DIR__ . '/../templates');
    $phpView->addAttribute('flash', $container->get('flash')->getMessages());
    $phpView->addAttribute('router', $container->get('router'));
    $phpView->setLayout('layout.phtml');

    return $phpView;
});

$app->add(MethodOverrideMiddleware::class);
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

$app->get('/', function ($request, $response) {
    return $this->get('renderer')->render($response, 'mainpage/index.phtml', ['activeMenu' => 'main']);
})->setName('mainpage.index');

$app->get('/urls', function ($request, $response) {
    $pdo = $this->get('pdo');
    $repo = new UrlRepository($pdo);

    $urls = $repo->getAllUrls();
    $lastChecks = $repo->getLastUrlChecks();

    $checksByUrlId = collect($lastChecks)->keyBy('url_id');

    $urlChecksInfo = collect($urls)->map(fn($url) => array_merge($url, $checksByUrlId->get($url['id'], [])))->all();

    $params = [
        'urls' => $urlChecksInfo,
        'activeMenu' => 'urls'
    ];

    return $this->get('renderer')->render($response, 'urls/index.phtml', $params);
})->setName('urls.index');

$app->get('/urls/{id:[0-9]+}', function ($request, $response, $args) {
    $pdo = $this->get('pdo');
    $repo = new UrlRepository($pdo);
    $id = (int)$args['id'];

    $url = $repo->getUrlById($id);
    $urlChecks = $repo->getUrlChecksByUrlId($id);

    if ($url === null) {
        throw new HttpNotFoundException($request);
    }

    $params = [
        'url' => [
            'id' => $id,
            'name' => $url['name'],
            'created_at' => $url['created_at']
        ],
        'urlChecks' => array_map(fn($row) => [
            'id' => $row['id'],
            'status_code' => $row['status_code'],
            'h1' => $row['h1'],
            'title' => $row['title'],
            'description' => $row['description'],
            'created_at' => $row['created_at']
        ], $urlChecks),
        'activeMenu' => ''
    ];

    return $this->get('renderer')->render($response, 'urls/show.phtml', $params);
})->setName('urls.show');

$app->post('/urls', function ($request, $response) {
    $inputtedUrlData = $request->getParsedBodyParam('url', []);
    $inputtedUrl = mb_strtolower($inputtedUrlData['name'] ?? '');

    $errors = UrlValidator::validate($inputtedUrlData);
    if (!empty($errors)) {
        return $this->get('renderer')->render($response->withStatus(422), 'mainpage/index.phtml', [
            'errors' => $errors,
            'inputtedUrl' => $inputtedUrl,
            'activeMenu' => 'main'
        ]);
    }
    $inputtedUrl = mb_strtolower($inputtedUrlData['name']);
    $parsedUrl = parse_url($inputtedUrl);
    $scheme = $parsedUrl['scheme'];
    $host = $parsedUrl['host'];
    $url = "{$scheme}://{$host}";

    $pdo = $this->get('pdo');
    $currentTime = date("Y-m-d H:i:s");

    $urlRepository = new UrlRepository($pdo);
    $urlExists = $urlRepository->findByName($url);

    if ($urlExists) {
        $this->get('flash')->addMessage('success', 'Страница уже существует');
        $id = $urlExists;
    } else {
        $id = $urlRepository->insertNewUrl($url, $currentTime);
        $this->get('flash')->addMessage('success', 'Страница успешно добавлена');
    }

    return $response->withRedirect($this->get('router')->urlFor('urls.show', ['id' => $id]), 301);
})->setName('urls.store');
;

$app->post('/urls/{id:[0-9]+}/checks', function ($request, $response, $args) {
    $id = (int)$args['id'];

    $urlRepository = new UrlRepository($this->get('pdo'));
    $urlData = $urlRepository->getUrlById($id);
    if ($urlData === null) {
        throw new HttpNotFoundException($request);
    }
    $url = $urlData['name'];

    $httpClient = new HttpClient();
    $result = $httpClient->checkUrl($url);

    if ($result['status'] === 'error') {
        $this->get('flash')->addMessage('danger', $result['message']);
        return $response->withRedirect($this->get('router')->urlFor('urls.show', ['id' => (string)$id]));
    }

    $urlCheckRepository = new UrlCheckRepository($this->get('pdo'));
    $urlCheckRepository->createCheck($id, $result);

    $this->get('flash')->addMessage('success', 'Страница успешно проверена');
    return $response->withRedirect($this->get('router')->urlFor('urls.show', ['id' => (string)$id]), 301);
})->setName('urls.id.check');

$app->map(['GET', 'POST'], '/{routes:.+}', function ($request, $response) {
    return $this->get('renderer')->render($response, 'errors/404.phtml', ['activeMenu' => '']);
})->setName('not-found');

$app->run();
