<?php
/**
 * Created by PhpStorm.
 * User: SANTO
 * Date: 29/10/2015
 * Time: 11:47 AM
 * Para trabajar con el datasource "default", es responsabilidad del login de la aplicacion crear la varaible de session:
 *      TO CONNECT TO A default DATABASE debe setearse la variable de session
 *      $_SESSION["CONNECTED"]["default"] = new stdClass();
 *
 */
##--REQUIRED TO EXECUTE PARSE PLSQS ---------------------
define("RelativePath", ".."); #-- RELATIVE TO ROOT OF CUURENT FILE

#define("PathToCurrentPage", "/services/"); #-- ABSOLUTE TO ROOT OF CUURENT FILE
define("PathToCurrentPage", "/services/");
define("FileName","do_sql_from_string.php"); #-- CURRENT FILE NAME
include_once(RelativePath . "/Common.php");
include_once(RelativePath . "/Template.php");
include_once(RelativePath . "/Sorter.php");
include_once(RelativePath . "/Navigator.php");
include_once(RelativePath . "/services/cryptojs-aes/cryptojs-aes.php");

## ESTO OBLIGA A TENER UNA CONEXION ACEPTADA A LA BASE DE DATOS "default";
$_SESSION["CONNECTED"]["default"] = new stdClass();


if (!isset($_SERVER["HTTP_REFERER"])) {
    bad_request(1);
}
if (!isset($_SERVER["HTTP_X_REQUESTED_WITH"])) {
    bad_request(2);
}

$REFER = parse_url($_SERVER["HTTP_REFERER"], PHP_URL_HOST);
if (!$REFER === $_SERVER["HTTP_HOST"]) {
    bad_request(3);
}
$XRF = $_SERVER["HTTP_X_REQUESTED_WITH"];
if (!$XRF === "XMLHttpRequest") {
    bad_request(4);
}

# Esto para el maejo de la encriptacion del password
#echo cryptoJsAesDecrypt("PASS PHRASE", $_POST["password"]);


class clsDBdatabase extends DB_Adapter
{
    function clsDBdatabase()
    {
        $this->Initialize();
    }

    function Initialize()
    {
        global $CCConnectionSettings;
        global $sourceName;
        $this->SetProvider($CCConnectionSettings[$sourceName]);
        parent::Initialize();
        $this->DateLeftDelimiter = "'";
        $this->DateRightDelimiter = "'";
        if ($CCConnectionSettings[$sourceName]["Type"] == "Oracle") {
            $this->query("ALTER SESSION SET NLS_DATE_FORMAT = 'YYYY-MM-DD HH24:MI:SS'");
        }
    }

}
//End oracle Connection Class

global $sourceName;
global $pass_phrase;
$sourceName = CCGetParam("sourcename",CCGetSession("sourcename"));
$pass_phrase = "PASS PHRASE";
$level = CCGetParam("level","single");

#$sourceName = "source1";
if (!file_exists("../textdb/" . $sourceName . ".sources.json")) {
    bad_request(5);
}

$datasource = file_get_contents("../textdb/" . $sourceName . ".sources.json");
$datasource = json_decode($datasource, true);
$CCConnectionSettings[$sourceName] = $datasource;

## CHECK IF CONNECTED ###################################
if (CCGetParam("connect") == "true") {
    if (isset($_SESSION["CONNECTED"])   and isset($_SESSION["CONNECTED"][$sourceName])) {
        unset($_SESSION["CONNECTED"][$sourceName]);
    }
    $user = CCGetParam("user");
    $password = cryptoJsAesDecrypt($pass_phrase, $_POST["password"]);
    #echo $password;
    sqlConnect($user, $password);
    die;
} else {
    if ($sourceName == "default") {
        #var_dump($_SESSION);
        if (!isset($_SESSION["CONNECTED"])   or !isset($_SESSION["CONNECTED"][$sourceName])) {
            die('{"ERROR" : {"CODE":"2","MESSAGE":"NOT CONNECTED TO \"DEFAULT\" DATABASE", "TYPE" : "'.$CCConnectionSettings[$sourceName]["Type"].'"}}');
        } else {
            # Si se usa la base de datos default, simepre usa el usuario definido en el datasource
            $_SESSION["CONNECTED"][$sourceName]->{"user"}     = $CCConnectionSettings[$sourceName]["User"];
            $_SESSION["CONNECTED"][$sourceName]->{"password"} = $CCConnectionSettings[$sourceName]["User"];
        }
    } else if (!isset($_SESSION["CONNECTED"])   or !isset($_SESSION["CONNECTED"][$sourceName])) {
        die('{"ERROR" : {"CODE":"12","MESSAGE":"NOT CONNECTED TO DATABASE '.$sourceName.'.", "TYPE" : "'.$CCConnectionSettings[$sourceName]["Type"].'"}}');
    }
}
## #####################################################

