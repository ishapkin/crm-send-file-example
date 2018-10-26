
<?php
/**
 * Created by PhpStorm.
 * User: Ilya Shapkin
 * Date: 26/10/2018
 * Time: 11:43
 */

use GuzzleHttp\Psr7\MultipartStream;
use GuzzleHttp\Client;

class SendFileExample {

    const OBJECT_CODE = 10081;
    const REQUEST_CODE = 60109;
    const FILE_TYPE_CERTIFICATE = 'certificate';
    // Токен авторизации
    const AUTH_TOKEN = '{Your auth token}';
    // Адрес шины
    const BASE_URL = '{CRM cervice base url}';

    /**
     * @var Client|object
     */
    protected $client;

    /**
     * Worker constructor.
     */
    public function __construct()
    {
        /** @var Client $this->client */
        $this->client = new Client([
            'base_uri' => self::BASE_URL,
            'timeout'  => 2.0,
        ]);
    }

    /**
     * Отправляем файл
     * @param string $filePath
     * @param integer $objectId
     * @return bool|\Psr\Http\Message\ResponseInterface
     */
    public function sendCertificateFile($filePath, $objectId) {

        // Открываем файл
        $file = fopen($filePath, 'r');

        // Считаем границу
        $boundary = hash('sha256', uniqid('', true));

        // Подготоваливаем массив для отправки
        $multipartForm = [
            [
                'name'     => 'fieldNameHere',
                'contents' => $file,
                'headers'  => [ 'Content-Type' => mime_content_type($file)]
            ],
            [
                'name'     => 'data',
                'contents' => json_encode(
                    [
                        'FileType' => self::FILE_TYPE_CERTIFICATE,
                        'ObjectId' => $objectId,
                        'ObjectCode' => self::OBJECT_CODE,
                        'RequestCode' => self::REQUEST_CODE
                    ]
                ),
                // Хедер внутри стрима? А Почему бы и нет!
                'headers'  => [ 'Content-Type' => 'application/json']
            ]
        ];

        $params = [
            'headers' => [
                // Авторизуемся
                'Token' => self::AUTH_TOKEN,
                // Ставим границу
                'Content-Type' => 'multipart/form-data; boundary='.$boundary,
            ],
            // Открываем стрим
            'body' => new MultipartStream($multipartForm, $boundary),
        ];

        // Пуляем
        return $this->client->post( 'file/upload', $params);
    }

}