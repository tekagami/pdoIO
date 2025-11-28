<?php

/*
    Author: Rodrigo Díaz
    https://unlimitedstudios.com
*/

class PDOIO {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function select($query) {
        try {
            $sql = "SELECT ";

            // Handle SELECT clause
            if (isset($query['SELECT'])) {
                $sql .= implode(", ", $query['SELECT']);
            } else {
                $sql .= "*";
            }

            // Handle FROM clause
            if (isset($query['FROM'])) {
                $sql .= " FROM " . $query['FROM'];
            } else {
                throw new Exception("FROM clause is required.");
            }

            // Handle WHERE clause
            if (isset($query['WHERE'])) {
                $sql .= " WHERE ";
                $whereClauses = [];
                foreach ($query['WHERE'] as $condition) {
                    if (isset($condition['word'])) {
                        $whereClauses[] = strtoupper($condition['word']);
                    } else {
                        $whereClauses[] = "{$condition['col']} {$condition['op']} :{$condition['col']}";
                    }
                }
                $sql .= implode(" ", $whereClauses);
            }

            // Handle ORDER BY clause
            if (isset($query['ORDER BY'])) {
                $sql .= " ORDER BY {$query['ORDER BY']['col']} {$query['ORDER BY']['dir']}";
            }

            // Handle LIMIT clause
            if (isset($query['LIMIT'])) {
                $sql .= " LIMIT {$query['LIMIT']}";
            }

            // Handle OFFSET clause
            if (isset($query['OFFSET'])) {
                $sql .= " OFFSET {$query['OFFSET']}";
            }

            $stmt = $this->pdo->prepare($sql);

            // Bind parameters for WHERE clause
            if (isset($query['WHERE'])) {
                foreach ($query['WHERE'] as $condition) {
                    if (!isset($condition['word'])) {
                        $stmt->bindValue(":{$condition['col']}", $condition['val']);
                    }
                }
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Database error in select: " . $e->getMessage());
        }
    }

    // handle get row by id
    public function getById($table, $id) {
        try {
            $sql = "SELECT * FROM {$table} WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(":id", $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Database error in getById: " . $e->getMessage());
        }
    }

    public function getTotal($query){
        try {
            $sql = "SELECT COUNT(*) as total";
            // Handle FROM clause
            if (isset($query['FROM'])) {
                $sql .= " FROM " . $query['FROM'];
            } else {
                throw new Exception("FROM clause is required.");
            }

            // Handle WHERE clause
            if (isset($query['WHERE'])) {
                $sql .= " WHERE ";
                $whereClauses = [];
                foreach ($query['WHERE'] as $condition) {
                    if (isset($condition['word'])) {
                        $whereClauses[] = strtoupper($condition['word']);
                    } else {
                        $whereClauses[] = "{$condition['col']} {$condition['op']} :{$condition['col']}";
                    }
                }
                $sql .= implode(" ", $whereClauses);
            }
            
            $stmt = $this->pdo->prepare($sql);
            
            // Bind parameters for WHERE clause
            if (isset($query['WHERE'])) {
                foreach ($query['WHERE'] as $condition) {
                    if (!isset($condition['word'])) {
                        $stmt->bindValue(":{$condition['col']}", $condition['val']);
                    }
                }
            }
            
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'];

        } catch (PDOException $e) {
            throw new Exception("Database error in getTotal: " . $e->getMessage());
        }
    }

    public function getnumPages($query, $maxperpage = 10){
        $total = $this->getTotal($query);
        $numPages = ceil($total / $maxperpage);

        return $numPages;
    }

    public function getPages($query, $maxperpage = 10, $maxbuttons = 10, $currentpage = null){
        $numPages = $this->getnumPages($query, $maxperpage);
        $pages = [];
        for ($i = 0; $i < $numPages; $i++) {
            $pages[] = $i + 1;
        }
        $count = count($pages);
        echo "<p>$maxbuttons $count</p>\n";

        if ($maxbuttons && count($pages) > $maxbuttons && $currentpage) {
            $lastPage = $pages[$count - 1];
            $lastPage1 = $lastPage - 1;
            $start = max(0, $currentpage - floor($maxbuttons / 2) - 1);
            $end = min($start + $maxbuttons - 1, $lastPage - 1);
            $pages = array_slice($pages, $start, $end - $start + 1);
            if ($end < $lastPage - 1) {
                $pages[] = $lastPage1;
                $pages[] = $lastPage;
            }
        }

        if ($pages[0] != 1) {
            array_unshift($pages, 1);
        }


        return $pages;
    }

