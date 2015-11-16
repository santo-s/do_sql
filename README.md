# do_sql.js
Ejecuta instrucciones DML (Data Manipulation Language)
 de SQL desde JavaScript.

Este proyecto forma parte un proyecto mayor que consiste en permitir la creación de bloques de código tipo PLSQL de Oracle usando JavaScript.
La estructura básica de trabajo es:
<pre>
try { // BEGIN
	... codigo javascript
	... codigo SQL
	... codigo javascript
} catch { // EXCEPTION
	.. manejo de errores
} // END
</pre>
El código SQL y la base de datos donde se ejecuta dicho código, no es dependiente de Oracle. Es decir puede ser cualquier base de datos. 
Pero, para efectos de este proyecto el código esta siendo probado, simultáneamente, con la base de datos Oracle y con MySql.
<table><tr><td width="200px" style="width:200px">
<h3>Instalación</h3>
</td><td><h3>Installation</h3></td></tr>
<tr>
<td>Para hacer uso de do_sql.js se requiere Apache y php 5.4 o superior del lado del servidor.


Copie los archivos en su servidor WEB.

La única configuración necesaria es crear los archivos de "datasource" ubicados en el directorio "textdb". Un datasource contiene la información necesaria para conectarse a la base de datos. Cada datasource tiene un nombre el cual se especifica en la clase DO_SQL_CLASS para indicar donde y como conectarse. Los archivos se nombre con la siguiente connotación:

datasourcename.sources.json

Donde "datasourcename" es el nombre como la clase DO_SQL reconoce a la base de datos.

A continuación hay un ejemplo para una base de datos Oracle y una base de datos MySql.

Oracle:
<pre>
{
  "Type"          : "Oracle",
  "DBLib"         : "OracleOCI",
  "Database"      : "YOUR-SERVER:YOUR-PORT/YOUR-DATABASE-SID",
  "Host"          : "",
  "Port"          : "",
  "User"          : "your-user",
  "Password"      : "your-password",
  "Encoding"      : "UTF8",
  "Persistent"    : false,
  "DateFormat"    : ["yyyy", "-", "mm", "-", "dd", " ", "HH", ":", "nn", ":", "ss"],
  "BooleanFormat" : [1, 0, ""],
  "Uppercase"     : false
}
</pre>

MySql:
<pre>
{
  "Type"          : "MySQL",
  "DBLib"         : "MySQLi",
  "Database"   : "your-database",
  "Host"          : "your-host",
  "Port"          : "3306",
  "User"          : "your-user",
  "Password"      : "your-password",
  "Encoding"      : ["", "utf8"],
  "Persistent"    : false,
  "DateFormat"    : ["yyyy", "-", "mm", "-", "dd", " ", "HH", ":", "nn", ":", "ss"],
  "BooleanFormat" : [1, 0, ""],
  "Uppercase"     : false
}
</pre>

Por favor utilice una copia de este ejemplo para crear los archivos. El Type y DBLib son importantes para saber que libreria utilizar y que tipo de base de datos es. Deje las variables Encoding, Persistent, DateFormat, BooleanFormat y Uppercaso tal cual para cada tipo de base de datos.

Default datasource.
En el directorio textdb hay un datasource llamado "default", (default.sources.json), este datasource será utilizado cuando no especificamos uno en la instanciación del la clases DO_SQL_CLASS. 
</td>
<td>To use do_sql.js Apache and PHP 5.4 or higher required  on server side.

Copy the files on your WEB server.

The only configuration required is to create files " datasource " located in the " textdb " directory. A data source contains the information needed to connect to the database. Each data source has a name which is specified in the DO_SQL_CLASS class to indicate where and how to connect. The files are named with the following connotation :

datasourcename.sources.json

Where he " DataSourceName " is the name as the class DO_SQL recognizes  the database .

Below there is an example for Oracle database and MySql database .

Oracle:
<pre>
{
  "Type"          : "Oracle",
  "DBLib"         : "OracleOCI",
  "Database"      : "YOUR-SERVER:YOUR-PORT/YOUR-DATABASE-SID",
  "Host"          : "",
  "Port"          : "",
  "User"          : "your-user",
  "Password"      : "your-password",
  "Encoding"      : "UTF8",
  "Persistent"    : false,
  "DateFormat"    : ["yyyy", "-", "mm", "-", "dd", " ", "HH", ":", "nn", ":", "ss"],
  "BooleanFormat" : [1, 0, ""],
  "Uppercase"     : false
}
</pre>

MySql:
<pre>
{
  "Type"          : "MySQL",
  "DBLib"         : "MySQLi",
  "Database"   : "your-database",
  "Host"          : "your-host",
  "Port"          : "3306",
  "User"          : "your-user",
  "Password"      : "your-password",
  "Encoding"      : ["", "utf8"],
  "Persistent"    : false,
  "DateFormat"    : ["yyyy", "-", "mm", "-", "dd", " ", "HH", ":", "nn", ":", "ss"],
  "BooleanFormat" : [1, 0, ""],
  "Uppercase"     : false
}
</pre>


Please use a copy of this example to create the files . The Type and DBLib variables are important to know what library use and what type of database is . Leave Encoding , Persistent , DateFormat , BooleanFormat and Uppercase variables such for each type of database.

Default datasource .
in the directory textdb there is a datasource called "default " ( default.sources.json ), this datasource will be used when you do not specify one on instantiation of the DO_SQL_CLASS classes.

</td>
</tr>
</table>
