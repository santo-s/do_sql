# do_sql.js
Ejecuta instrucciones DML (Data Manipulation Language)
 de SQL desde JavaScript.

Este proyecto forma parte un proyecto mayor que consiste en permitir la creaci�n de bloques de c�digo tipo PLSQL de Oracle usando JavaScript.
La estructura b�sica de trabajo es:

try { // BEGIN
	... codigo javascript
	... codigo SQL
	... codigo javascript
} catch { // EXCEPTION
	.. manejo de errores
} // END

El c�digo SQL y la base de datos donde se ejecuta dicho c�digo, no es dependiente de Oracle. Es decir puede ser cualquier base de datos. 
Pero, para efectos de este proyecto el c�digo esta siendo probado, simult�neamente, con la base de datos Oracle y con MySql.
