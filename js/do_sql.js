/**
 * Created by SANTO on 27/10/2015.
 */

/** *************************************************************
 * CASO TABLE OF. Maneja una arreglo del tipo PLSQL
 * ESTO DEBE ESTAR EN EL JS GENERAL
 * REQUIERE: Class.js
 *****************************************************************  */
var DO_SQL_EXCEPTION_CLASS = Class.extend({
    init: function (sqlcode, sqlerrm, sqltype) {

        this.ACCESS_INTO_NULL       = 'ACCESS_INTO_NULL';
        this.CASE_NOT_FOUND         = 'CASE_NOT_FOUND';
        this.COLLECTION_IS_NULL     = 'COLLECTION_IS_NULL';
        this.CURSOR_ALREADY_OPEN    = 'CURSOR_ALREADY_OPEN';
        this.DUP_VAL_ON_INDEX       = 'DUP_VAL_ON_INDEX';
        this.INVALID_CURSOR         = 'INVALID_CURSOR';
        this.INVALID_NUMBER         = 'INVALID_NUMBER';
        this.LOGIN_DENIED           = 'LOGIN_DENIED';
        this.NO_DATA_FOUND          = 'NO_DATA_FOUND';
        this.NOT_LOGGED_ON          = 'NOT_LOGGED_ON';
        this.PROGRAM_ERROR          = 'PROGRAM_ERROR';
        this.ROWTYPE_MISMATCH       = 'ROWTYPE_MISMATCH';
        this.SELF_IS_NULL           = 'SELF_IS_NULL';
        this.STORAGE_ERROR          = 'STORAGE_ERROR';
        this.SUBSCRIPT_BEYOND_COUNT = 'SUBSCRIPT_BEYOND_COUNT';
        this.SUBSCRIPT_OUTSIDE_LIMIT = 'SUBSCRIPT_OUTSIDE_LIMIT';
        this.SYS_INVALID_ROWID      = 'SYS_INVALID_ROWID';
        this.TIMEOUT_ON_RESOURCE    = 'TIMEOUT_ON_RESOURCE';
        this.TOO_MANY_ROWS          = 'TOO_MANY_ROWS';
        this.VALUE_ERROR            = 'VALUE_ERROR';
        this.ZERO_DIVIDE            = 'ZERO_DIVIDE';
        this.OTHERS                 = 'OTHERS';

        this.EXCEPTIONS = [];
        this.EXCEPTIONS['ACCESS_INTO_NULL'+'Oracle']=6530;
        this.EXCEPTIONS['CASE_NOT_FOUND'+'Oracle']=6592;
        this.EXCEPTIONS['COLLECTION_IS_NULL'+'Oracle']=6531;
        this.EXCEPTIONS['CURSOR_ALREADY_OPEN'+'Oracle']=6511;
        this.EXCEPTIONS['DUP_VAL_ON_INDEX'+'Oracle']=1;
        this.EXCEPTIONS['INVALID_CURSOR'+'Oracle']=1001;
        this.EXCEPTIONS['INVALID_NUMBER'+'Oracle']=1722;
        this.EXCEPTIONS['LOGIN_DENIED'+'Oracle']=1017;
        this.EXCEPTIONS['NO_DATA_FOUND'+'Oracle']=1403;
        this.EXCEPTIONS['NOT_LOGGED_ON'+'Oracle']=1012;
        this.EXCEPTIONS['PROGRAM_ERROR'+'Oracle']=6501;
        this.EXCEPTIONS['ROWTYPE_MISMATCH'+'Oracle']=6504;
        this.EXCEPTIONS['SELF_IS_NULL'+'Oracle']=30625;
        this.EXCEPTIONS['STORAGE_ERROR'+'Oracle']=6500;
        this.EXCEPTIONS['SUBSCRIPT_BEYOND_COUNT'+'Oracle']=6533;
        this.EXCEPTIONS['SUBSCRIPT_OUTSIDE_LIMIT'+'Oracle']=6532;
        this.EXCEPTIONS['SYS_INVALID_ROWID'+'Oracle']=1410;
        this.EXCEPTIONS['TIMEOUT_ON_RESOURCE'+'Oracle']=51;
        this.EXCEPTIONS['TOO_MANY_ROWS'+'Oracle']=1422;
        this.EXCEPTIONS['VALUE_ERROR'+'Oracle']=6502;
        this.EXCEPTIONS['ZERO_DIVIDE'+'Oracle']=1476;
        //
        this.EXCEPTIONS['ACCESS_INTO_NULL'+'MySQL']='';
        this.EXCEPTIONS['CASE_NOT_FOUND'+'MySQL']=1339;
        this.EXCEPTIONS['COLLECTION_IS_NULL'+'MySQL']='';
        this.EXCEPTIONS['CURSOR_ALREADY_OPEN'+'MySQL']=1325;
        this.EXCEPTIONS['DUP_VAL_ON_INDEX'+'MySQL']=1022;
        this.EXCEPTIONS['INVALID_CURSOR'+'MySQL']=1326;
        this.EXCEPTIONS['INVALID_NUMBER'+'MySQL']=1367;
        this.EXCEPTIONS['LOGIN_DENIED'+'MySQL']=1045;
        this.EXCEPTIONS['NO_DATA_FOUND'+'MySQL']=1329;
        this.EXCEPTIONS['NOT_LOGGED_ON'+'MySQL']='';
        this.EXCEPTIONS['PROGRAM_ERROR'+'MySQL']='';
        this.EXCEPTIONS['ROWTYPE_MISMATCH'+'MySQL']='';
        this.EXCEPTIONS['SELF_IS_NULL'+'MySQL']='';
        this.EXCEPTIONS['STORAGE_ERROR'+'MySQL']='';
        this.EXCEPTIONS['SUBSCRIPT_BEYOND_COUNT'+'MySQL']='';
        this.EXCEPTIONS['SUBSCRIPT_OUTSIDE_LIMIT'+'MySQL']='';
        this.EXCEPTIONS['SYS_INVALID_ROWID'+'MySQL']='';
        this.EXCEPTIONS['TIMEOUT_ON_RESOURCE'+'MySQL']=1205;
        this.EXCEPTIONS['TOO_MANY_ROWS'+'MySQL']=1172;
        this.EXCEPTIONS['VALUE_ERROR'+'MySQL']=1367;
        this.EXCEPTIONS['ZERO_DIVIDE'+'MySQL']=1365;
        //
        this.EXCEPTIONS['ACCESS_INTO_NULL'+'POSTGRES']='';
        this.EXCEPTIONS['CASE_NOT_FOUND'+'POSTGRES']=20000;
        this.EXCEPTIONS['COLLECTION_IS_NULL'+'POSTGRES']='';
        this.EXCEPTIONS['CURSOR_ALREADY_OPEN'+'POSTGRES']='';
        this.EXCEPTIONS['DUP_VAL_ON_INDEX'+'POSTGRES']=23505;
        this.EXCEPTIONS['INVALID_CURSOR'+'POSTGRES']='';
        this.EXCEPTIONS['INVALID_NUMBER'+'POSTGRES']='';
        this.EXCEPTIONS['LOGIN_DENIED'+'POSTGRES']=28000;
        this.EXCEPTIONS['NO_DATA_FOUND'+'POSTGRES']='P0002';
        this.EXCEPTIONS['NOT_LOGGED_ON'+'POSTGRES']='';
        this.EXCEPTIONS['PROGRAM_ERROR'+'POSTGRES']='';
        this.EXCEPTIONS['ROWTYPE_MISMATCH'+'POSTGRES']='';
        this.EXCEPTIONS['SELF_IS_NULL'+'POSTGRES']='';
        this.EXCEPTIONS['STORAGE_ERROR'+'POSTGRES']='';
        this.EXCEPTIONS['SUBSCRIPT_BEYOND_COUNT'+'POSTGRES']='';
        this.EXCEPTIONS['SUBSCRIPT_OUTSIDE_LIMIT'+'POSTGRES']='';
        this.EXCEPTIONS['SYS_INVALID_ROWID'+'POSTGRES']='';

        //this.NO_DATA_FOUND = 1403;
        this.SQLCODE = sqlcode;
        this.SQLERRM = sqlerrm;
        this.SQLTYPE = sqltype;
    }
    , EXCEPTION : function(check) {
        console.log("CHECK EXCEPTION "+check);
        if (check == "OTHERS") {
            return true;
        } else if (this.SQLCODE == this.EXCEPTIONS[check+this.SQLTYPE]) {
            return true;
        } else {
            return false;
        }
    }
});

