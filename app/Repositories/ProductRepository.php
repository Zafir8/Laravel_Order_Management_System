<?php

namespace App\Repositories;

use App\Models\Product;

interface ProductRepository
{
    /**
     * Find a product by ID.
     *
     * @param int $id
     * @return Product|null
     */
    public function findById(int $id): ?Product;

    /**
     * Find a product by SKU.
     *
     * @param string $sku
     * @return Product|null
     */
    public function findBySku(string $sku): ?Product;

    /**
     * Create a new product.
     *
     * @param array $data
     * @return Product
     */
    public function create(array $data): Product;

    /**
     * Update an existing product.
     *
     * @param Product $product
     * @param array $data
     * @return Product
     */
    public function update(Product $product, array $data): Product;

    /**
     * Delete a product.
     *
     * @param Product $product
     * @return bool
     */
    public function delete(Product $product): bool;
}
