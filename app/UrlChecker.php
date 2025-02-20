<?php

namespace Hexlet\Code;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;
use DiDom\Document;

class UrlChecker
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function checkUrl(string $url): array
    {
        try {
            $response = $this->client->get($url);
            $statusCode = $response->getStatusCode();
            $body = $response->getBody();
            $document = new Document((string)$body);

            $h1 = optional($document->first('h1'))->text();
            $title = optional($document->first('title'))->text();
            $description = $document->first('meta[name=description]')?->getAttribute('content');

            return [
                'status' => 'success',
                'statusCode' => $statusCode,
                'h1' => $h1,
                'title' => $title,
                'description' => $description,
            ];
        } catch (RequestException $e) {
            $response = $e->getResponse();
            $statusCode = $response ? $response->getStatusCode() : null;

            return [
                'status' => 'error',
                'message' => 'Проверка выполнена успешно, но сервер ответил с ошибкой',
                'statusCode' => $statusCode,
                'errorDetails' => $e->getMessage()
            ];
        } catch (ConnectException $e) {
            return [
                'status' => 'error',
                'message' => 'Не удалось подключиться к серверу',
                'errorDetails' => $e->getMessage()
            ];
        }
    }
}
