<?php declare(strict_types=1);

require_once("../Mysql.config.php");
require_once("../interfaces/PersistentStorage.php");

/*
* 	DESCRIPTION	: Concrete implementation of the PersistentStorage handler. Responsible for 
*                 performing all CRUD related operations with a mySQL database
*/
class MySqlDatabase implements PersistentStorage
{
    private $connection;             //Mysql connection instance

    public function __construct()
    {
        $this->connect();
    }

        
    /*
    * 	DESCRIPTION	: Opens a connection to the mysql database
    *	PARAMETERS	: NA
    *	RETURNS		: NA
    */
    private function connect()
    {

        try
        {
            if(DB_USE_LOCAL === TRUE)
            {
                //Connect to the local MySQL database
                $this->connection = new MySQLi(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
            }
            else
            {
                //Connect to the remote MySQL database
                $this->connection = new MySQLi(DB_REMOTE_HOST, DB_REMOTE_USER, DB_REMOTE_PASSWORD, DB_REMOTE_DATABASE, DB_REMOTE_PORT);
            }
    
            //Ensure there were no connection errors
            if($this->connection->connect_error)
            {
                throw new Exception("Connection Error: " . $this->connection->connect_error);
            }
        }
        catch (Exception $e)
        {
            //Log the connection error
            $e->getMessage();
        }
    }

        
    /*
    * 	DESCRIPTION	: Completes the final portion of any command sent to the database, by preparing the commands, and executing them at the database
    *	PARAMETERS	: string $command : A template, and unprepared SQL command
    *                 array $data : Contains the values to sub into the SQL command during preperation/binding
    *	RETURNS		: mysqli_stmt : Represents the prepared statement that was just executed
    */
    private function executeQuery(string $command, array $data)
    {
        if(!isset($this->connection))
        {
            throw new Exception("Connection Error: Connection not set");
        }

        //Grab the connnection data from the include file
        $stmt = $this->connection-> prepare($command);
        if ($stmt === FALSE) 
        {
            throw new Exception("Mysql Error: " . $this->connection->error);
        }

        $values = array_values($data);
        $types = str_repeat ('s', count($values));
        $stmt-> bind_param($types, ...$values);
        $stmt->execute();
        return $stmt;
    }


    /*
    * 	DESCRIPTION	: Queries the database using the provided command
    *	PARAMETERS	: string $queryString : Complete query command
    *	RETURNS		: array : Collection of all rows that matched the query
    */
    public function query(string $queryString) : array
    {

        //Ensure the query string is provided
        if(!isset($queryString))
        {
            throw new InvalidArgumentException("Error: query command must be provided");
        }

        $this->connect();
        $result = $this->connection->query($queryString);
        $resultArray = [];

        //Ensure the query was completed successfully, even if no records were returned
        if($result === false)
        {
            throw new InvalidArgumentException("Error: query command was invalid");
        }

        //Check 
        if($result->num_rows >0)
        {
            while($row = $result->fetch_assoc())
            {
                array_push($resultArray, $row);
            }
        }

        $this->connection->close();
        return $resultArray;
    }



    /*
    * 	DESCRIPTION	: Builds a query string using the provided arguments, and executes the query once complete
    *	PARAMETERS	: string $table : Table name of where to query
    *                 int $limit : The total number of rows that can be returned from the query
    *                 array $columns : The columns of include in the return array 
    *                 array $keyValues : Collection of key-value pairs used to query the rows (e.g. WHERE key = value, or WHERE id = xyz)
    *	RETURNS		: array : Collection of all rows that matched the query
    */
    public function dynamicQuery(string $table, int $limit = null, array $columns = [], array $keyValues = []): array
    {     

        //The database table must be defined for the query to work
        if(!isset($table))
        {
            throw new InvalidArgumentException("Error: Target table must be provided");
        }


        //If no columns were explicitly defined, then query all columns in the table
        $command = '';
        if(!empty($columns))
        {
            //Example: SELECT coumn1, column2, column4 FROM...
            $command = "SELECT ";
            $i = 0;
            $columnCount = count($columns);
            foreach($columns as $column)
            {
                //Final selected column can't have a comma before the FROM keyword
                if ($i === $columnCount - 1)
                {
                    $command .= "$column FROM $table ";
                }
                else
                {
                    $command .= "$column, ";
                }
                $i++;
            }
        }
        else
        {
            $command = "SELECT * FROM $table ";
        }



        $i = 0;
        //Query params are defaulted to null, so only append the key=value params if they'er explicitly defined
        foreach($keyValues as $key => $value)
        {
            if($i === 0)
            {
                $command .= "WHERE $key=? ";
            }
            else
            {
                $command .= "AND $key=? ";
            }
            $i++;
        }

        //Limit the record count if provided
        if(isset($limit))
        {
            $command .= "LIMIT $limit";
        }

        $this->connect();
        $stmt = $this->connection-> prepare($command);

        if(!empty($keyValues))
        {
            //Pull out the values from the key-value pairs
            $queryValues = array_values($keyValues);

            $types = str_repeat ('s', count($queryValues));
            $stmt-> bind_param($types, ...$queryValues);
        }

        //Executre the query and place the results into an associative array
        $stmt->execute();
        $queryResult = $stmt-> get_result()-> fetch_all(MYSQLI_ASSOC);

        //Close the connection
        $this->connection->close();
        return $queryResult;

    }
    

    /*
    * 	DESCRIPTION	: Inserts a new row with the provided data, into the target table
    *	PARAMETERS	: string $table  : Table of where to insert the new row
    *                 array $data : Values of the new row
    *	RETURNS		: string : Id of the newly inserted row, or null if an error occured
    */
    public function create(string $table, array $data) : string
    {
        //Command Example: INSERT INTO table SET key=? value=?...
        $sql = "INSERT INTO $table SET ";

        $i = 0;
        foreach($data as $key => $value)
        {
            if($i === 0)
            {
                $sql = $sql . "$key=? ";
            }
            else
            {
                $sql = $sql . ", $key=? ";
            }
            $i++;
        }

        //Executre the command and return the records id
        $this->connect();
        $stmt = $this->executeQuery($sql, $data);
        $recordId = strval($stmt-> insert_id);

        //Close the connection
        $this->connection->close();
        return $recordId;
    }
    

    /*
    * 	DESCRIPTION	: Updates a row in the specied table, with a matching id, to the data provided in the data array
    *	PARAMETERS	: string $table : Table where to update the row
    *                 int $id : If of the row to update
    *                 array $data : New values to update the row to 
    *	RETURNS		: int : Number of rows updated
    */
    public function update(string $table, int $id, array $data) : int
    {
        //Command Example: UPDATE table SET property = value, property2 = value2...WHERE id = ?
        $sql = "UPDATE $table SET ";

        $i = 0;
        foreach($data as $key => $value)
        {
            if($i === 0)
            {
                $sql = $sql . "$key=? ";
            }
            else
            {
                $sql = $sql . ", $key=? ";
            }
            $i++;
        }
        $sql = $sql . " WHERE id = $id ";

        //Execute the update command, and return a count of the affecrted rows
        $this->connect();
        $stmt = $this->executeQuery($sql, $data);
        $afftectedRowsCount = $stmt-> affected_rows;

        //Close the connection
        $this->connection->close();
        return $afftectedRowsCount;
    }


    /*
    * 	DESCRIPTION	: Deletes a row from the specified table, with a matching id
    *	PARAMETERS	: string $table : Table where to delete the row from
    *                 int $id : Id of the row to delete
    *	RETURNS		: int : Number of rows affected by the delete operation
    */
    public function delete(string $table, int $id) : int
    {
        //DELETE FROM table WHERE id = value
        $sql = "DELETE FROM $table WHERE id=? ";

        //Execute the delete command, and reutrn a count of the affected rows
        $this->connect();
        $stmt = $this->executeQuery($sql, ['id' => $id]);
        $afftectedRowsCount = $stmt-> affected_rows;

        $this->connection->close();
        return $afftectedRowsCount;
    }
}