<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Color;
use Inertia\Inertia;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
class ProductController extends Controller
{
public function index(Request $request)
{
    $query = Product::with(['category', 'colors']);
    
    // Si un terme de recherche est fourni
    if ($request->has('search') && $request->search) {
        $searchTerm = $request->search;
        $query->where(function($q) use ($searchTerm) {
            $q->where('name', 'like', '%' . $searchTerm . '%')
              ->orWhere('description', 'like', '%' . $searchTerm . '%')
              ->orWhereHas('category', function($categoryQuery) use ($searchTerm) {
                  $categoryQuery->where('name', 'like', '%' . $searchTerm . '%');
              });
        });
    }
    
    return Inertia::render('Home', [
        'products' => $query->get(),
        'categories' => \App\Models\Category::all(),
        'search' => $request->search ?? '',
     
    ]);
}

// Dashboard admin
public function dashboard(Request $request)
{
    $query = Product::with(['category', 'colors']);
    
    // Si un terme de recherche est fourni
    if ($request->has('search') && $request->search) {
        $searchTerm = $request->search;
        $query->where(function($q) use ($searchTerm) {
            $q->where('name', 'like', '%' . $searchTerm . '%')
              ->orWhere('description', 'like', '%' . $searchTerm . '%')
              ->orWhereHas('category', function($categoryQuery) use ($searchTerm) {
                  $categoryQuery->where('name', 'like', '%' . $searchTerm . '%');
              });
        });
    }
    
    return Inertia::render('Dashboard', [
        'products' => $query->get(),
        'categories' => \App\Models\Category::all(),
        'search' => $request->search ?? '',
        'auth' => [
            'user' => auth()->user(),
        ],
    ]);
}


    public function show($id)
    {
        $product = Product::with(['category', 'colors'])->findOrFail($id);
        
        return Inertia::render('ProductDetail', [
            'product' => $product,
            'auth' => [
                'user' => auth()->user(),
            ],
        ]);
    }

    // Méthode pour l'interface admin
    public function adminIndex(Request $request)
    {
        $query = Product::with(['category', 'colors']);
        
        // Si un terme de recherche est fourni
        if ($request->has('search') && $request->search) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%')
                  ->orWhereHas('category', function($categoryQuery) use ($searchTerm) {
                      $categoryQuery->where('name', 'like', '%' . $searchTerm . '%');
                  });
            });
        }
        
        return response()->json($query->get());
    }

    // Méthode API pour récupérer tous les produits en JSON
    public function apiIndex(Request $request)
    {
        $query = Product::with(['category', 'colors']);
        
        // Si un terme de recherche est fourni
        if ($request->has('search') && $request->search) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%')
                  ->orWhereHas('category', function($categoryQuery) use ($searchTerm) {
                      $categoryQuery->where('name', 'like', '%' . $searchTerm . '%');
                  });
            });
        }
        
        return response()->json($query->get());
    }
 public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'reference' => 'nullable|string|unique:products',
            'image' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'color_ids' => 'required|array|exists:colors,id',
            'size_ids' => 'nullable|array',
        ]);

        $product = Product::create($request->only([
            'name', 'description', 'price', 'stock', 'reference', 'image','category_id'
        ]));

        // Ajouter les couleurs
        if ($request->has('color_ids')) {
            $product->colors()->sync($request->color_ids);
        }

        // Ajouter les tailles (stockage en JSON)
        if ($request->has('size_ids')) {
            $product->update(['size' => $request->size_ids]);
        }

        return response()->json([
            'message' => 'Produit créé avec succès',
            'product' => $product->load('colors'),
        ], 201);
    }

    // Récupérer un produit par ID avec ses couleurs
    public function showColor($id)
    {
        $product = Product::with('colors')->findOrFail($id);
        return response()->json($product);
    }
public function update(Request $request, $id)
{
    $product = Product::findOrFail($id);

    $validated = $request->validate([
        'name' => 'sometimes|required|string|max:255',
        'description' => 'nullable|string',
        'price' => 'sometimes|required|numeric',
        'stock' => 'sometimes|required|integer',
        'size_ids' => 'nullable|array',
        'category_id' => 'sometimes|required|exists:categories,id',
        'image' => 'nullable|string',
        'color_ids' => 'nullable|array',
        'color_ids.*' => 'integer|exists:colors,id',
    ]);

    // Mettre à jour les champs de base
    $product->update($request->only([
        'name', 'description', 'price', 'stock', 'reference', 'image', 'category_id'
    ]));

    // Mettre à jour les couleurs
    if ($request->has('color_ids')) {
        $product->colors()->sync($request->color_ids);
    }

    // Mettre à jour les tailles
    if ($request->has('size_ids')) {
        $product->update(['size' => $request->size_ids]);
    }

    return response()->json([
        'message' => 'Produit modifié avec succès',
        'product' => $product->load('colors')
    ]);
}


    public function destroy($id)
    {
        
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json(null, 204);
    }
}
