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

    $app->get("/getDetailExercise", function (Request $request, Response $response){       
        $input=$request->getParsedBody();     
        $class = new All();
        $result = $class->getDetailExercise($input['uid_exercise']);
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

    $app->get("/getImage", function (Request $request, Response $response){       
        $input=$request->getParsedBody();     
        $class = new All();
        $result = $class->getImage($input['filename']);

        return $response->getBody()->write((string)json_encode($result));
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

        $app->put("/updateProfile", function (Request $request, Response $response){       
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

        $app->post("/addExerciseToFav", function (Request $request, Response $response){       
            $input=$request->getParsedBody();     
            $class = new User();
            $result = $class->addExerciseFav(
                $input['uid_user'],
                $input['uid_exercise'],
                $input['category1'],
                $input['category2'],
                $input['nama'],
                $input['desc'],
                $input['picture'],
                $input['video']
            );
            if ($result['status']=="true"){
                return $response->withJson(["status"=>"true","message"=>"Insert Success"]); 
            } else {
                return $response->withJson(["status"=>"false","message"=>$result]);
            }
        });

        $app->get("/getUserFavExercise/{id}", function (Request $request, Response $response, Array $args){       
                
            $class = new User();
            $result = $class->getUserFav($args['id']);    

            return $response->getBody()->write((string)json_encode($result));
            // if ($result['status']=="true"){
            //     return $response->withJson(["status"=>"true","message"=>"Insert Success"]); 
            // } else {
            //     return $response->withJson(["status"=>"false","message"=>$result]);
            // }
        });

        $app->put("/removeUserFavExercise", function (Request $request, Response $response, Array $args){       
            $input=$request->getParsedBody();
            $class = new User();
            $result = $class->removeUserFav($input['id_user'],$input['id_exer']);    

            if ($result=="berhasil"){
                return $response->withJson(["status"=>"true","message"=>"Delete Success"]); 
            } else {
                return $response->withJson(["status"=>"false","message"=>$result]);
            }

            // return $response->getBody()->write((string)json_encode($result));
        });

        $app->get("/getFavOrNot/{id_user}/{id_exe}", function (Request $request, Response $response, Array $args){      
            $class= new User();

            $result = $class->getFavOrNot($args['id_user'],$args['id_exe']);    

            $hasil=[
                "message"=>$result
            ];

            return $response->getBody()->write((string)json_encode($hasil));
        });

        $app->get("/getAllWorkouts", function (Request $request, Response $response, Array $args){           
            $class = new User();
            $result = $class->getAllWorkouts();    

            return $response->getBody()->write((string)json_encode($result));
            // if ($result['status']=="true"){
            //     return $response->withJson(["status"=>"true","message"=>"Insert Success"]); 
            // } else {
            //     return $response->withJson(["status"=>"false","message"=>$result]);
            // }
        });

        $app->get("/getAllTemp", function (Request $request, Response $response, Array $args){           
            $class = new User();
            $result = $class->getAllTemp();    

            return $response->getBody()->write((string)json_encode($result));
            // }
        });

        $app->get("/getWorkouts", function (Request $request, Response $response, Array $args){           
            $input=$request->getParsedBody();
            $class = new User();
            $result = $class->getWorkouts($input['uid']);    

            return $response->getBody()->write((string)json_encode($result));
            // }
        });

    });

    //trainer service
    $app->group('/trainer',function(\Slim\App $app)
    {
        $app->post("/addNewWorkouts", function (Request $request, Response $response){       
            $input=$request->getParsedBody();     
            $class = new Trainer();
            $result = $class->addNewWorkouts($input['creator'],
            $input['name'],$input['desc'],$input['category'],$input['duration'],
            $input['level'],$input['picture'],$input['exercises']);
            
            if ($result['status']=="true"){
                return $response->withJson(["status"=>"true","message"=>"Insert Success"]); 
            } else {
                return $response->withJson(["status"=>"false","message"=>$result]);
            }
            
        });

        $app->post("/test", function (Request $request, Response $response){       
            $input=$request->getParsedBody();     
            $class = new Trainer();
            $result = $class->test($input['exercises']);
            
            // if ($result['status']=="true"){
            //     return $response->withJson(["status"=>"true","message"=>"Insert Success"]); 
            // } else {
            //     return $response->withJson(["status"=>"false","message"=>$result]);
            // }
            return $result;
        });
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

        $app->post("/addExercise", function (Request $request, Response $response){       
            $input=$request->getParsedBody();     
            $class = new All();
            $result = $class->addExercise($input['category1'],$input['category2'],$input['nama'],$input['desc'],$input['picture'],$input['video']);
            return $response->getBody()->write((string)json_encode($result));
        });

        $app->get("/getAllExercise", function (Request $request, Response $response){       
            $input=$request->getParsedBody();     
            $class = new All();
            $result = $class->getAllExercise();
            return $response->getBody()->write((string)json_encode($result));
        });

        $app->post("/activateUser", function (Request $request, Response $response){       
            $input=$request->getParsedBody();     
            $class = new Admin();

            $result = $class->activateUser($input['uid']);
            return $response->getBody()->write((string)json_encode($result));
        });

        $app->post("/deactivateUser", function (Request $request, Response $response){       
            $input=$request->getParsedBody();     
            $class = new Admin();

            $result = $class->deactivateUser($input['uid']);
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

        return "berhasil";
    }

    public function getExercise($category){
        return array_values($this->database->getReference("exercise")->getChild($category)->getValue());
    }

    public function getImage($filename){ 
    }

    public function addExercise($ctg1,$ctg2,$name,$desc,$picture,$video){
        try{
            $postData=["name"=>$name , "desc"=>$desc,"category1"=>$ctg1,"category2"=>$ctg2, "picture"=>$picture,"video"=>$video];
            $postRef=$this->database->getReference("exercise")->push($postData);
            
            $this->database->getReference("exercise/".$postRef->getKey()."/uid")->set($postRef->getKey());

            $response=[
                "message"=>"Add Exercise Success",
                "status"=>"true"
            ];

            return $response;
        } catch (Exception $e){
            $response=[
                "message"=>$e->getMessage(),
                "status"=>"false"
            ];
            return $response;
        }
    }

    public function getAllExercise(){
        return array_values($this->database->getReference("exercise")->getValue());
    }

    public function getDetailExercise($uid){
        return $this->database->getReference("exercise/".$uid)->getValue();
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

    public function addExerciseFav($uid_user,$uid_exercise,$category1,$category2,$nama,$desc,$picture,$video){
        $postData=[
            "uid"=>$uid_exercise,
            "category1"=>$category1,
            "category2"=>$category2,
            "name"=>$nama,
            "desc"=>$desc,
            "picture"=>$picture,
            "video"=>$video
        ];

        $postRef=$this->database->getReference('user-exercise/'.$uid_user)->push($postData);
        $response=[
            "message"=>"Add Favourites Success",
            "status"=>"true"
        ];

        return $response;
    }

    public function getUserFav($uid_user){
        return array_values($this->database->getReference('user-exercise/'.$uid_user)->getValue());
    }

    public function getFavOrNot($id_user,$id_exercise){
        if ($this->database->getReference('user-exercise')->getSnapshot()->hasChild($id_user))
        {
            //ada
            $val=$this->database->getReference('user-exercise/'.$id_user)->getValue();

            foreach ($val as $value){
                if ($value["uid"]==$id_exercise){
                    $message="Ada";
                    break;
                } else {
                    $message="Tidak Ada";
                }
            }          
        } else {
            $message="Tidak Ada";
        }
        return $message;  
    }

    public function removeUserFav($id_user,$id_exercise){
        
        $reference=$this->database->getReference('user-exercise/'.$id_user)->orderByChild('uid')->equalTo($id_exercise);
        $snapshot=$reference->getSnapshot();

        $value = $snapshot->getValue();
        $yourKey="";

        foreach ($value as $key => $value){
            $yourKey=$key;
        }

        $this->database->getReference('user-exercise/'.$id_user.'/'.$yourKey)->set(null);

        return "berhasil";

    }

    public function updateProfile($uid, $nama, $tinggi, $berat){
        try {  
            if ($this->database->getReference($this->dbname)->getSnapshot()->hasChild($uid)){

                $this->database->getReference("users/".$uid."/nama")->set($nama);
                $this->database->getReference("users/".$uid."/tinggi")->set($tinggi);
                $this->database->getReference("users/".$uid."/berat")->set($berat);

                return "berhasil";

                //how to update
                // $postData=[
                //     "nama"=>$nama,
                //     "tinggi"=>$tinggi
                // ];

                // $updates=[
                //     "users/".$uid => $postData
                // ];

                // if ($this->database->getReference()->update($updates)){
                //     return "berhasil";
                // }
            
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
            $this->database->getReference("users/".$uid."/aktif")->set("Yes");

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

    public function getAllTemp(){
        return array_values($this->database->getReference('workouts')->getValue());
    }

    public function getWorkouts($uid){
        return $this->database->getReference('workouts/'.$uid)->getValue();
    }

    public function getAllWorkouts(){
        $temp1=$this->database->getReference('workouts')->getValue();
    
        foreach ($temp1 as $value){
            $category=$value['category'];
            $level=$value['level'];
            $duration=$value['duration'];
            $picture=$value['picture'];
            $uid=$value['uid'];

            $array[$value['uid']]["category"]=$category;
            $array[$value['uid']]["level"]=$level;
            $array[$value['uid']]["duration"]=$duration;
            $array[$value['uid']]["picture"]=$picture;
            $array[$value['uid']]["uid"]=$uid;

            // $temp2=$this->database->getReference('workouts/wo1/exercises')->getValue();

            // foreach ($temp2 as $val){
            //     $array[$value['uid']]["exercises"][$val['uid']]['desc']=$val['desc'];
            //     $array[$value['uid']]["exercises"][$val['uid']]['duration']=$val['duration'];
            //     $array[$value['uid']]["exercises"][$val['uid']]['nama']=$val['nama'];
            //     $array[$value['uid']]["exercises"][$val['uid']]['picture']=$val['picture'];
            // }
    
        }  

        return array_values($array);
        // return $temp1;
        //return array_values($this->database->getReference('workouts')->getValue());
    }
}

class Trainer{
    public function __construct(){
        $factory = (new Factory)->withServiceAccount(__DIR__. '\secret\tugasakhir-273202-6ee1f9786c82.json');
        
        $database = $factory->createDatabase();
        $auth=$factory->createAuth();

        $this->auth=$auth;
        $this->database=$database;

    }

    public function addNewWorkouts($creator,$name,$desc,$category,$duration,$level,$picture,$exercises){
        try{
            $postData=["creator"=>$creator,"name"=>$name,"desc"=>$desc,"category"=>$category,"duration"=>$duration,"level"=>$level,"picture"=>$picture];
            $postRef=$this->database->getReference("workouts")->push($postData);
            
            $this->database->getReference("workouts/".$postRef->getKey()."/uid")->set($postRef->getKey());
    
            foreach (json_decode($exercises,true) as $value) {
                $postData2=[
                    "desc"=>$value['desc'],
                    "duration"=>$value['durasi'],
                    "nama"=>$value['name'],
                    "picture"=>$value['picture']
                ];

                $postRef2=$this->database->getReference("workouts/".$postRef->getKey()."/exercises")->push($postData2);

                $this->database->getReference("workouts/".$postRef->getKey()."/exercises/".$postRef2->getKey()."/uid")->set($postRef2->getKey());
            }

            $response=[
                "message"=>"Add Workouts Success",
                "status"=>"true"
            ];

        } catch (Exception $e){
            $response=[
                "message"=>$e->getMessage(),
                "status"=>"false"
            ];
        }
        return $response;
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

    public function activateUser($uid){
        try {
            if ($this->database->getReference('users')->getChild($uid)->getValue()){
                $this->database->getReference("users/".$uid."/aktif")->set("Yes");
                $response=[                    
                    "status"=>"true",
                    "message"=>"Update Succes"
                ];
            } else {
                $response=[
                    "message"=>"wrong uid",
                    "status="=>"false"
                ];
            }
        } catch (Exception $e){
            $response=[
                "message"=>$e->getMessage(),
                "status="=>"false"
            ];
        }
        return $response;
    }

    public function deactivateUser($uid){
        try {
            if ($this->database->getReference('users')->getChild($uid)->getValue()){
                $this->database->getReference("users/".$uid."/aktif")->set("No");
                $response=[                    
                    "status"=>"true",
                    "message"=>"Update Succes"
                ];
            } else {
                $response=[
                    "message"=>"wrong uid",
                    "status="=>"false"
                ];
            }
        } catch (Exception $e){
            $response=[
                "message"=>$e->getMessage(),
                "status="=>"false"
            ];
        }
        return $response;
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
