<?php

namespace App\Repositories\Impl;

use App\Models\Customer;
use App\Repositories\CustomerRepository;

class CustomerRepositoryImpl implements CustomerRepository
{
    /**
     * Find a customer by ID.
     */
    public function findById(int $id): ?Customer
    {
        return Customer::find($id);
    }

    /**
     * Find a customer by email.
     */
    public function findByEmail(string $email): ?Customer
    {
        return Customer::where('email', $email)->first();
    }

    /**
     * Create a new customer.
     */
    public function create(array $data): Customer
    {
        return Customer::create($data);
    }

    /**
     * Update an existing customer.
     */
    public function update(Customer $customer, array $data): Customer
    {
        $customer->fill($data);
        $customer->save();
        return $customer;
    }

    /**
     * Delete a customer.
     */
    public function delete(Customer $customer): bool
    {
        return $customer->delete();
    }
}
