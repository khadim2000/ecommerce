<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Product extends Model
{
    protected $fillable = [
        'name', 'description', 'price', 'stock', 'size', 'color_id', 'category_id', 'image',
    ];

    protected $casts = [
        'size' => 'array',
        'color' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
   public function colors()
{
    return $this->belongsToMany(Color::class, 'color_products', 'product_id', 'color_id');
}


    public function cart()
    {
        return $this->hasMany(Cart::class);
    }

  /*  public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }*/
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function reviews()
    {
        return $this->hasMany(reviews::class);
    }

    public function wishlist()
    {
        return $this->hasMany(Wishlist::class);
    }
}

