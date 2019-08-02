<?php

/*
 * 
 * @Version 1.0
 * @Package Database
 */

class Database
{
    /*
     * Create variables for credentials to MySQL database
     * The variables have been declared as private. This
     * means that they will only be available with the
     * Database class
     */
    private $db_host = "localhost";  // Change as required
    private $db_user = "zenda";  // Change as required
    private $db_pass = "h53LFeyP";  // Change as required
    private $db_name = "zenda_shopify";    // Change as required

    /*
     * Extra variables that are required by other function such as boolean con variable
     */
    private $con = false; // Check to see if the connection is active
    private $myconn = ""; // This will be our mysqli object
    private $result = array(); // Any results from a query will be stored here
    private $myQuery = "";// used for debugging process with SQL return
    private $numResults = "";// used for returning the number of rows

    // Function to make connection to database
    function __construct()
    {
        $this->connect();
    }

    public function getRequestParam($paramString)
    {
        if (isset($_POST[$paramString]) || isset($_GET[$paramString])) {
            if (isset($_POST[$paramString])) {
                return $_POST[$paramString];
            } else {
                return $_GET[$paramString];
            }
        }

        return false;
    }

    public function connect()
    {
        if (!$this->con) {
            $this->myconn = new mysqli($this->db_host, $this->db_user, $this->db_pass,
                $this->db_name);  // mysql_connect() with variables defined at the start of Database class
            if ($this->myconn->connect_errno > 0) {
                array_push($this->result, $this->myconn->connect_error);
                return false; // Problem selecting database return FALSE
            } else {
                $this->con = true;
                return true; // Connection has been made return TRUE
            }
        } else {
            return true; // Connection has already been made return TRUE
        }
    }

    // Function to disconnect from the database
    public function disconnect()
    {
        // If there is a connection to the database
        if ($this->con) {
            // We have found a connection, try to close it
            if ($this->myconn->close()) {
                // We have successfully closed the connection, set the connection variable to false
                $this->con = false;
                // Return true tjat we have closed the connection
                return true;
            } else {
                // We could not close the connection, return false
                return false;
            }
        }
    }

    public function rawQuery($sql)
    {
        $result = [];
        $query = $this->myconn->query($sql);
        $this->myQuery = $query; // Pass back the SQL
        if ($query) {
            // If the query returns >= 1 assign the number of rows to numResults
            $this->numResults = $query->num_rows;
            // Loop through the query results by the number of rows returned
            for ($i = 0; $i < $this->numResults; $i++) {
                $r = $query->fetch_array();

                $key = array_keys($r);
                for ($row = 0; $row < count($key); $row++) {
                    // Sanitizes keys so only alphavalues are allowed
                    if (!is_int($key[$row])) {
                        if ($query->num_rows >= 1) {
                            //$this->result[$i][$key[$row]] = $r[$key[$row]];
                            $result[$i][$key[$row]] = $r[$key[$row]];
                        } else {
                            //$this->result[$i][$key[$row]] = null;
                            $result[$i][$key[$row]] = null;
                        }
                    }
                }
            }
            return $result; // Query was successful
        } else {
            array_push($this->result, $this->myconn->error);
            return false; // No rows where returned
        }

    }

    public function sql($sql)
    {
        $result = [];
        $query = $this->myconn->query($sql);
        $this->myQuery = $sql; // Pass back the SQL
        if ($query) {
            // If the query returns >= 1 assign the number of rows to numResults
            $this->numResults = $query->num_rows;
            // Loop through the query results by the number of rows returned
            for ($i = 0; $i < $this->numResults; $i++) {
                $r = $query->fetch_array();
                $key = array_keys($r);
                for ($row = 0; $row < count($key); $row++) {
                    // Sanitizes keys so only alphavalues are allowed
                    if (!is_int($key[$row])) {
                        if ($query->num_rows >= 1) {
                            $this->result[$i][$key[$row]] = $r[$key[$row]];
                            $result[$i][$key[$row]] = $r[$key[$row]];
                        } else {
                            $this->result = null;
                        }
                    }
                }
            }
            return $result; // Query was successful
        } else {
            array_push($this->result, $this->myconn->error);
            return false; // No rows where returned
        }
    }

    public function getPrimaryKey($storeTable)
    {
        $primaryKey = $this->rawQuery("SHOW KEYS FROM `$storeTable` WHERE Key_name = 'PRIMARY'");
        if (isset($primaryKey['Column_name'])) {
            return $primaryKey['Column_name'];
        } else {
            return false;
        }
    }

    public function getById($storeTable, $id = '')
    {
        $primaryKey = $this->getPrimaryKey($storeTable);
        if ($primaryKey) {
            return $this->select($storeTable, '*', null, "`{$primaryKey['Column_name']}`='$id'");
        }
    }

