<?php

use Slim\Http\UploadedFile;
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Auth;

return function (App $app) {
    $container = $app->getContainer();
    $container['upload_directory'] = __DIR__ . '/uploads';

    $app->post("/login", function (Request $request, Response $response){       
        $input=$request->getParsedBody();     
        $class = new All();
        $result = $class->login($input['email'],$input['password']);

        return $response->getBody()->write((string)json_encode($result));
    });

    $app->get("/getExerciseSuggestion", function (Request $request, Response $response){       
        $input=$request->getParsedBody();     
        $class = new All();
        $result = $class->login($input['email'],$input['password']);

        return $response->getBody()->write((string)json_encode($result));
    });

    $app->post("/uploadGambar", function (Request $request, Response $response){       
        $body=$request->getParsedBody();
        $file=$request->getUploadedFiles();

        $uploadedFile=$file['image'];
        $extension=pathinfo($uploadedFile->getClientFileName(),PATHINFO_EXTENSION);
        $filename="testing".".".$extension;

        if ($uploadedFile!=null){
            $directory=$this->get('settings')['upload_directory'];
            $uploadedFile->moveTo($directory. DIRECTORY_SEPARATOR. $filename);

            return "asd";
        }

    });

    //member service
    $app->group('/member',function(\Slim\App $app)
    {
        $app->post("/register", function (Request $request, Response $response){       
            $input=$request->getParsedBody();     
            $class = new User();
            $result = $class->registerUser(
                $input['email'],
                $input['nama'],
                $input['password'],
                $input['tanggal_lahir'],
                $input['gender'],
                $input['tinggi'],
                $input['berat'],
                $input['type'],
                $input['tanggal_berat']
            );
            if ($result=="berhasil"){
                return $response->withJson(["status"=>"true","message"=>"Registration Success"]); 
            } else {
                return $response->withJson(["status"=>"false","message"=>$result]);
            }
        });

        $app->post("/updateProfile", function (Request $request, Response $response){       
            $input=$request->getParsedBody();     
            $class = new User();
            $result = $class->updateProfile(
                $input['uid'],
                $input['nama'],
                $input['tinggi'],
                $input['berat']
            );
            if ($result=="berhasil"){
                return $response->withJson(["status"=>"true","message"=>"Update Success"]); 
            } else {
                return $response->withJson(["status"=>"false","message"=>$result]);
            }
        });

        $app->post("/addWeight", function (Request $request, Response $response){       
            $input=$request->getParsedBody();     
            $class = new All();
            $result = $class->addWeight(
                $input['uid'],
                $input['berat'],
                $input['tanggal']
            );
            if ($result=="berhasil"){
                return $response->withJson(["status"=>"true","message"=>"Insert Success"]); 
            } else {
                return $response->withJson(["status"=>"false","message"=>$result]);
            }
        });
    });

    //trainer service
    $app->group('/trainer',function(\Slim\App $app)
    {

    });
    
    //admin service
    $app->group('/admin',function(\Slim\App $app)
    {
        $app->get("/getAllUserData", function (Request $request, Response $response){
            $class = new User();
            $result = $class->getAllData();

            return $response->getBody()->write((string)json_encode($result));
        });

        $app->post("/login", function (Request $request, Response $response){       
            $input=$request->getParsedBody();     
            $class = new Admin();
            $result = $class->loginAdmin($input['email'],$input['password']);
    
            return $response->getBody()->write((string)json_encode($result));
        });
    });
};

class All{
    public function __construct(){
        $factory = (new Factory)->withServiceAccount(__DIR__. '\secret\tugasakhir-273202-6ee1f9786c82.json');
        
        $database = $factory->createDatabase();
        $auth=$factory->createAuth();

        $this->auth=$auth;
        $this->database=$database;

    }

    public function login($email,$password){
        try {
            $signInResult = $this->auth->signInWithEmailAndPassword($email, $password);

            $signInResult->firebaseUserId();
            if ($signInResult){
                $data=$this->database->getReference("users")->getChild($signInResult->firebaseUserId())->getValue();
                // $data = [
                //     "uid"=>$signInResult->firebaseUserId(),
                //     "nama"=>$this->database->getReference("users")
                //         ->getChild($signInResult->firebaseUserId())
                //         ->getChild("nama")->getValue(),
                //     "type"=>$this->database->getReference("users")
                //     ->getChild($signInResult->firebaseUserId())
                //     ->getChild("type")->getValue()
                // ];
                $response=[
                    "data"=>$data,
                    "uid"=>$signInResult->firebaseUserId(),
                    "status"=>"true",
                    "message"=>"Login Successful",
                    "type"=>$this->database
                    ->getReference("users")
                    ->getChild($signInResult->firebaseUserId())
                    ->getChild("type")->getValue()
                ];
                return $response;    
            }
        } catch (Exception $e){
            $response=[
                "message"=>$e->getMessage(),
                "status="=>"false"
            ];
            return $response;
        }
    }

    public function getSuggestion($value){
        
    }

    public function addWeight($uid,$berat,$tanggal){
        $this->database->getReference("berat/".$uid."/".$tanggal)->set($tanggal);
        $this->database->getReference("berat/".$uid."/".$tanggal."/value")->set($berat);

        //update berat di db utama
        $this->database->getReference("users/".$uid."/berat")->set($berat);
    }