$BIND = CCGetParam("BIND");
//$SQL  = CCGetParam("SQL");
$SQL = cryptoJsAesDecrypt($pass_phrase, CCGetParam("SQL"));

$BIND = json_decode($BIND);
#$phpcode = "";
#eval($phpcode);
#var_dump($BIND);
#var_dump($SQL);
#var_dump($phpcode);



sqlParserFromString($SQL, $BIND, $level);

$result = '';
$____error = "";
$____lastkey = "";

eval($plsqlParsed["ANONYMOUS"]->phpCode);
#var_dump($db);
#echo "ERROR ".$____error ;
if ($____error !== "") {
    $____error = str_replace(array("\\", '"', "/", "\n" , "\r", "\t", "\b"), array("\\\\", '\"', '\/', '\\n', '', '\t', '\b'), $____error);
    echo '{"ERROR" : {"CODE":"10","MESSAGE":"'.$____error.'", "TYPE" : "'.$CCConnectionSettings[$sourceName]["Type"].'"}}';
    die;
}

if ($CCConnectionSettings[$sourceName]["Type"] == "Oracle") {
    $phpcode = '{"ERROR" : {"CODE":"0","MESSAGE":"SUCCESS", "LAST_INSERT_ID":"'.$____lastkey.'", "TYPE" : "'.$CCConnectionSettings[$sourceName]["Type"].'"}, "RESULT" : {';
    $n = 0;
    foreach($BIND as $i => $var) {
        eval('$val = $'.$i.';');
        $phpcode .= ($n == 0 ? '' : ', '). '"'.$i.'" : "'.$val.'"';
        $n++;
    }
    $phpcode .= '}}';

} else {
    $phpcode = '{"ERROR" : {"CODE":"0","MESSAGE":"SUCCESS", "LAST_INSERT_ID":"'.$____lastkey.'", "TYPE" : "'.$CCConnectionSettings[$sourceName]["Type"].'"}, "RESULT" : '.$result.'}';
}
## ---------------------------------------------------------------
#$phpcode = str_replace(array("\\", '"', "/", "\n" , "\r", "\t", "\b"), array("\\\\", '\"', '\/', '\\n', '', '\t', '\b'), $phpcode);
echo $phpcode ;

die;

//oracle Connection Class