var TableOfPLSQL = Class.extend({
    init: function(){
        this.table = new Object();
        this.LAST = null;
        this.FIRST = null;
    }

    ,tabsize : function(obj) {
        var size = 0, first = 0, last = 0, key;
        for (key in obj) {
            if (obj.hasOwnProperty(key)) {
                if (first === 0) first = key;
                else if(key < first) first = key;
                if(key > last) last = key;
                size++;
            }
        }
        console.log("Inside TABLE fisrt = "+first)
        this.LAST = last;
        this.FIRST = first;
        return size;
    }

    , setValue : function (pos, value) {
        this.table[pos] = value;
        console.log("Inside TABLE set = "+ pos + " val "+value);
        return this.tabsize(this.table);
    }
    , getValue : function (pos) {
        return this.table[pos];
    }
    , deleteAll : function () {
        this.table = new Object();
        this.LAST = null;
        this.FIRST = null;
    }
    , delete : function (pos) {
        if (typeof this.table[pos] != "undefined") {
            delete this.table[pos];
            this.tabsize(this.table);
        }
    }

    , DELETE : function($i) {
        if (arguments.length === 0) {
            this.deleteAll();
            return;
        } else {
            this.delete($i);
        }
    }

});

var PLSQL_GENERIC_TAB = function (PLSQL_TYPE, TAB, $i, $val) {
    if (arguments.length == 3) {
        var a = PLSQL_TYPE.getValue($i);
        TAB.LAST = PLSQL_TYPE.LAST;
        TAB.FIRST = PLSQL_TYPE.FIRST;
        return a;
    } else if (arguments.length == 4) {
        PLSQL_TYPE.setValue($i,$val);
        TAB.LAST = PLSQL_TYPE.LAST;
        TAB.FIRST = PLSQL_TYPE.FIRST;
    }
}

