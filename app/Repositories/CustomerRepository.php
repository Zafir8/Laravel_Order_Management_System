<?php

namespace App\Repositories;

use App\Models\Customer;

interface CustomerRepository
{
    /**
     * Find a customer by ID.
     *
     * @param int $id
     * @return Customer|null
     */
    public function findById(int $id): ?Customer;

    /**
     * Find a customer by email.
     *
     * @param string $email
     * @return Customer|null
     */
    public function findByEmail(string $email): ?Customer;

    /**
     * Create a new customer.
     *
     * @param array $data
     * @return Customer
     */
    public function create(array $data): Customer;

    /**
     * Update an existing customer.
     *
     * @param Customer $customer
     * @param array $data
     * @return Customer
     */
    public function update(Customer $customer, array $data): Customer;

    /**
     * Delete a customer.
     *
     * @param Customer $customer
     * @return bool
     */
    public function delete(Customer $customer): bool;
}