function sqlParserFromString($currenString = "", $bind, $level="single") {
    global $plsqlParsed;
    global $CCConnectionSettings;
    global $sourceName;
    $DB_TYPE =  $CCConnectionSettings[$sourceName]["Type"];
    if (!$currenString) {
        exit;
    }
    $lump = $currenString;

    $currenString = trim($currenString);
    if (substr($currenString, strlen($currenString)-1, 1) !== ";") {
        $currenString = $currenString.';';
    }

    $body = $currenString;
    $name = "ANONYMOUS";
    $plsqlParsed[$name] = new stdClass();

    preg_match_all("/\\:\s*([a-zA-Z0-9_.]+)/ise", $body, $arr);

    if (isset($arr[1])) {
        $arr = array_unique($arr[1]);
    } else $arr = array();

    #$x = sqlCompile('BEGIN '.$body.' END;');

    #if ($x->SQLCODE !== 0) {
    #    echo $x->SQLERRMSG . "<BR>";
    #}
    #if ($x->SQLCODE === 0) {
    #    #echo "COMPILED<br>";
    #}

    #$plsqlParsed[$name]->scope = $scope;
    $plsqlParsed[$name]->body = $body;
    $plsqlParsed[$name]->bind = $arr;
    #------ CREATE EL CODIGO PARA EVAL --------
    $esto = array(chr(10), chr(9), chr(13));
    $porEsto = array("\n", "\t", " ");
    $body = trim(str_replace($esto, $porEsto, $body));

    $phpCode = '$db = new clsDBdatabase();' . "\n";
    if ($DB_TYPE == "Oracle") {
        foreach($bind as $i => $var) {
            $phpCode .= "$".$i ." = " . CCToSQL($var, ccsText).";\n";
        }
        foreach ($arr as $toBind) {
            $t = trim(str_replace(',', '', $toBind));
            $phpCode .= 'if (!isset($' . $t . ')) $' . $t . '="";' . "\n";
            $phpCode .= '$db->bind(\'' . $t . '\', $' . $t . ', 4000, SQLT_CHR);' . "\n";
            #$phpCode .= '$db->bind(\'' . $t . '\', $' . $t . ', strlen($'.$t.')+1000, SQLT_CHR);' . "\n";
        }

        # USA EL USARIO CONECTADO #################################################
        $user       = $_SESSION["CONNECTED"][$sourceName]->{"user"};
        $password   = $_SESSION["CONNECTED"][$sourceName]->{"password"};
        $phpCode .= '$db->DBUser = "'.$user.'";' . "\n";
        $phpCode .= '$db->DBPassword = "'.$password.'";' . "\n";
        # ##########################################################################

        $phpCode .= '$db->query("BEGIN ' . $body . ' END;");' . "\n";
        $phpCode .= '$____error = $db->Errors->ToString();'. "\n";
        foreach ($arr as $toBind) {
            $t = trim(str_replace(',', '', $toBind));
            $phpCode .= '$' . $t . ' = $db->Record[\'' . $t . '\'] ;' . "\n";
        }
        #echo $phpCode;
        #die;
    } else if ($DB_TYPE == "MySQL"){
        if ($level == "advanced") {

            $unique_name = "_" . (int)uniqid(rand(), true);
            $drop = "DROP procedure IF EXISTS $unique_name";
            $create = "begin
                declare resultado varchar(4000) default ' ';
                #SETS
                #SQL
                set @____resultado = '{';
                #RETURN
                set @____resultado = concat(@____resultado, '}');
                END
            ";
            $create = "create procedure " . $unique_name . "()
                -- returns varchar(4000)
                begin
                #SETS
                begin
                #SQL
                end;
                set @____resultado = '{';
                #RETURN
                set @____resultado = concat(@____resultado, '}');
                set @____resultado = replace(@____resultado, ', }', '}');
                END
            ";
            $SETS = "";
            foreach ($bind as $i => $var) {
                $SETS .= "set @" . $i . " = " . CCToSQL($var, ccsText) . ";\n";
            }
            #foreach ($arr as $toBind) {
            #    $t = trim(str_replace(',', '', $toBind));
            #    $SETS .= " set $".$t." = '';\n";
            #}
            $RETURN = "";
            foreach ($arr as $toBind) {
                $t = trim(str_replace(',', '', $toBind));
                $body = str_replace(':' . $t, '@' . $t, $body);
                $RETURN .= '  set @____resultado = concat(@____resultado, concat(\'\"' . $t . '\" : \"\', concat(coalesce(#var,\'\'), \'\", \')));' . "\n";
                $RETURN = str_replace('#var', '@' . $t, $RETURN);
            }
            $create = str_replace('#SETS', $SETS, $create);
            $create = str_replace('#RETURN', $RETURN, $create);
            $create = str_replace('#SQL', ' ' . $body . ' ', $create);

            $phpCode .= '$db->query("' . $drop . '");' . "\n";
            $phpCode .= '$db->query("' . $create . '");' . "\n";

            # USA EL USARIO CONECTADO #################################################
            $user       = $_SESSION["CONNECTED"][$sourceName]->{"user"};
            $password   = $_SESSION["CONNECTED"][$sourceName]->{"password"};
            $phpCode .= '$db->DBUser = "'.$user.'";' . "\n";
            $phpCode .= '$db->DBPassword = "'.$password.'";' . "\n";
            # ##########################################################################

            #$phpCode .= '$result = CCDLookUp("' . $unique_name . '()", "", "", $db);' . "\n";
            $phpCode .= '$db->query("call '.$unique_name . '()");' . "\n";
            $phpCode .= '$result = CCDLookUp("@____resultado", "", "", $db);' . "\n";
            $phpCode .= '$db->query("' . $drop . '");' . "\n";
            $phpCode .= '$____lastkey = CCDLookUp("LAST_INSERT_ID()", "", "", $db);' . "\n";
            $phpCode .= '$____error = $db->Errors->ToString();' . "\n";
            #echo $phpCode;
        } else { // SINGLE STATEMENT
            foreach($bind as $i => $var) {
                $phpCode .= '$db->query("'."set @".$i ." = " . CCToSQL($var, ccsText).';");' . "\n";
            }

            $RETURN = "";
            $RETURN .= '$db->query("set @____resultado = \'{\'");'."\n";

            foreach ($arr as $toBind) {
                $t = trim(str_replace(',', '', $toBind));
                $body = str_replace(':'.$t, '@'.$t, $body);
                $RETURN .= '$db->query("set @____resultado = concat(@____resultado, concat(\'\"'.$t.'\" : \"\', concat(coalesce(#var,\'\'), \'\", \')))");'."\n";
                $RETURN = str_replace('#var', '@'.$t, $RETURN);
            }
            $RETURN .= '$db->query("set @____resultado = concat(@____resultado, \'}\')");'."\n";
            $RETURN .= '$db->query("set @____resultado = replace(@____resultado, \', }\', \'}\')");'."\n";

            # USA EL USARIO CONECTADO #################################################
            $user       = $_SESSION["CONNECTED"][$sourceName]->{"user"};
            $password   = $_SESSION["CONNECTED"][$sourceName]->{"password"};
            $phpCode .= '$db->DBUser = "'.$user.'";' . "\n";
            $phpCode .= '$db->DBPassword = "'.$password.'";' . "\n";
            # ##########################################################################

            //$phpCode .= '$db->query("set @statement = \''.utf8_encode(addslashes($body)).'\'");' . "\n";
            $phpCode .= '$db->query("'.$body.'");' . "\n";
            //$phpCode .= '$db->query("PREPARE stmt1 FROM @statement;");' . "\n";
            //$phpCode .= '$db->query("EXECUTE stmt1;");' . "\n";
            //$phpCode .= '$db->query("DEALLOCATE PREPARE stmt1;");' . "\n";
            $phpCode .= ' if ($db->Link_ID->affected_rows == 0 and $db->Link_ID->warning_count > 0 and  $db->Link_ID->info === null) {
                 $msg["code"]    = 1329;
                 $msg["message"] = "No data - zero rows fetched, selected, or processed";
                 $db->halt($msg);
            }'."\n";
            #$phpCode .= 'if ($db->Link_ID->affected_rows == 0 and $db->Link_ID->warning_count > 0 ) echo "NO DATA FOUD" ;'. "\n";
            #$phpCode .= 'echo $db->Link_ID->affected_rows ." ". $db->Link_ID->warning_count ;'. "\n";

            $phpCode .= $RETURN;

            #$phpCode .= '$result = CCDLookUp("'.$unique_name.'()", "", "", $db);' . "\n";
            $phpCode .= '$result = CCDLookUp("@____resultado", "", "", $db);' . "\n";
            $phpCode .= '$____lastkey = CCDLookUp("LAST_INSERT_ID()", "", "", $db);' . "\n";
            $phpCode .= '$____error = $db->Errors->ToString();'. "\n";
            #echo $phpCode;
        }

        #echo $create;
        #echo $phpCode;
    }
    $plsqlParsed[$name]->phpCode = $phpCode;

    ## AHORA EL ARRAY SCOPE TIENE TODOS LOS CODIGOS SQL
    ##
}

