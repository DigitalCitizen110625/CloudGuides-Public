<?php declare(strict_types=1);


/*
* 	DESCRIPTION	: Situated between the entity layer (class Account) and access object layer (PersistentStorage).
*                 Provides a means of accessing the records held in long term storage, in a standardized manner. 
*/
class AccountRepository
{

    private PersistentStorage $persistence; //Impliments the PersistentStorage interface, and is used to access the long term storage where the records are kept


    public function __construct(PersistentStorage $persistence)
    {
        $this->persistence = $persistence;
    }


    /*
    * 	DESCRIPTION	: Finds an account with the matching id
    *	PARAMETERS	: string $table : Tale name of where to query 
    *                 int $limit : Number of records to return in the query
    *                 array $keyValues : Key-value pairs containing the query properties, and their values
    *	RETURNS		: array : Collection of all matching records
    */
    public function get(string $table, int $limit, array $columns, array $keyValues) : array
    {
        return $this->persistence->dynamicQuery($table, $limit, $columns, $keyValues);
    }


    /*
    * 	DESCRIPTION	: Uses the PersistentStorage to create an account in the database
    *	PARAMETERS	: string $table  : The table name of where to create the account 
    *                 array $account : Array containing the account details
    *	RETURNS		: String : Id of the newley created account
    */
    public function create(string $table, array $account) : string
    {
        return $this->persistence->create($table,$account);
    }   

    /*
    * 	DESCRIPTION	: Uses the PersistentStorage to update records in a specific table
    *	PARAMETERS	: string $table : The table name of where to update the records
    *                 int $id : Id of the record to update
    *                 array $account : Contains the new accoutn details
    *	RETURNS		: int : Affected rows count
    */
    public function update(string $table, int $id, array $account) : int
    {
        return $this->persistence->update($table, $id, $account);
    }

    /*
    * 	DESCRIPTION	: uses the PersistentStorage to delete a record in a table
    *	PARAMETERS	: string $table  : The table name of where to delete the record
    *                 int $id        : Id of the record to delete
    *	RETURNS		: int : Affected rows count
    */
    public function delete(string $table, int $id) : int
    {
        return $this->persistence->delete($table, $id);
    }
}