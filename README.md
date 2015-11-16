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
<table><tr><td>
<h1>Instalacion</h1>
</td><td>Installation</td></tr></table>