    public function getExercise($category){
        return array_values($this->database->getReference("exercise")->getChild($category)->getValue());
    }

    public function addExercise($category,$name,$desc){
        $postData=["name"=>$name , "desc"=>$desc];
        $postRef=$this->database->getReference("exercise/".$category)->push($postData);

        return $postRef->getKey();
        
        //$this->database->getReference("exercise/".$category."/".$name."/desc")->set($desc);

    }
}

class User {
    protected $database;
    protected $dbname='users';

    public function __construct(){
        $factory = (new Factory)->withServiceAccount(__DIR__. '\secret\tugasakhir-273202-6ee1f9786c82.json');
        
        $database = $factory->createDatabase();
        $auth=$factory->createAuth();

        $this->auth=$auth;
        $this->database=$database;

    }

    public function updateProfile($uid, $nama, $tinggi, $berat){
        try {            
            if ($this->database->getReference($this->dbname)->getSnapshot()->hasChild($uid)){
                $this->database->getReference("users/".$uid."/nama")->set($nama);
                $this->database->getReference("users/".$uid."/tinggi")->set($tinggi);
                $this->database->getReference("users/".$uid."/berat")->set($berat);

                return "berhasil";
            }
 
            return "gagal";
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function registerUser($email, $nama, $password, $tanggal_lahir, $gender,
    $tinggi, $berat, $type, $tanggal_berat){
        try{

            $request = \Kreait\Firebase\Request\CreateUser::new()
            ->withUnverifiedEmail($email)
            ->withClearTextPassword($password);
            $createUser = $this->auth->createUser($request);

            //get uid
            $result=$this->auth->signInWithEmailAndPassword($email,$password);
            $uid=$result->firebaseUserId();

            //getting data in
            $this->database->getReference("users/".$uid."/uid")->set($uid);
            $this->database->getReference("users/".$uid."/email")->set($email);
            $this->database->getReference("users/".$uid."/nama")->set($nama);
            $this->database->getReference("users/".$uid."/tanggal_lahir")->set($tanggal_lahir);
            $this->database->getReference("users/".$uid."/gender")->set($gender);
            $this->database->getReference("users/".$uid."/tinggi")->set($tinggi);
            $this->database->getReference("users/".$uid."/berat")->set($berat);
            $this->database->getReference("users/".$uid."/type")->set($type);
            $this->database->getReference("users/".$uid."/premium")->set("No");

            //updating berat
            $this->database->getReference("berat/".$uid."/".$tanggal_berat)->set($tanggal_berat);
            $this->database->getReference("berat/".$uid."/".$tanggal_berat."/value")->set($berat);

            return "berhasil";
        } catch (Exception $e) {
            return $e->getMessage();
        }
        
    }

    public function getData($uid){
        if ($this->database->getReference($this->dbname)->getSnapshot()->hasChild($uid)){
            return $this->database->getReference($this->dbname)->getChild($uid)->getValue();
        } else {
            return false; 
        }
    }

    public function getAllData(){
        return $this->database->getReference($this->dbname)->getValue();
    }

    // public function get(string $userID = NULL){
    //     if (empty($userID) || !isset($userID)) { return false; }

    //     if ($this->database->getReference($this->dbname)->getSnapshot()->hasChild($userID)){
    //         return $this->database->getReference($this->dbname)->getChild($userID)->getValue();
    //     } else {
    //         return false; 
    //     }
    // }

    public function insert(array $data){
        if (empty($data) || !isset($data)) { return false; }

        foreach ($data as $key => $value){
            $this->database->getReference()->getChild($this->dbname)->getChild($key)->set($value);
        }

        return true;

    }

    public function delete(int $userID){
        if (empty($userID) || !isset($userID)) { return false; }

        if ($this->database->getReference($this->dbname)->getSnapshot()->hasChild($userID)){
            $this->database->getReference($this->dbname)->getChild($userID)->remove();
            return true;
        } else {
            return false;
        }
    }
}

class Admin{
    protected $database;
    protected $dbname='users';

    public function __construct(){
        $factory = (new Factory)->withServiceAccount(__DIR__. '\secret\tugasakhir-273202-6ee1f9786c82.json');
        
        $database = $factory->createDatabase();
        $auth=$factory->createAuth();

        $this->auth=$auth;
        $this->database=$database;

    }

    public function loginAdmin($email,$password){
        try {
            $signInResult = $this->auth->signInWithEmailAndPassword($email, $password);

            $signInResult->firebaseUserId();
            if ($signInResult){
                //$data=$this->database->getReference("users")->getChild($signInResult->firebaseUserId())->getValue();
                $response=[                    
                    "status"=>"true",
                    "message"=>"Login Successful"
                ];
                return $response;    
            }
        } catch (Exception $e){
            $response=[
                "message"=>$e->getMessage(),
                "status="=>"false"
            ];
            return $response;
        }
    }
}

class Berat {
    protected $database;
    protected $dbname='berat';

    public function __construct(){
        $factory = (new Factory)->withServiceAccount(__DIR__. '\secret\tugasakhir-273202-6ee1f9786c82.json');
        
        $database = $factory->createDatabase();
        $auth=$factory->createAuth();

        $this->auth=$auth;
        $this->database=$database;

    }

    public function firstInsert($uid,$berat,$tanggal){

    }
}
