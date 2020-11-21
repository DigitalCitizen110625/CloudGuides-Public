<?php declare(strict_types=1);

require_once('../../Php/Mysql.config.php');

/*
* 	DESCRIPTION	: Situated between the entity layer (class Post) and access object layer (PersistentStorage).
*                 Provides a means of accessing the records held in long term storage, in a standardized manner. 
*/
class PostRepository
{
    private PersistentStorage $persistence;


    public function __construct(PersistentStorage $persistence)
    {
        $this->persistence = $persistence;
    }


    /*
    * 	DESCRIPTION	: Queries the database, at the specified table, for a record with matching values
    *	PARAMETERS	: int $limit : The number of rows/records to return from the query
    *                 array $keyValues : The column names, and values to query for (e.g. For SELECT * FROM posts WHERE id = 1, keyValues = ['id'] => 1)
    *	RETURNS		: array : Contains all rows that matched the query, or an empty array if no matches were found
    */
    public function dynamicQuery(int $limit = NULL, array $columns, array $keyValues, string $table = POST_TABLE) : array
    {
        return $postArray = $this->persistence->dynamicQuery($table, $limit, $columns, $keyValues);
    }


    /*
    * 	DESCRIPTION	: Queries the database using a command string. This string will 
    *                 not be altered in the subsequent actions (unlike the "get" method)
    *	PARAMETERS	: string $Command : Query string used to query the database
    *	RETURNS		: array : Associative array containing all ther rows for the query
    */
    public function query(string $command) : array
    {
        return $this->persistence->query($command);
    }


    /* 
    * 	DESCRIPTION	: Updates the selected record if an id is provided, or creates a new one
    *	PARAMETERS	: array $data : Associative array of the data to insert or used to update the target record
    *                 int $id = null :  Id of the record to update if provided
    *                 string $table = POST_TABLE : Table to insert into, or update
    *	RETURNS		: string or int : Indicates the number of rows affected if update is called, or the records id, if create is called
    */
    public function upsert(array $data, int $id = null, string $table = POST_TABLE)
    {
        //If an id was provided, then we want to update the entity
        if(!is_null($id) )
        {
            return $this->persistence->update($table, $id, $data);
        }

        //No id provided, so the entity is new, and needs to be inserted
       return $this->persistence->create($table, $data);
    }


    /* 
    * 	DESCRIPTION	: Inserts an entity into the specified table, in the database
    *	PARAMETERS	: string $table = POST_TABLE : Table to insert the new data. Defaulted to the post table, or not specified
    *                 array $entity : Associative array containing the new data to insert
    *	RETURNS		: string : Id of the newly inserted entity, or an exception on error
    */
    public function insert(array $entity, string $table = POST_TABLE)
    {
       return $this->persistence->create($table, $entity);
    }


    /*
    * 	DESCRIPTION	: Updates a record with the matching id, from the specified table, to the values in the data array
    *	PARAMETERS	: int $id : Id of the row to update in the target table
    *		          array $data : Contains the updated values for the record 
    *		          stirng $table : Table of where to update the record
    *	RETURNS		: int : Count of rows/records updated
    */
    public function update(array $data, int $id, string $table = POST_TABLE)
    {
        return $this->persistence->update($table, $id, $data);
    }


    /*
    * 	DESCRIPTION	: Deletes a record with a matching id, from the specified table
    *	PARAMETERS	: int $id : Id of the row to delete in the target table
    *	RETURNS		: int : Count of records/rows deleted
    */
    public function delete(int $id, string $table = POST_TABLE)
    {
        return $this->persistence->delete($table, $id);
    }
}