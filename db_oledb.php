<?php

//DB OLEDB Class @0-65424FDA
/*
 * Database Management for PHP
 *
 * Copyright (c) 1998-2000 NetUSE AG
 *                    Boris Erdmann, Kristian Koehntopp
 * Derived from db_mssql.php
 *
 * db_oledb.php
 */
class DB_OLEDB {
    public $DBDatabase = "";
    public $DBUser     = "";
    public $DBPassword = "";
    public $Persistent = false;
    public $Uppercase  = false;
    public $Options    = array();
    public $Encoding   = "";

    public $Binds = array();

    public $Link_ID  = 0;
    public $Query_ID = 0;
    public $Record   = array();
    public $Row      = 0;

    public $Errno    = 0;
    public $Error    = "";

    public $Auto_Free = 1;     ## set this to 1 to automatically free results
    public $Debug     = 0;     ## Set to 1 for debugging messages.
    public $Connected = false;

    public $Stored_Query = 0;
    public $Child_Field  = "children";
    public $Parents      = array();

    public $AffectedRows = false;

    /* public: constructor */
    function DB_Sql($query = "") {
        $this->query($query);
    }

    function try_connect() {
        $this->Connected = true;
        $this->Query_ID  = 0;
        try {
        $this->Link_ID = new COM("ADODB.Connection");
        $this->Link_ID->Open($this->DBDatabase);
        } catch(Exception $e) {};
        if (!$this->Link_ID) {
            $this->Connected = false;
        }
        return $this->Connected;
    }

    function connect() {
        if (!$this->Connected) {
            $this->Connected = true;
            $this->Query_ID  = 0;
            try {
            $this->Link_ID = new COM("ADODB.Connection");
            $this->Link_ID->Open($this->DBDatabase);
            } catch(Exception $e) {};
            if (!$this->Link_ID) {
                $this->Halt("Cannot connect to OLEDB Database.");
                $this->Connected = false;
            }
        }
    }

    function free_result() {
        $this->Query_ID = null;
    }

    function query($Query) {
        if (!$this->Link_ID)
            $this->connect();
        if ($this->Debug)
            printf("Debug: query = %s<br>\n", $Query);
        try {
        $this->Query_ID = $this->Link_ID->Execute($Query, $this->AffectedRows);
        } catch(Exception $e) {};
        if ($this->Link_ID->Errors->Count > 0) {
            $Error = $this->Link_ID->Errors->Item($this->Link_ID->Errors->Count-1);
            $this->Errno = $Error->NativeError;
            $this->Error = $Error->Description;
            $this->Errors->addError("Database error: " . $this->Error);
        }
        return $this->Query_ID;
    }

    function next_record() {
    if (!$this->Query_ID)
        return 0;
        if (!$this->Query_ID->EOF) {
            $num_columns = $this->Query_ID->Fields->Count();
            for ($i = 0; $i < $num_columns; $i++) {
                $this->Record[$i] = $this->Query_ID->Fields($i)->Value;
                $this->Record[$this->Query_ID->Fields($i)->Name] = $this->Query_ID->Fields($i)->Value;
            }
            $this->Query_ID->MoveNext();
            return $this->Record;
        }
        return false;
    }

    function seek($pos) {
        @$this->Query_ID->Move($pos);
        return true;
    }

    function affected_rows() {
        return $this->AffectedRows;
    }

    function num_rows() {
        return $this->Query_ID->RecordCount;
    }

    function num_fields() {
        return $this->Query_ID->Fields->Count();
    }

    function nf() {
        return $this->num_rows();
    }

    function np() {
        print $this->num_rows();
    }

    function f($Name) {
        if($this->Uppercase) $Name = strtoupper($Name);
        return $this->Record && array_key_exists($Name, $this->Record) ? $this->Record[$Name] : "";
    }

    function p($Field_Name) {
        print $this->f($Field_Name);
    }

    function close() {
        if ($this->Query_ID) {
            $this->free_result();
        }
        if ($this->Connected && !$this->Persistent) {
            $this->Link_ID = null;
            $this->Connected = false;
        }
    }

    function halt($msg) {
        printf("</td></tr></table><b>Database error:</b> %s<br>\n", $msg);
        printf("<b>OLEDB Error</b><br>\n");
        die("Session halted.");
    }
}
//End DB OLEDB Class


?>