PLSQL_GENERIC_TAB.DELETE = function(PLSQL_TYPE, TAB, $i) {
    if (arguments.length === 2) {
        PLSQL_TYPE.deleteAll();
    } else {
        PLSQL_TYPE.delete($i);
    }
    TAB.LAST = PLSQL_TYPE.LAST;
    TAB.FIRST = PLSQL_TYPE.FIRST;

}
// UNA TABLA PLSQL CONTEMPLA ESTOS METODOS
// MOV.LAST         OK
// MOV.FIRST        OK
// MOV.DELETE       OK
// MOV.DELETE(n)    OK
// MOV.DELETE(n,m)  // TODO Elimina un rango
// MOV.TRIM         TODO
// MOV.TRIM(n)      TODO
// MOV.EXTEND //    TODO Crea posiciones vacias
// MOV.EXTEND(n)    TODO
/******************************************************************/


/** *************************************************************
 * CASO CURSOR Maneja una arreglo interno simulando un cursor SQL
 * REQUIERE: Class.js y JQuery
 * Necesito 2 parametros:
 * 1) Lista de parametros en forma de opciones para el SQL
 * 2) El SQL
 *
 * Nota: No contempla el FOR UPDATE
 *****************************************************************  */
var CursorPLSQL = Class.extend({
    init: function(options, SQL){
        this.options = options;
        this.SQL = SQL;
        this.parameters = {};
        this.datasource = "default";
        this.buffer_size  = 100;
        this.parameters.first_time = true;
        this.header = {}; // Se necesita la estructura de campos resultante del select
        this.datatable = []; // El data table guarda por posicion
        this.last_record  = 0;
        this.current_num_record = 0;
        this.max_record = 0;
        this.last_page = 0;
        this.status = "CLOSED";
        //console.log("CURSOR init");
        this.SQL = this.PREPARE();
    }

    , PREPARE : function() {
        // Prepara el sql adicionando los valores de parametros
        return this.SQL
    }
    ,setHeader : function(header) {
        this.header = header;
    }
    ,setDataTable : function(datatable) {
        //this.datatable = datatable;
        this.datatable.push.apply(this.datatable, datatable);
    }
    ,OPEN : function() {
        // Llama Ajax en modo sync.
        this.header = {}; // Se necesita la estructura de campos resultante del select
        this.datatable = []; // El data table guarda por posicion
        this.last_record  = 0;
        this.max_record = 0;
        this.last_page = 0;
        this.end_of_records = false;
        this.fillBuffer();
    }
    ,fillBuffer : function() {
        // Llama Ajax en modo sync.
        console.log("CALL AJAX");
        var header ={};
        var datatable = [];
        $.ajax({
            type: 'POST'
            ,url: '../services/metadato_4.php?sourcename='+this.datasource+'&ResultPageSize='+this.buffer_size+'&ResultPage='+(this.last_page+1)
            ,data: {"SQL" : this.SQL}
            ,success : function(data, x, y) {
                console.log("CALL AJAX success");
                console.log(data);
                if (data.ERROR.CODE != "0") {
                    alert("ERROR: "+data.ERROR.MESSAGE);
                }
                header = data.HEADER;
                datatable =  data.DATA;
            }
            ,error : function(data, x, y) {
                console.log("ERROR ");
                console.log(data);
            }
            ,dataType: "json"
            ,async:false
        });
        this.setHeader(header);
        this.setDataTable(datatable);
        this.max_record = this.datatable.length;
        //console.log(" LEIDOS "+datatable.length+ " BUFFER= "+this.buffer_size, " MAX TABLE SIZE="+this.max_record);
        if (datatable.length < this.buffer_size) {
            this.end_of_records = true;
        }
        if (datatable.length == 0) {
            this.end_of_records = true;
            return false;
        }
        this.status = "OPEN";
        this.last_page++;
        return true;

    }

    ,FETCH_RECORD : function() {
        if (this.status == "CLOSE") {
            //raise CANNOT FETCH AN CLOSED CURSOR;
            console.log("CANNOT FETCH A CLOSED CURSOR");
            return false;
        }

        if (this.last_record+1 > this.max_record) {
            if (this.end_of_records) {
                return false;
            }
            if (!this.fillBuffer()) {
                return false;
            };
            console.log("NO HAY MAS REGISTROS ="+this.end_of_records);
        }
        //if (this.last_record+1 <= this.max_record) {
        var pos = 0;
        var rec = this.last_record;
        var data = this.datatable;
        var structure = this;
        $.each(this.header, function(index) {
            structure[index] = data[rec][pos];
            pos++;
        });
        this.last_record++;
        this.current_num_record = this.last_record;
        return true;
    }

    ,FETCH_INTO : function() {
        return error;
    }

    ,CLOSE : function() {
        // CLEAR data table to free resource
        this.datatable = [];
        this.header = {};
        this.last_record  = 0;
        this.max_record = 0;
        this.last_page = 0;

        this.status = "CLOSE";
    }

});


