<?php 

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;

namespace App;

class TestClass {
    protected $database;
    protected $dbname='users';

    // public function __construct() {
    //     $acc=ServiceAccount::fromJsonFile(__DIR__. '\secret\tugasakhir-273202-6ee1f9786c82.json');
    //     $firebase=(new Factory)->withServiceAccount($acc)->create(); 
        
    //     $this->database=$firebase->getDatabase();
    // }
    // public static function get(int $userID = NULL){
    //     if (empty($userID) || !isset($userID)) { return false; }

    //     if ($this->database->getReference($this->dbname)->getSnapshot()->hasChild($userID)){
    //         return $database->getReference($this->dbname)->getChild($userID)->getValue();
    //     } else {
    //         return false; 
    //     }
    // }

    // public static  function testing() {
    //     return 'Happy Test';
    //    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $response->getBody()->write('Hello, World!');

        return $response;
    }
}