    // Function to SELECT from the database
    public function select($table, $rows = '*', $join = null, $where = null, $order = null, $limit = null)
    {
        // Create query from the variables passed to the function
        $result = [];
        $q = 'SELECT ' . $rows . ' FROM ' . $table;
        if ($join != null) {
            $q .= ' JOIN ' . $join;
        }
        if ($where != null) {
            $q .= ' WHERE ' . $where;
        }
        if ($order != null) {
            $q .= ' ORDER BY ' . $order;
        }
        if ($limit != null) {
            $q .= ' LIMIT ' . $limit;
        }
        // echo $table;
        $this->myQuery = $q; // Pass back the SQL

        // Check to see if the table exists
        if ($this->tableExists($table)) {
            // The table exists, run the query
            $query = $this->myconn->query($q);
            if ($query) {
                // If the query returns >= 1 assign the number of rows to numResults
                $this->numResults = $query->num_rows;
                // Loop through the query results by the number of rows returned
                for ($i = 0; $i < $this->numResults; $i++) {
                    $r = $query->fetch_array();

                    $key = array_keys($r);
                    for ($row = 0; $row < count($key); $row++) {
                        // Sanitizes keys so only alphavalues are allowed
                        if (!is_int($key[$row])) {
                            if ($query->num_rows >= 1) {
                                //$this->result[$i][$key[$row]] = $r[$key[$row]];
                                $result[$i][$key[$row]] = $r[$key[$row]];
                            } else {
                                //$this->result[$i][$key[$row]] = null;
                                $result[$i][$key[$row]] = null;
                            }
                        }
                    }
                }
                return $result; // Query was successful
            } else {
                array_push($this->result, $this->myconn->error);
                return false; // No rows where returned
            }
        } else {
            return false; // Table does not exist
        }
    }

    // Function to insert into the database
    public function insert($table, $params = array())
    {
        // Check to see if the table exists

        if ($this->tableExists($table)) {
            foreach ($params as $key => $value) {
                $params[$key] = mysqli_real_escape_string($this->myconn, $value);
            }
            $sql = 'INSERT INTO `' . $table . '` (`' . implode('`, `',
                    array_keys($params)) . '`) VALUES ("' . implode('", "', $params) . '")';
            $this->myQuery = $sql; // Pass back the SQL
            // Make the query to insert to the database
            if ($ins = $this->myconn->query($sql)) {
                array_push($this->result, $this->myconn->insert_id);
                return $this->myconn->insert_id; // The data has been inserted
            } else {
                array_push($this->result, $this->myconn->error);
                return false; // The data has not been inserted
            }
        } else {
            return false; // Table does not exist
        }
    }

    //Function to delete table or row(s) from database
    public function delete($table, $where = null)
    {
        // Check to see if table exists
        if ($this->tableExists($table)) {
            // The table exists check to see if we are deleting rows or table
            if ($where == null) {
                $delete = 'DROP TABLE ' . $table; // Create query to delete table
            } else {
                $delete = 'DELETE FROM ' . $table . ' WHERE ' . $where; // Create query to delete rows
            }
            // Submit query to database
            if ($del = $this->myconn->query($delete)) {
                array_push($this->result, $this->myconn->affected_rows);
                $this->myQuery = $delete; // Pass back the SQL
                return true; // The query exectued correctly
            } else {
                array_push($this->result, $this->myconn->error);
                return false; // The query did not execute correctly
            }
        } else {
            return false; // The table does not exist
        }
    }

    // Function to update row in database
    public function update($table, $params = array(), $where)
    {
        // Check to see if table exists
        $result = [];
        if ($this->tableExists($table)) {
            // Create Array to hold all the columns to update
            $args = array();
            foreach ($params as $field => $value) {
                // Seperate each column out with it's corresponding value
                $args[] = $field . '="' . mysqli_real_escape_string($this->myconn, $value) . '"';
            }
            // Create the query
            $sql = 'UPDATE ' . $table . ' SET ' . implode(',', $args) . ' WHERE ' . $where;
            // Make query to database
            $this->myQuery = $sql; // Pass back the SQL
            if ($query = $this->myconn->query($sql)) {
                array_push($this->result, $this->myconn->affected_rows);
                $result = $this->myconn->affected_rows;
                return $result; // Update has been successful
            } else {
                array_push($this->result, $this->myconn->error);
                return false; // Update has not been successful
            }
        } else {
            return false; // The table does not exist
        }
    }

    // Private function to check if table exists for use with queries
    private function tableExists($table)
    {
        $sql = 'SHOW TABLES FROM ' . $this->db_name . ' LIKE "' . $table . '"';
        $tablesInDb = $this->myconn->query($sql);
        if ($tablesInDb) {
            if ($tablesInDb->num_rows == 1) {
                return true; // The table exists
            } else {
                array_push($this->result, $table . " does not exist in this database");
                return false; // The table does not exist
            }
        }
    }

    // Public function to return the data to the user
    public function getResult()
    {
        $val = $this->result;
        $this->result = array();
        return $val;
    }

    //Pass the SQL back for debugging
    public function getSql()
    {
        $val = $this->myQuery;
        $this->myQuery = array();
        return $val;
    }

    //Pass the number of rows back
    public function numRows()
    {
        $val = $this->numResults;
        $this->numResults = array();
        return $val;
    }

    // Escape your string
    public function escapeString($data)
    {
        return $this->myconn->real_escape_string($data);
    }
}