/** *************************************************************
 * DO_SQL ejecuta UPDATE, DELETE o SELECT .. INTO ..
 * Debe contemplar BIND variables
 *
*****************************************************************  */
var DO_SQL_CLASS = Class.extend({
    init: function(datasource){
        this.datasource = (typeof datasource == 'undefined' ? "default" : datasource);
        this.parameters = {};
        this.pass_phrase = "PASS PHRASE";
        this.async = false;

    }

    , BIND : function (bind_varibales) {
        this.parameters = bind_varibales;
    }

    /*
    , SQL_ERROR : function (sqlcode, sqlerrm) {
        var ERROR = new Error("DO SQL ERROR: "+data.ERROR.MESSAGE);
        ERROR.SQL = new DO_SQL_EXCEPTION_CLASS(sqlcode, sqlerrm );
        return ERROR;
    }
*/
    , SQL_ADVANCED : function (SQL) {
        return this.SQL(SQL, "advanced");

    }

    , SQL : function (SQL, level) {

        if (typeof level == "undefined") level = "single";

        var data = {};
        data.BIND = JSON.stringify(this.parameters);
        //data.SQL = SQL;
        data.SQL = CryptoJS.AES.encrypt(JSON.stringify(SQL), this.pass_phrase, {format: CryptoJSAesJson}).toString();
        data.level = level;
        var result = false;
        var async = this.async;

        $.ajax({
            type: 'POST'
            ,url: '../services/do_sql_from_string.php?sourcename='+this.datasource
            ,data: data
            ,success : function(data, x, y) {
                console.log("CALL AJAX success");
                if (data.ERROR.CODE != "0") {
                    var ERROR = new Error("DO SQL ERROR: "+data.ERROR.MESSAGE);
                    console.log("Asigno error "+data.ERROR.TYPE);
                    ERROR.SQL = new DO_SQL_EXCEPTION_CLASS(data.ERROR.CODE, data.ERROR.MESSAGE, data.ERROR.TYPE );
                    console.log("Asignado error "+ERROR.SQL.SQLCODE);
                    throw ERROR;
                    return false;
                }
                result =  data.RESULT;
            }
            ,error : function(data, x, y) {
                //console.log("DO SQL ERROR: "+"BAD RESPONSE");
                //console.log(data);
                var ERROR = new Error("DO SQL ERROR: "+data.ERROR.MESSAGE);
                ERROR.SQL = new DO_SQL_EXCEPTION_CLASS(data.ERROR.CODE, data.ERROR.MESSAGE, data.ERROR.TYPE  );
                throw ERROR;
                return false;
            }
            ,dataType: "json"
            ,async:async
        });
        if (!result) return false;

        this.parameters = result;
        // RETORNA VALOR
        return this.parameters;
    }

    , SQL_CONNECT : function (user, password, datasource) {
        var datasource = (datasource ? datasource : this.datasource );
        var data = {};
        data.user= user;
        data.password = CryptoJS.AES.encrypt(JSON.stringify(password), this.pass_phrase, {format: CryptoJSAesJson}).toString();
        var result = false;
        $.ajax({
            type: 'POST'
            ,url: '../services/do_sql_from_string.php?sourcename='+datasource+"&connect=true"
            ,data: data
            ,success : function(data, x, y) {
                console.log("CALL AJAX CONNECT success");
                console.log(data);
                if (data.ERROR.CODE != "0") {
                    var ERROR = new Error("DO SQL ERROR: "+data.ERROR.MESSAGE);
                    ERROR.SQL = new DO_SQL_EXCEPTION_CLASS(data.ERROR.CODE, data.ERROR.MESSAGE, data.ERROR.TYPE  );
                    throw ERROR;
                    return false;
                }
                result =  true;
            }
            ,error : function(data, x, y) {
                //console.log("DO SQL ERROR: "+"BAD RESPONSE");
                //console.log(data);
                var ERROR = new Error("DO SQL ERROR: "+data.ERROR.MESSAGE);
                ERROR.SQL = new DO_SQL_EXCEPTION_CLASS(data.ERROR.CODE, data.ERROR.MESSAGE, data.ERROR.TYPE  );
                throw ERROR;
                return false;
            }
            ,dataType: "json"
            ,async:false
        });
        this.connected = result;
        return this.connected;
    }

    , SQL_DIALOG_CONNECT : function (continue_to) {

        var datasource = this.datasource;
        var e = $('<style>' +
        ' #dialog-form label, input { display:block; }' +
        ' #dialog-form input.text { margin-bottom:12px; width:95%; padding: .4em; }' +
        ' #dialog-form fieldset { padding:0; border:0; margin-top:25px; }' +
        ' .ui-dialog .ui-state-error { padding: .3em; }' +
        ' .validateTips { border: 1px solid transparent; padding: 0.3em; }' +
        ' #dialog-form {' +
        '   font-family: "Trebuchet MS", "Helvetica", "Arial",  "Verdana", "sans-serif";' +
        '   font-size: 80%;' +
        ' } ' +
        ' .ui-dialog .ui-dialog-buttonset { font-size: .7em !important; }' +
        ' .ui-dialog-titlebar { font-size: .7em !important; }' +
        '</style>' +
        '<div id="dialog-form" title="Connect to Database: '+ datasource  +'">' +
        '<form>' +
        '<fieldset>' +
        '    <label for="username">User Name</label>' +
        '    <input type="text" name="username" id="username" value="" class="text ui-widget-content ui-corner-all">' +
        '    <label for="password">Password</label>' +
        '    <input type="password" name="password" id="password" value="" class="text ui-widget-content ui-corner-all">' +
        '        <!-- Allow form submission with keyboard without duplicating the dialog button --> ' +
        '<input type="submit" tabindex="-1" style="position:absolute; top:-1000px"> ' +
        '</fieldset> ' +
        '</form> ' +
        '</div>');
        $('body').append(e);

        var connect
            , form
            ,username = $( "#username" )
            ,password = $( "#password" )
            ,allFields = $( [] ).add( username ).add( password );
        var parent = this;

        connect = $("#dialog-form").dialog({
            autoOpen    : false,
            height      : 250,
            resizable   : false,
            width       : 350,
            modal       : true,
            buttons: {
                Connect : function () {
                    parent.SQL_CONNECT(username.val(), password.val(), parent.datasource);
                    allFields.removeClass( "ui-state-error" );
                    form[0].reset();
                    if (parent.connected) {
                        $(this).dialog("close");
                        if (typeof continue_to == 'function') {
                            continue_to();
                        }
                    }

                }, //DO_SQL.SQL_CONNECT(username, password),
                Cancel : function () {
                    $(this).dialog("close");
                }
            },
            close: function () {
                form[0].reset();
                allFields.removeClass("ui-state-error");
            }
        });

        form = connect.find("form").on("submit", function (event) {
            event.preventDefault();
        });

        connect.dialog( "open" );
    }

});