    // Handle INSERT
    public function insert($insertQuery) {
        try {
            $table = $insertQuery['TABLE'];
            $data = $insertQuery['DATA'];  // contains associative array of column => value

            $columns = [];
            $placeholders = [];
            foreach ($data as $key => $value) {
                $columns[] = $key;
                $placeholders[] = ":{$key}";
            }

            $columns = implode(", ", $columns);
            $placeholders = implode(", ", $placeholders);
            $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
            $stmt = $this->pdo->prepare($sql);
            foreach ($data as $key => $value) {
                $stmt->bindValue(":{$key}", $value);
            }

            $stmt->execute();
            $lastInsertedId = $this->pdo->lastInsertId();
            return $lastInsertedId;
        } catch (PDOException $e) {
            throw new Exception("Database error in insert: " . $e->getMessage());
        }
    }

    // Handle UPDATE
    public function update($updateQuery) {
        try {
            $table = $updateQuery['TABLE'];
            $data = $updateQuery['DATA'];  // contains associative array of column => value
            $where = $updateQuery['WHERE'];  // contains associative array of column => value for WHERE clause

            // what we are updating
            $columns = [];
            $placeholders = [];
            foreach ($data as $key => $value) {
                $columns[] = $key;
                $placeholders[] = ":{$key}";
            }

            // where clause
            $whereClauses = [];
            foreach ($where as $key => $value) {
                $whereClauses[] = "{$key} = :where_{$key}";
            }

            $columns = implode(", ", $columns);
            $placeholders = implode(", ", $placeholders);
            $whereSql = implode(" AND ", $whereClauses);
            $sql = "UPDATE {$table} SET ";
            $setParts = [];
            foreach ($data as $key => $value) {
                $setParts[] = "{$key} = :{$key}";
            }

            $sql .= implode(", ", $setParts);
            $sql .= " WHERE {$whereSql}";

            $stmt = $this->pdo->prepare($sql);

            foreach ($data as $key => $value) {
                $stmt->bindValue(":{$key}", $value);
            }

            foreach ($where as $key => $value) {
                $stmt->bindValue(":where_{$key}", $value);
            }

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Database error in update: " . $e->getMessage());
        }
    }

    // Handle DELETE
    public function delete($deleteQuery) {
        $table = $deleteQuery['TABLE'];
        $where = $deleteQuery['WHERE'];  // contains associative array of column => value for WHERE clause
        $limit = isset($deleteQuery['LIMIT']) ? $deleteQuery['LIMIT'] : 1;  // default limit to 1, only on mySQL NOT on SQLite

        // where clause
        $whereClauses = [];
        foreach ($where as $key => $value) {
            $whereClauses[] = "{$key} = :{$key}";
        }

        $whereSql = implode(" AND ", $whereClauses);

        $dbtype = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($dbtype !== 'mysql' && $limit) {
            $sql = "DELETE FROM {$table} WHERE {$whereSql}";
        } elseif ($dbtype === 'mysql' && $limit) {
            $sql = "DELETE FROM {$table} WHERE {$whereSql} LIMIT {$limit}";
        } else {
            $sql = "DELETE FROM {$table} WHERE {$whereSql}";
        }

        try {
            $stmt = $this->pdo->prepare($sql);

            foreach ($where as $key => $value) {
                $stmt->bindValue(":{$key}", $value);
            }

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Database error in delete: " . $e->getMessage());
        }
    }

    // Handle deleteby id
    public function deleteById($table, $id) {
        try {
            $sql = "DELETE FROM {$table} WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(":id", $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Database error in deleteById: " . $e->getMessage());
        }
    }


}