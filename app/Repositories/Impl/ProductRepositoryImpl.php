<?php

namespace App\Repositories\Impl;

use App\Models\Product;
use App\Repositories\ProductRepository;

class ProductRepositoryImpl implements ProductRepository
{
    /**
     * Find a product by ID.
     */
    public function findById(int $id): ?Product
    {
        return Product::find($id);
    }

    /**
     * Find a product by SKU.
     */
    public function findBySku(string $sku): ?Product
    {
        return Product::where('sku', $sku)->first();
    }

    /**
     * Create a new product.
     */
    public function create(array $data): Product
    {
        return Product::create($data);
    }

    /**
     * Update an existing product.
     */
    public function update(Product $product, array $data): Product
    {
        $product->fill($data);
        $product->save();
        return $product;
    }

    /**
     * Delete a product.
     */
    public function delete(Product $product): bool
    {
        return $product->delete();
    }
}
