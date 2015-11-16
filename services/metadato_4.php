<?php  

//Include Common Files @1-64426E2F
define("RelativePath", "..");
define("PathToCurrentPage", "/services/");
define("FileName", "metadato_4.php");
include_once(RelativePath . "/Common.php");
include_once(RelativePath . "/Template.php");
include_once(RelativePath . "/Sorter.php");
include_once(RelativePath . "/Navigator.php");
#include_once("../Common.php");
#include_once("../Template.php");
#include_once("../Sorter.php");
#include_once("../Navigator.php");
//End Include Common Files

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

// Aqui falta que las clase de acceso a la base de datos estan hardcoded, asi que debemos generalizarlas
// Se necesita un archivo de conf, para cada acceso. Un acceso debe constituir en
// 1) datasource = conexion a la base de datos de la forma
/*
"datasource" => array(
    "Type" => "MySQL",
    "DBLib" => "MySQLi",
    "Database" => "uicipe_proyecto",
    "Host" => "localhost",
    "Port" => "3306",
    "User" => "system",
    "Password" => "manager",
    "Encoding" => array("", "utf8"),
    "Persistent" => false,
    "DateFormat" => array("yyyy", "-", "mm", "-", "dd", " ", "HH", ":", "nn", ":", "ss"),
    "BooleanFormat" => array(1, 0, ""),
    "Uppercase" => false
*/

// por ahora esta hardcoded

//Parametro $sourceName = Indica un nombre de definicion de conexion a una base de datos
//Parametro $jsonStyle = Indica el estilo de como generar el JSON
//          1=[ {....}
//              ,{....}
//            ]
//          Cada posicion en la tabla es una estructura
//          { nombre:"valor", nombre : "valor"}
//          El estilo se define el el template
//
//Parametro $sourceSQL = instruccion SQL que debe ejecutarse;
//
//Parametro $defName = Nombre de una definicion que contiene la informacion de la fuente de datos tales como la base de datos, el sql etc.
//
//Parametro $dest == indica un uso especializado, si no se indica nada, es para uso general como tal como datatable. Por defecto recordarray;
//
//
// EJEMPLO USO VIA POST POR FORMS-JS
// ../services/metadata_4.php?sourcename=source1
// el SQL via post.
//
$dest       = CCGetParam("dest", "recordarray");
#$action     = "structuresonly";
$action     = CCGetParam("action", "dataonly");
$defName    = CCGetParam("defname", "");
$SQL        = CCGetParam("SQL", "");
$sourceName = CCGetParam("sourcename", "");

if (!isset($_SESSION["CONNECTED"])   or !isset($_SESSION["CONNECTED"][$sourceName])) {
    die('{"ERROR" : {"CODE":"2","MESSAGE":"NOT CONNECTED TO DATABASE '.$sourceName.'."}}');
}

#echo $sourceName." ".$SQL." ";
if ($dest=="pivot") {
    $pivotName  = $defName;
    $pivotData  = file_get_contents(RelativePath . "/textdb/".$pivotName.".pivot.json.php");

    $pivotData  = json_validate($pivotData);

    $sourceName = $pivotData->datasource;
    $sourceSQL  = $pivotData->sql;
    $jsonStyle  = 1;

    if ($action=="structuresonly") {
        // envia la estructura y termina
        $value_arr = array();
        $replace_keys = array();
        // Permitir que las funciones sean enviadas como tal.
        $sec = 0;
        changeFunctions($pivotData->structures, $sec, $value_arr, $replace_keys);
        //
        $json = json_encode($pivotData->structures);
        $json = str_replace($replace_keys, $value_arr, $json);
        echo $json;
        exit;

    }

    // GET DATASOURCE PARAMETERS
    #$datasource = file_get_contents("../textdb/" . $sourceName . ".sources.json.php");
    #$datasource = json_decode($datasource, true);
    #$CCConnectionSettings[$sourceName] = $datasource;
} else {

    if (!$defName and !$SQL) {
        sendError(0);
    }

    if ($defName) {
        $pivotName  = $defName;
        $pivotData  = file_get_contents(RelativePath . "/textdb/".$pivotName.".pivot.json");

        $pivotData  = json_validate($pivotData);

        $sourceName = $pivotData->datasource;
        $sourceSQL  = $pivotData->sql;

    } else if ($SQL) {
        if (!$sourceName) {
            sendError(1);
        }

        $sourceSQL  = $SQL;

    }
    //$sourceSQL = "select * from estad.result_parl_2010 where cod_estado = 7 and circuito = 5";

    $jsonStyle = 0;

    // resuelve el datasource
    #$datasource = file_get_contents("../textdb/" . $sourceName . ".sources.json");
    #$datasource = json_decode($datasource, true);
    #$CCConnectionSettings[$sourceName] = $datasource;

    #var_dump($CCConnectionSettings);
    #die;
}


// GET DATASOURCE PARAMETERS
$datasource = file_get_contents("../textdb/" . $sourceName . ".sources.json");
$datasource = json_decode($datasource, true);
$CCConnectionSettings[$sourceName] = $datasource;