function sqlConnect($user, $password) {
    global $CCConnectionSettings;
    global $sourceName;
    $DB_TYPE =  $CCConnectionSettings[$sourceName]["Type"];
    $db = new clsDBdatabase();
    $db->close();
    $db->DBUser     = $user;
    $db->DBPassword = $password;
    if ($db->Provider->connect()) {
        $_SESSION["CONNECTED"][$sourceName] = new stdClass();
        $_SESSION["CONNECTED"][$sourceName]->{"user"} = $user;
        $_SESSION["CONNECTED"][$sourceName]->{"password"} = $password;
        die('{"ERROR" : {"CODE":"0","MESSAGE":"CONNECT SUCCESS", "TYPE" : "'.$CCConnectionSettings[$sourceName]["Type"].'"}}');
    } else {
        //die('{"ERROR" : {"CODE":"1","MESSAGE":"NOT CONNECTED"}}');
    };


}
function sqlCompile($Query_String) {

    $db = new clsDBdatabase();

    $db->Provider->sqoe = 0;
    $esto = array(chr(10), chr(9), chr(13));
    $porEsto = array("\n","\t"," ");
    $parse = '
	declare
		c integer := dbms_sql.open_cursor();
	begin
		dbms_sql.parse(c, :STMT, dbms_sql.native);
		dbms_sql.close_cursor(c);
	end;
	';
    $plsql = trim(str_replace($esto, $porEsto, $Query_String)) ;
    #echo $plsql ;
    #$Query_String = 'select a from dual';
    $db->bind('STMT', $plsql, 4000, SQLT_CHR);
    #$db->query('BEGIN '.trim(str_replace($esto, $porEsto, $parse)).' END;');
    $db->Query_ID = OCIParse($db->Link_ID,'BEGIN '.trim(str_replace($esto, $porEsto, $parse)).' END;');
    if(!$db->Query_ID) {
        $db->Error=OCIError($db->Link_ID);
        echo 'ERROR '.OCIError($db->Link_ID);
    }
    if(sizeof($db->Provider->Binds) > 0)
    {
        foreach ($db->Provider->Binds as $parameter_name => $parameter_values) {
            if($parameter_values[2] == OCI_B_CURSOR)
                $this->db[$parameter_name][0] = OCINewCursor($db->Link_ID);

            if($parameter_values[2] == 0)
                OCIBindByName ($db->Query_ID, ":" . $parameter_name, $db->Provider->Binds[$parameter_name][0], $parameter_values[1]);
            else
                OCIBindByName ($db->Query_ID, ":" . $parameter_name, $db->Provider->Binds[$parameter_name][0], $parameter_values[1], $parameter_values[2]);
        }
    }
    @OCIExecute($db->Query_ID);
    $db->Error = OCIError($db->Query_ID);
    #var_dump($db->Error);
    $SQLCODE = $db->Error['code'];
    $SQLERRMSG = explode('ORA-06512', $db->Error['message']);
    $SQLERRMSG = $SQLERRMSG[0];
    $error = new stdClass;
    $error->SQLCODE   = !$SQLCODE ? 0 : $SQLCODE;
    $error->SQLERRMSG = $SQLERRMSG;
    return $error;
}

function bad_request($msg)
{
    if ($msg == '5') {
        $e = '{"ERROR" : {"CODE":"'. "3" . '", "MESSAGE" : "' . "BAD REQUEST $msg" . '", "TYPE" : "'.$CCConnectionSettings[$sourceName]["Type"].'"}}';
    } else {
        $e = '{"ERROR" : {"CODE":"' . "3" . '", "MESSAGE" : "' . "BAD REQUEST $msg" . '", "TYPE" : "'.$CCConnectionSettings[$sourceName]["Type"].'"}}';
    }
    die($e);
}
?>
