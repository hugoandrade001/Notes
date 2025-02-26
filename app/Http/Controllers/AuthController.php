<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB as FacadesDB;

class AuthController extends Controller
{
    public function login() {
        return view("login");
    }

    public function logout() {
        // logout from application
        session()->forget("user");
        return redirect()->to("/login");
    }
    public function loginSubmit(Request $request) {


        ///form validation 
        $request->validate(
            [
            "text_username" => "required|email", 
            "text_password" => "required|min:6|max:16"
            ],
            [
                "text_username.required" => "o username é obrigatorio",
                "text_username.email" => "o username deve ser um email valido",
                "text_password.required" => "a password é obrigatorio",
                "text_password.max" => "a password deve ter pelo menos :min caracteres",
                "text_password.min" => "a password deve ter no maximo :max caracteres",


            ]
        );

        $username = $request->input("text_username");
        $password = $request->input("text_password");

        // test database connection

        try {
            FacadesDB::connection()->getPdo();
            echo "connection is ok";
        } catch (\PDOException $e) {
            echo "connection failed: " . $e->getMessage();
        }

        //get all the users from database

        //$userModel = new User();
        //$users = $userModel->all()->toArray()
        //echo "<prev>";
        //print_r($users);

        //check if user exist
        $user = User::where("username", $username) ->where("deleted_at", NULL)->first();

        if(!$user) {
            return redirect()
            ->back()
            ->withInput()
            ->with("loginError", "Username ou password incorretos");
        }
        //check if passowrd is correct

        if(!password_verify($password, $user->password)) {
            return redirect()
            ->back()
            ->withInput()
            ->with("loginError", "Username ou password incorretos");

        }

        //update last login

        $user->last_login = date("Y-m-y H:i:s");
        $user->save();

        //login user
        session([
            "user" => [
                "id" => $user->id, 
                "username" => $user->username
            ]
            ]);

        return redirect() -> to("/");
        //print_r($user);

        
    }
}
