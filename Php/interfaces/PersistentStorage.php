<?php declare(strict_types=1);

interface PersistentStorage
{
    //Queries the database using the provided string
    public function query(string $queryString): array;

    //Construct a query string from the provided arguments before querying the database
    public function dynamicQuery(string $table, int $limit = null, array $keyValues = []): array;

    //Creates a new record in the database
    public function create(string $table, array $data) : string;

    //Finds a record with the matching id, and saves the new data to it
    public function update(string $table, int $id, array $data) : int;

    //Deletes a record with the matching id
    public function delete(string $table, int $id) : int;
}