// ////////////////////////////////////////////////////////////////////
// Crea la clase dinamicamente a traves del EVAL....
// ////////////////////////////////////////////////////////////////////
eval(
'class clsDB'. $sourceName .' extends DB_Adapter
{
    function clsDBuicipe()
    {
        $this->Initialize();
    }

    function Initialize()
    {
        global $CCConnectionSettings;
        $this->SetProvider($CCConnectionSettings["' . $sourceName . '"]);
        parent::Initialize();
        $this->DateLeftDelimiter  = "\'";
        $this->DateRightDelimiter = "\'";
    }

    function OptimizeSQL($SQL)
    {
		#echo "Optimiza ".$this->Type."<br>";
        $PageSize = (int) $this->PageSize;
        if (!$PageSize) return $SQL;
        $Page = $this->AbsolutePage ? (int) $this->AbsolutePage : 1;

		## En caso de Oracle

		if ($this->Type == "Oracle") {
			$SQL = "SELECT a.*, rownum a_count_rows FROM (".$SQL.") a where rownum <= ".(($Page) * $PageSize);
			$SQL = "SELECT * from (".$SQL.") where a_count_rows > ".(($Page - 1) * $PageSize)."";

			#echo $SQL."<br>";
		} else if ($this->Type == "MySql") {
			if (strcmp($this->RecordsCount, "CCS not counted"))
				$SQL .= (" LIMIT " . (($Page - 1) * $PageSize) . "," . $PageSize);
			else
				$SQL .= (" LIMIT " . (($Page - 1) * $PageSize) . "," . ($PageSize + 1));
		}
		return $SQL;
    }

}' );

// ///////////////////////////////////////////////////////////////////////////////////////
// Como la siguiente clase, extiende la recien creada...la hacemos dinamica tambien
// //////////////////////////////////////////////////////////////////////////////////////
eval('
class clsResultDataSource extends clsDB'.$sourceName.' {

//DataSource Variables 
    public $Parent = "";
    public $CCSEvents = "";
    public $CCSEventResult;
    public $ErrorBlock;
    public $CmdExecution;

    public $CountSQL;
    public $wp;
    public $Query;


    // Datasource fields

//End DataSource Variables

//DataSourceClass_Initialize Event 
    function clsResultDataSource(& $Parent)
    {
        $this->Parent = & $Parent;
        $this->ErrorBlock = "Grid Result";
        $this->Initialize();    

		## Elimina el order by, si lo tiene
		$re = "/ORDER BY.*?(?=\\s*LIMIT|\\)|$)/mi";
		$sql = preg_replace($re, "", $Parent->Query);
		//echo "<br>El select sin el Order by ".$sql;

		#$this->query("select * from (". $sql .") x1q1 limit 1");
		$this->query("select * from (". $sql .") x1q1 ");
		if ($this->Errors->ToString()) {
			die("Error ... ".$this->Errors->ToString());
		}
		$Parent->Metadata = metadata($this);
		//$this->close();

		$this->Query = $sql;

        foreach($Parent->Metadata->colsbyname as $col => $prop) {
        	$this->{$col} = new clsField($col, $prop->type, ($prop->type == ccsDate ? $this->DateFormat : ""));
        }
    }
//End DataSourceClass_Initialize Event

//SetOrder Method 
    function SetOrder($SorterName, $SorterDirection)
    {
        $this->Order = "";
        $this->Order = CCGetOrder($this->Order, $SorterName, $SorterDirection, "");
    }
//End SetOrder Method

//Prepare Method 
    function Prepare()
    {
        global $CCSLocales;
        global $DefaultDateFormat;
    }
//End Prepare Method

//Open Method
    function Open()
    {
        $this->CCSEventResult = CCGetEvent($this->CCSEvents, "BeforeBuildSelect", $this->Parent);
        $this->SQL = $this->Parent->Query ." {SQL_Where} {SQL_OrderBy}";
        $this->CountSQL = "SELECT COUNT(*) from (\n\n" . $this->Parent->Query .") aszalst";
        $this->CCSEventResult = CCGetEvent($this->CCSEvents, "BeforeExecuteSelect", $this->Parent);
        if ($this->CountSQL) 
            $this->RecordsCount = CCGetDBValue(CCBuildSQL($this->CountSQL, $this->Where, ""), $this);
        else
            $this->RecordsCount = "CCS not counted";
        $this->query($this->OptimizeSQL(CCBuildSQL($this->SQL, $this->Where, $this->Order)));
        $this->CCSEventResult = CCGetEvent($this->CCSEvents, "AfterExecuteSelect", $this->Parent);
    }
//End Open Method

//SetValues Method 
    function SetValues()
    {
        foreach($this->Parent->Metadata->colsbyname as $col => $prop) {
        	$this->{$col}->SetDBValue(trim($this->f($col)));     
        	#echo "<br> $col = ".$this->{$col}->GetDBValue();
        }
   }
//End SetValues Method
} '
);


// BLOQUE PRINCIPAL /////////////////////////////////////////////
//Initialize Page 
// Variables
$FileName = "";
$Redirect = "";
$Tpl = "";
$TemplateFileName = "";
$BlockToParse = "";
$ComponentName = "";
$Attributes = "";

// Events;
$CCSEvents = "";
$CCSEventResult = "";
$TemplateSource = "";

$FileName = FileName;
$Redirect = "";
//$TemplateFileName = "result.html";
$BlockToParse = "main";
$TemplateEncoding = "UTF-8";
$ContentType = "text/html";
$PathToRoot = "../";
$PathToRootOpt = "../";
#$Scripts = "|";
//End Initialize Page

//Before Initialize 
$CCSEventResult = CCGetEvent($CCSEvents, "BeforeInitialize", $MainPage);
//End Before Initialize

//Initialize Objects 
eval ('$DBmyDB = new clsDB'.$sourceName.'();');
$MainPage->Connections[$sourceName] = & $DBmyDB;
$Attributes = new clsAttributes("page:");
$Attributes->SetValue("pathToRoot", $PathToRoot);
$MainPage->Attributes = & $Attributes;

// Controls                
##### AQUI ENVIAR SELECT COMO PARAMETRO $sql
#echo $sourceSQL ; die;
$Result = new clsGridResult("", $MainPage, $sourceSQL );

$MainPage->Result = & $Result;
$Result->Initialize();
### NO HAY MANEJO DE JAVASCRIPT
# $ScriptIncludes = "";
# $SList = explode("|", $Scripts);
# foreach ($SList as $Script) {
#     if ($Script != "") $ScriptIncludes = $ScriptIncludes . "<script src=\"" . $PathToRoot . $Script . "\" type=\"text/javascript\"></script>\n";
# }
# $Attributes->SetValue("scriptIncludes", $ScriptIncludes);
#######################################
BindEvents();

$CCSEventResult = CCGetEvent($CCSEvents, "AfterInitialize", $MainPage);
$FileEncoding = "";
$Charset = $Charset ? $Charset : "utf-8";
if ($Charset) {
    header("Content-Type: " . $ContentType . "; charset=" . $Charset);
} else {
    header("Content-Type: " . $ContentType);
}
//End Initialize Objects

//Initialize HTML Template 
$CCSEventResult = CCGetEvent($CCSEvents, "OnInitializeView", $MainPage);
$Tpl = new clsTemplate($FileEncoding, $TemplateEncoding);

if ($jsonStyle == 1) {

    ######## CONSTRUYE EL TemplateSource con estilo 1
    $TemplateSource = "<!-- BEGIN Grid Result -->[ \n <!-- BEGIN Row -->{\n";
    $i = 0;
    foreach ($Result->Metadata->colsbyname as $col => $prop) {
        $TemplateSource .= ($i > 0 ? "," : "") . '"' . $col . '":' . '"{' . $col . '}"';
        $i++;
    }
    $TemplateSource .= "\n}<!-- END Row --> \n <!-- BEGIN Separator -->, <!-- END Separator --> \n ] <!-- END Grid Result -->";
    #################################################################
} else {

    ######## CONSTRUYE EL TemplateSource
    $TemplateSource = "<!-- BEGIN Grid Result -->[ \n <!-- BEGIN Row -->[\n";
    $i = 0;
    foreach ($Result->Metadata->colsbyname as $col => $prop) {
        $TemplateSource .= ($i > 0 ? "," : "") . '"{' . $col . '}"';
        $i++;
    }
    $TemplateSource .= "\n]<!-- END Row --> \n <!-- BEGIN Separator -->, <!-- END Separator --> \n ] <!-- END Grid Result -->";
    #################################################################
}
#echo $TemplateSource;
 
if (strlen($TemplateSource)) {
    $Tpl->LoadTemplateFromStr($TemplateSource, $BlockToParse, "UTF-8", "replace");
} else {
    $Tpl->LoadTemplate(PathToCurrentPage . $TemplateFileName, $BlockToParse, "UTF-8", "replace");
}

$Tpl->SetVar("CCS_PathToRoot", $PathToRoot);
$Tpl->block_path = "/$BlockToParse";  

$CCSEventResult = CCGetEvent($CCSEvents, "BeforeShow", $MainPage);
$Attributes->SetValue("pathToRoot", "../");   

$Attributes->Show();
//End Initialize HTML Template

//Go to destination page
if($Redirect)
{
    $CCSEventResult = CCGetEvent($CCSEvents, "BeforeUnload", $MainPage);
    //$DBmyDB->close();
    header("Location: " . $Redirect);
    unset($Result);
    unset($Tpl);
    exit;
}
//End Go to destination page

//Show Page @1-89183586
$Result->Show();
$Tpl->block_path = "";
$Tpl->Parse($BlockToParse, false);

if (!isset($main_block)) $main_block = $Tpl->GetVar($BlockToParse);
$main_block = CCConvertEncoding($main_block, $FileEncoding, $CCSLocales->GetFormatInfo("Encoding"));
$CCSEventResult = CCGetEvent($CCSEvents, "BeforeOutput", $MainPage);

if ($CCSEventResult) {
    if (isset($pivotData->structures) and $action != "dataonly") {
        $value_arr = array();
        $replace_keys = array();
        // Permitir que las funciones sean enviadas como tal.
        $sec = 0;
        //changeFunctions($pivotData->structures, $sec, $value_arr, $replace_keys);
        //
        $json = json_encode($pivotData->structures);
        $json = str_replace($replace_keys, $value_arr, $json);
        $json = "{\"structures\":".$json;
        $json .= ",\"data\":".json_encode($main_block)."}";
        echo $json;
    } else {
        #$header = new stdClass();
        #$header->{"HEADER"} = $Result->Metadata->colsbyname;
        #$header = json_encode($header);
        $header = $Result->Metadata->colsbyname;
        $json = json_encode($header);
        #$json = str_replace($replace_keys, $value_arr, $json);
        $json = "{\"HEADER\":".$json;
        $json .= ',"ERROR" : {"CODE":"0", "MESSAGE" : "SUCCESS"}';
        $json .= ",\"DATA\":".$main_block."}";
        echo $json;
        #echo $main_block;
    }
}
//End Show Page

//Unload Page 
$CCSEventResult = CCGetEvent($CCSEvents, "BeforeUnload", $MainPage);
//$DBmyDB->close();
unset($Result);
unset($Tpl);
//End Unload Page 
exit;



function changeFunctions(&$in_obj, &$sec, &$value_arr, &$replace_keys) {
    foreach($in_obj as $key => &$value){
        // Look for values starting with 'function('
        if (is_object($value) or is_array($value)) changeFunctions($value, $sec, $value_arr, $replace_keys );
        else {
            //echo $key . '=' . $value . '<br>';
            if (strpos($value, 'function(') === 0) {
                // Store function string.
                $value_arr[] = $value;
                // Replace function string in $foo with a 'unique' special key.
                $value = '%' . $key . '-' . $sec++ . '%';
                // Later on, we'll look for the value, and replace it.
                $replace_keys[] = '"' . $value . '"';
            }
        }
    }

}

function MetaStandardType($DBtype, $DATAtype, $DATAscale = 0) {
	#echo "-- DATA TYPE=".$DATAtype."----- ESCALA = ".$DATAscale;
	switch ($DBtype) {
		case "Oracle" : switch($DATAtype) {  
			//Internal Oracle Datatype 	Maximum Internal Length 	Datatype Code
			//
			//I INTERVAL YEAR TO MONTH	5 bytes	182
			//I INTERVAL DAY TO SECOND	11 bytes	183
			
			//T VARCHAR2, NVARCHAR2	4000 bytes	1
			//T LONG	2^31-1 bytes (2 gigabytes)	8
			//T ROWID	10 bytes	11 
			//T CHAR, NCHAR	2000 bytes	96
			//T CLOB, NCLOB	4 gigabytes	112			
			//T TIMESTAMP	11 bytes	180
			//T TIMESTAMP WITH TIME ZONE	13 bytes	181
			//T TIMESTAMP WITH LOCAL TIME ZONE	11 bytes	231			
			
			//F NUMBER	21 bytes	2
			//D DATE	7 bytes	12
			//* RAW	2000 bytes	23
			//* LONG RAW	2^31-1 bytes	24
			//* User-defined type (object type, VARRAY, Nested Table)	N/A	108
			//* REF	N/A	111
			//* BLOB	4 gigabytes	113	
			//* BFILE	4 gigabytes	114
			//* UROWID	3950 bytes	208
			case "2":  
				#echo "------- ESCALA = ".$DATAscale;
				if ($DATAscale > 0) return ccsFloat; else return ccsInteger;
				break;
			case "182": 
			case "183": 
				return ccsInteger;
				break;
			case "1": 
			case "8": 
			case "11": 
			case "96": 
			case "112": 
			case "180": 
			case "181": 
			case "231": 
				return ccsText;
				break;
			case "12": 
				return ccsDate;
				break;
			default : return null; break;
		}
		case "MySQL" : switch($DATAtype) {  
			//For version 4.3.4, types returned are:
			//
			//T STRING, VAR_STRING: string
			//I TINY, SHORT, LONG, LONGLONG, INT24: int
			//F FLOAT, DOUBLE, DECIMAL: real
			//D TIMESTAMP: timestamp
			//I YEAR: year
			//D DATE: date
			//D TIME: time
			//D DATETIME: datetime
			//T TINY_BLOB, MEDIUM_BLOB, LONG_BLOB, BLOB: blob
			//* NULL: null
			//Any other: unknown			
			case "string": 
				return ccsText;
				break;
			case "timestamp": 
			case "year": 
			case "int": 
			case "time": 
				return ccsInteger;
				break;
			case "real": 
				return ccsFloat;
				break;
			case "date": 
				return ccsDate;
				break;
			//case "blob": 
			//	return ccsText;
			//	break;
			default: return null; break;
		}
		case "MySQLi" : switch($DATAtype) {  
			//Codigos de tipos de datos devueltos por fetch_fields()
			//	
			//	Nombre        Codigo
			//	B boolean_    1
			//	I tinyint_    1
			//	I bigint_        8
			//	I serial        8
			//	I mediumint_    9
			//	I smallint_    2
			//	I int_        3
			//	I time_        11
			//	I year_        13
			//	F float_        4
			//	F double_        5
			//	F real_        5
			//	F decimal_    246
			//	D timestamp_    7
			//	D date_        10
			//	D datetime_    12
			//	* bit_        16
			//	T text_        252
			//	T tinytext_    252
			//	T mediumtext_    252
			//	T longtext_    252
			//	T tinyblob_    252
			//	T mediumblob_    252
			//	T blob_        252
			//	T longblob_    252
			//	T varchar_    253
			//	T varbinary_    253
			//	T char_        254
			//	T binary_        254			
			case "1" : 
			case "2" : 
			case "3" : 
			case "8" : 
			case "9" : 
			case "11" : 
			case "13" : 
				return ccsInteger;
				break;
			case "4" : 
			case "5" : 
			case "6" : 
			case "246" : 
				return ccsFloat;
				break;
			case "7" : 
			case "10" : 
			case "12" : 
				return ccsDate;
				break;
			case "252" : 
			case "253" : 
			case "254" : 
				return ccsText;
				break;
			default: return null; break;
		}
	}
	return null;	
}

function mysqlMetadata(& $db) {   
	$id 	= $db->Query_ID;
	$META = new stdClass();

	$count = @mysql_num_fields($id);
	#echo "SQL=".$db->LastSQL."<br>";
	#echo "Columnas =".$count."<br>";

	$META->cols = array();
	
	for($ix=0;$ix<$count;$ix++) {
		$col 			= @mysql_field_name  ($id, $ix); 
		$type 			= @mysql_field_type  ($id, $ix);
		$standarType 	= MetaStandardType("MySQL",$type);
		
		$META->colsbyname[ "$col" ] = new stdClass();
	    $META->colsbyname[ "$col" ]->{"type"}  	= $standarType;
	    $META->colsbyname[ "$col" ]->{"type_raw"} = $type;
	    $META->colsbyname[ "$col" ]->{"size"}   	= @mysql_field_len   ($id, $ix);
		$META->colsbyname[ "$col" ]->{"precision"}= 0;
		$META->colsbyname[ "$col" ]->{"scale"}  	= 0;
		$META->colsbyname[ "$col" ]->{"is_null"}  = 1;
	    $META->colsbyname[ "$col" ]->{"flags"} 	= @mysql_field_flags ($id, $i);    
	    
		$META->cols[  $ix  ] = new stdClass();
	  	$META->cols[  $ix  ]->{"type"}  	= $standarType;
	    $META->cols[  $ix  ]->{"type_raw"} = $type;
	    $META->cols[  $ix  ]->{"size"}   	= @mysql_field_len   ($id, $ix);
		$META->cols[  $ix  ]->{"precision"}= 0;
		$META->cols[  $ix  ]->{"scale"}  	= 0;
		$META->cols[  $ix  ]->{"is_null"}  = 1;
	    $META->cols[  $ix  ]->{"flags"} 	= @mysql_field_flags ($id, $i);

		#echo"<b>[$col]</b>:"
		#.'CCS TYPE='.$META->colsbyname["$col"]->type
		#.' '.$META->colsbyname["$col"]->size
		#.' '.$META->colsbyname["$col"]->precision
		#.' '.$META->colsbyname["$col"]->scale
		#.' '.$META->colsbyname["$col"]->is_null
		#.' type='.$META->colsbyname["$col"]->type_raw
		#.' '."<br>\n";   
  	}

	return $META;

}  

function mysqliMetadata(& $db) {   
	$id 	= $db->Query_ID;
	$META = new stdClass();

	$count = @mysqli_field_count($db->Link_ID);
	#echo "SQL=".$db->LastSQL."<br>";
	#echo "Columnas =".$count."<br>";
	//$result = mysqli_query($id, 'SELECT * FROM myTable');
	$i = 0;
	$META->cols = array();
	while ($property = mysqli_fetch_field($id)) {
		$col 			= strtolower($property->name);  
		$type 			= $property->type;
		$standarType 	= MetaStandardType("MySQLi",$type);
		
		$META->colsbyname[ "$col" ] = new stdClass();
	    $META->colsbyname[ "$col" ]->{"type"}  	= $standarType ;
	    $META->colsbyname[ "$col" ]->{"type_raw"} = $type;
	    $META->colsbyname[ "$col" ]->{"size"}   	= $property->length;
		$META->colsbyname[ "$col" ]->{"precision"}= $property->decimals;
		$META->colsbyname[ "$col" ]->{"scale"}	= $property->decimals;
		$META->colsbyname[ "$col" ]->{"is_null"}  = MYSQLI_NOT_NULL_FLAG & $property->flags ;// decbin($property->flags ); //1;

		$META->cols[ $i ] = new stdClass();
	    $META->cols[ $i ]->{"type"}  	= $standarType;
	    $META->cols[ $i ]->{"type_raw"} = $type;
	    $META->cols[ $i ]->{"size"}   	= $property->length;
		$META->cols[ $i ]->{"precision"}= $property->decimals;
		$META->cols[ $i ]->{"scale"}	= $property->decimals;
		$META->cols[ $i ]->{"is_null"}  = MYSQLI_NOT_NULL_FLAG & $property->flags ;// decbin($property->flags ); //1;
		$i++;
		//FLAG (Posiciones binarias) :
		// NOT_NULL 
		// PRI_KEY  
		// UNIQUE_KEY
		// MULTIPLE_KEY
		// UNSIGNED
		// ENUM
		// AUTO_INCREMENT
		// GROUP
		// UNIQUE		
		
		// MYSQLI_NOT_NULL_FLAG & 49967
		// MYSQLI_PRI_KEY_FLAG & 49967
		// MYSQLI_UNIQUE_KEY_FLAG & 49967
		// MYSQLI_MULTIPLE_KEY_FLAG & 49967
		// MYSQLI_BLOB_FLAG & 49967

		//foreach ($property as $a => $val) {
		//	echo $a.' '.$val."<br>";
		//}

		//complete list of flags from MySQL source code:
		//
		//NOT_NULL_FLAG   1       /* Field can't be NULL */
		//PRI_KEY_FLAG    2       /* Field is part of a primary key */
		//UNIQUE_KEY_FLAG 4       /* Field is part of a unique key */
		//MULTIPLE_KEY_FLAG 8     /* Field is part of a key */
		//BLOB_FLAG   16      /* Field is a blob */
		//UNSIGNED_FLAG   32      /* Field is unsigned */
		//ZEROFILL_FLAG   64      /* Field is zerofill */
		//BINARY_FLAG 128     /* Field is binary   */
		//ENUM_FLAG   256     /* field is an enum */
		//AUTO_INCREMENT_FLAG 512     /* field is a autoincrement field */
		//TIMESTAMP_FLAG  1024        /* Field is a timestamp */
		//SET_FLAG    2048        /* field is a set */
		//NO_DEFAULT_VALUE_FLAG 4096  /* Field doesn't have default value */
		//ON_UPDATE_NOW_FLAG 8192         /* Field is set to NOW on UPDATE */
		//NUM_FLAG    32768       /* Field is num (for clients) */
		//PART_KEY_FLAG   16384       /* Intern; Part of some key */
		//GROUP_FLAG  32768       /* Intern: Group field */
		//UNIQUE_FLAG 65536       /* Intern: Used by sql_yacc */
		//BINCMP_FLAG 131072      /* Intern: Used by sql_yacc */
		//GET_FIXED_FIELDS_FLAG (1 << 18) /* Used to get fields in item tree */
		//FIELD_IN_PART_FUNC_FLAG (1 << 19)/* Field part of partition func */     
		

		#echo"<b>[$col]</b>:"
		#.$META->colsbyname["$col"]->type
		#.' '.$META->{"cols"}["$col"]->size
		#.' '.$META->{"cols"}["$col"]->precision
		#.' '.$META->{"cols"}["$col"]->scale
		#.' '.$META->{"cols"}["$col"]->is_null
		#.' type='.$META->{"cols"}["$col"]->type_raw
		#.' '."<br>\n";   
  	}

	return $META;
}               

function oracleMetadata(& $db) {   
	$id 	= $db->Query_ID;
	$META = new stdClass();

	#echo "SQL=".$db->LastSQL."<br>";
	#echo "Columnas =".OCINumcols($id)."<br>";

	$META->cols = array();
	for($ix=1;$ix<=OCINumcols($id);$ix++) {
		$col 			= oci_field_name($id, $ix);
		$type 			= oci_field_type_raw($id,$ix); 
		$presicion      = oci_field_precision($id,$ix);
		$escala			= oci_field_scale($id,$ix);
		$standarType 	= MetaStandardType("Oracle",$type, $escala);
		
		$META->colsbyname[ "$col" ] = new stdClass();
		$META->colsbyname[ "$col" ]->{"type"}  		= $standarType;
		$META->colsbyname[ "$col" ]->{"precision"}  	= $presicion;
		$META->colsbyname[ "$col" ]->{"scale"}  		= $escala;
		$META->colsbyname[ "$col" ]->{"size"}  		= oci_field_size($id,$ix);
		$META->colsbyname[ "$col" ]->{"is_null"}  	= oci_field_is_null($id,$ix);  
		$META->colsbyname[ "$col" ]->{"type_raw"}  	= $type;  
		
		$META->cols[ $ix - 1 ] = new stdClass();
		$META->cols[ $ix - 1 ]->{"type"}  		= $standarType;
		$META->cols[ $ix - 1 ]->{"precision"} 	= $presicion;
		$META->cols[ $ix - 1 ]->{"scale"}  		= $escala;
		$META->cols[ $ix - 1 ]->{"size"}  		= oci_field_size($id,$ix);
		$META->cols[ $ix - 1 ]->{"is_null"}  	= oci_field_is_null($id,$ix);  
		$META->cols[ $ix - 1 ]->{"type_raw"}  	= $type;  
		
		//if($db->Debug) 
		#echo"<b>[$col]</b>:"
		#.$META->colsbyname["$col"]->type
		#.' '.$META->colsbyname["$col"]->size
		#.' Presicion='.$META->colsbyname["$col"]->precision
		#.' Ecala='.$META->colsbyname["$col"]->scale
		#.' '.$META->colsbyname["$col"]->is_null
		#.' type='.$META->colsbyname["$col"]->type_raw
		#.' '."<br>\n";   
	}   
	return $META;  
	
	
}                     
		
function metadata(& $db) {
	#
	# $db debe ser un objeto de DB de CodeCharge
	#	  

    $id 	= $db->Query_ID;
	$tipo 	= $db->Type;
	$META = new stdClass();

	if (!$id){
		$db->Errors->addError("Metadata query failed: No query specified.");
		return false;
	}
	
	switch ($tipo) {
		case "Oracle" : 
			#echo "<br>DATABASE TYPE=".$db->Type."<br>\n\n"; 
			return oracleMetadata($db); 
			break;
		case "MySQL"  : 
			if ($db->DB == "MySQLi") {
				#echo "<br>DATABASE TYPE=".$db->DB."<br>\n\n"; 
				return mysqliMetadata($db); 
			} else {
				#echo "<br>DATABASE TYPE=".$db->Type."<br>\n\n"; 
				return mysqlMetadata($db);
			}
			break;
		default: return false;
	}
  	
  	return $META;
}  	  



/// CLASES Y MANEJO //////////////////////////////////////////////

class clsGridResult { 

//Variables 

    // Public variables
    public $ComponentType = "Grid";
    public $ComponentName;
    public $Metadata;
    public $Query;
    public $Visible;
    public $Errors;
    public $ErrorBlock;
    public $ds;
    public $DataSource;
    public $PageSize;
    public $IsEmpty;
    public $ForceIteration = false;
    public $HasRecord = false;
    public $SorterName = "";
    public $SorterDirection = "";
    public $PageNumber;
    public $RowNumber;
    public $ControlsVisible = array();

    public $CCSEvents = "";
    public $CCSEventResult;

    public $RelativePath = "";
    public $Attributes;

    // Grid Controls
    public $StaticControls;
    public $RowControls;
//End Variables

//Class_Initialize Event 
    function clsGridResult($RelativePath, & $Parent, $Query)
    {
        global $FileName;
        global $CCSLocales;
        global $DefaultDateFormat;

        $this->ComponentName = "Result";
        $this->Visible = True;
        $this->Parent = & $Parent;
        $this->RelativePath = $RelativePath;
        $this->Errors = new clsErrors();
        $this->ErrorBlock = "Result";
        $this->Attributes = new clsAttributes($this->ComponentName . ":");

        ## ES IMPORTANTE MANEJAR EL SQL SEPARANDO EL SELECT + WHERE Y EL ORDER BY.
        ## AQUI ASUMINOS QUE NO HAY ORDER BY PUES DEBEMOS USAR UN LIMIT. SINO QUITARLO PROGRAMATICAMENTE.
        $this->Query = $Query;

        $this->DataSource = new clsResultDataSource($this);
        $this->ds = & $this->DataSource;

        ## YA AQUI DEBE EXISTIR EL METADATA QUE LO DEBE HABER CREADO clsResultDataSource
        ## VERIFICAR
        ## var_dump($this->Metadata);
        ## die;
        ## VERIFICADO (Y)
        ######################
        #$this->ds->Debug = 1;

        $this->PageSize = CCGetParam($this->ComponentName . "PageSize", "");
        if(!is_numeric($this->PageSize) || !strlen($this->PageSize))
            $this->PageSize = 10;
        else
            $this->PageSize = intval($this->PageSize);
        if ($this->PageSize > 100)
            $this->PageSize = 100;
        if($this->PageSize <= 0)
            $this->PageSize = 100;
        //$this->Errors->addError("<p>Form: Grid " . $this->ComponentName . "<BR>Error: (CCS06) Invalid page size.</p>");
        $this->PageNumber = intval(CCGetParam($this->ComponentName . "Page", 1));
        if ($this->PageNumber <= 0) $this->PageNumber = 1;
        //$this->PageSize = 9999999999;
        #echo "Param =".$this->ComponentName . "PageSize"."<br>" ;
        #echo "Page Size =".$this->PageSize."<br>" ;
        #echo "Page Number =".$this->PageNumber."<br>" ;
        foreach ($this->Metadata->colsbyname as $col => $prop) {
            $this->{$col} = new clsControl(ccsLabel, $col, $col, $prop->type, ($prop->type == ccsDate ? $DefaultDateFormat : ""), CCGetRequestParam($col, ccsGet, NULL), $this);
            $this->{$col}->HTML = true;
        }

    }
//End Class_Initialize Event

//Initialize Method 
    function Initialize()
    {
        if(!$this->Visible) return;

        $this->DataSource->PageSize = & $this->PageSize;
        $this->DataSource->AbsolutePage = & $this->PageNumber;
        $this->DataSource->SetOrder($this->SorterName, $this->SorterDirection);
    }
//End Initialize Method

//Show Method 
    function Show()
    {
        $Tpl = CCGetTemplate($this);
        #var_dump($Tpl);
        global $CCSLocales;  
        if(!$this->Visible) return;

        $this->RowNumber = 0;


        $this->CCSEventResult = CCGetEvent($this->CCSEvents, "BeforeSelect", $this);


        $this->DataSource->Prepare();
        $this->DataSource->Open();
        $this->HasRecord = $this->DataSource->has_next_record();  
        if ($this->DataSource->Errors->ToString()) die($this->DataSource->Errors->ToString()); 
		#echo "tiene registro ".$this->HasRecord.' '.$this->DataSource->RecordsCount;

        $this->IsEmpty = ! $this->HasRecord;
        $this->Attributes->Show();

        $this->CCSEventResult = CCGetEvent($this->CCSEvents, "BeforeShow", $this);
        if(!$this->Visible) return;

        $GridBlock = "Grid " . $this->ComponentName;
        $ParentPath = $Tpl->block_path;
        $Tpl->block_path = $ParentPath . "/" . $GridBlock;


		#echo "<br>Esta Vacio? ".$this->IsEmpty;
        if (!$this->IsEmpty) {  
        	foreach ($this->Metadata->colsbyname as $col => $prop) {
            	$this->ControlsVisible[$col] = $this->{$col}->Visible;
				#echo "<br>Esta visible ? $col ".$this->{$col}->Visible;
        	}
            while ($this->ForceIteration || (($this->RowNumber < $this->PageSize) &&  ($this->HasRecord = $this->DataSource->has_next_record()))) {
                // Parse Separator
                if($this->RowNumber) {
                    $this->Attributes->Show();
                    $Tpl->parseto("Separator", true, "Row");
                }
                $this->RowNumber++;
                if ($this->HasRecord) {
                    $this->DataSource->next_record();
                    $this->DataSource->SetValues();
                }
                $Tpl->block_path = $ParentPath . "/" . $GridBlock . "/Row";  
        		
        		foreach ($this->Metadata->colsbyname as $col => $prop) {
                
                	$this->{$col}->SetValue($this->DataSource->{$col}->GetValue());  
					#echo '<br>'.$GridBlock." ".$col." ".$this->{$col}->getValue();
        		}

                $this->Attributes->SetValue("rowNumber", $this->RowNumber);
                $this->CCSEventResult = CCGetEvent($this->CCSEvents, "BeforeShowRow", $this);

                $this->Attributes->Show();

        		foreach ($this->Metadata->colsbyname as $col => $prop) {
                	$this->{$col}->Show();
        		}

                $Tpl->block_path = $ParentPath . "/" . $GridBlock;
                $Tpl->parse("Row", true);
            }
        }

        $errors = $this->GetErrors();
        if(strlen($errors))
        {
            $Tpl->replaceblock("", $errors);
            $Tpl->block_path = $ParentPath;
            return;
        }
        $Tpl->parse();
        $Tpl->block_path = $ParentPath;
        $this->DataSource->close();
    }
//End Show Method

//GetErrors Method 
    function GetErrors()
    {
        $errors = "";  
        foreach ($this->Metadata->colsbyname as $col => $prop) {
        	$errors = ComposeStrings($errors, $this->{$col}->Errors->ToString());
        }

        $errors = ComposeStrings($errors, $this->Errors->ToString());
        $errors = ComposeStrings($errors, $this->DataSource->Errors->ToString());
        return $errors;
    }
//End GetErrors Method
} 

    
//BindEvents Method 
function BindEvents()
{
    global $Result;
    $Result->CCSEvents["BeforeShowRow"] = "ResultBeforeShowRow";
}
//End BindEvents Method

//ResultBeforeShowRow
function ResultBeforeShowRow(& $sender)
{
    $ResultBeforeShowRow = true;
    $Component = & $sender;
    $Container = & CCGetParentContainer($sender);
    global $Result; //Compatibility
//End ResultBeforeShowRow

//Format JSON 
    foreach ($Component->Metadata->colsbyname as $col => $prop) {
    	if ($prop->type == ccsText) {
    		$Component->{$col}->SetValue(str_replace(array("\\", '"', "/", "\n" , "\r", "\t", "\b"), array("\\\\", '\"', '\/', '\\n', '', '\t', '\b'), $Component->{$col}->GetValue()));
    	}
    }   
//End Format JSON

//Close ResultBeforeShowRow 
    return $ResultBeforeShowRow;
}
//End Close ResultBeforeShowRow

function json_validate($string,$flag=false)
{
    // clena the string
    $string = str_replace("\n", "", $string);
    $string = str_replace("\r", "", $string);

    // decode the JSON data
    $result = json_decode($string, $flag);

    // switch and check possible JSON errors
    switch (json_last_error()) {
        case JSON_ERROR_NONE:
            $error = ''; // JSON is valid // No error has occurred
            break;
        case JSON_ERROR_DEPTH:
            $error = 'The maximum stack depth has been exceeded.';
            break;
        case JSON_ERROR_STATE_MISMATCH:
            $error = 'Invalid or malformed JSON.';
            break;
        case JSON_ERROR_CTRL_CHAR:
            $error = 'Control character error, possibly incorrectly encoded.';
            break;
        case JSON_ERROR_SYNTAX:
            $error = 'Syntax error, malformed JSON.';
            break;
        // PHP >= 5.3.3
        case JSON_ERROR_UTF8:
            $error = 'Malformed UTF-8 characters, possibly incorrectly encoded.';
            break;
        // PHP >= 5.5.0
        case JSON_ERROR_RECURSION:
            $error = 'One or more recursive references in the value to be encoded.';
            break;
        // PHP >= 5.5.0
        case JSON_ERROR_INF_OR_NAN:
            $error = 'One or more NAN or INF values in the value to be encoded.';
            break;
        case JSON_ERROR_UNSUPPORTED_TYPE:
            $error = 'A value of a type that cannot be encoded was given.';
            break;
        default:
            $error = 'Unknown JSON error occured.';
            break;
    }

    if ($error !== '') {
        // throw the Exception or exit // or whatever :)
        exit($error);
    }

    // everything is OK
    return $result;
}

?>
