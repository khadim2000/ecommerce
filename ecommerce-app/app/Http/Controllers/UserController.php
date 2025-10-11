<?php

namespace App\Http\Controllers;

use App\Models\User;            // <-- importer le modèle User
use Illuminate\Http\Request;
use Inertia\Inertia;           // <-- importer Inertia

class UserController extends Controller
{
      public function index()
    {
        $users = User::all(); // Récupérer tous les utilisateurs
        return response()->json($users, 200); // Retourner la liste des utilisateurs
    }

}
