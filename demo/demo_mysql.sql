GRANT USAGE ON *.* TO demo@localhost IDENTIFIED BY 'demo' REQUIRE NONE;
GRANT Select  ON *.* TO demo@localhost;
GRANT Insert  ON *.* TO demo@localhost;
GRANT Update  ON *.* TO demo@localhost;
GRANT Delete  ON *.* TO demo@localhost;
GRANT Create routine  ON *.* TO demo@localhost;
GRANT Alter routine  ON *.* TO demo@localhost;

CREATE DATABASE demo
    CHARACTER SET 'utf8'
    COLLATE 'utf8_general_ci';
    
use demo;    

create table bonus
(
  ename  varchar(10),
  job    varchar(9),
  sal    float(38,15),
  comm   float(38,15)
);

create table dept
(
  deptno  int(2),
  dname   varchar(14),
  loc     varchar(13)
);

create unique index pk_dept on dept
(deptno);

alter table dept add (
  constraint pk_dept
  primary key
  (deptno)
--  using index pk_dept
--  enable validate
  );

  create table emp
(
  empno     int(4),
  ename     varchar(10),
  job       varchar(9),
  mgr       int(4),
  hiredate  date,
  sal       float(7,2),
  comm      float(7,2),
  deptno    int(2)
);


create unique index pk_emp on emp
(empno);


alter table emp add (
  constraint pk_emp
  primary key
  (empno)
  -- using index pk_emp
  -- enable validate
  );
  
create table salgrade
(
  grade  float(7,2),
  losal  float(7,2),
  hisal  float(7,2)
);
use demo;
truncate table dept;
insert into dept(deptno, dname, loc) Values(10, 'ACCOUNTING', 'NEW YORK');
insert into dept(deptno, dname, loc) Values(20, 'RESEARCH', 'DALLAS');
insert into dept(deptno, dname, loc) Values(30, 'SALES', 'CHICAGO');
insert into dept(deptno, dname, loc) Values(40, 'OPERATIONS', 'BOSTON');
COMMIT;

truncate table emp;
insert into emp (empno, ename, job, mgr, hiredate, sal, deptno)       Values (7369, 'SMITH', 'CLERK', 7902, STR_TO_DATE('12/17/1980 00:00:00', '%m/%d/%Y %H:%i:%s'), 800, 20);
insert into emp (empno, ename, job, mgr, hiredate, sal, comm, deptno) Values (7499, 'ALLEN', 'SALESMAN', 7698, STR_TO_DATE('02/20/1981 00:00:00', '%m/%d/%Y %H:%i:%s'), 1600, 300, 30);
insert into emp (empno, ename, job, mgr, hiredate, sal, comm, deptno) Values (7521, 'WARD', 'SALESMAN', 7698, STR_TO_DATE('02/22/1981 00:00:00', '%m/%d/%Y %H:%i:%s'), 1250, 500, 30);
insert into emp (empno, ename, job, mgr, hiredate, sal, deptno)       Values (7566, 'JONES', 'MANAGER', 7839, STR_TO_DATE('04/02/1981 00:00:00', '%m/%d/%Y %H:%i:%s'), 2975, 20);
insert into emp (empno, ename, job, mgr, hiredate, sal, comm, deptno) Values (7654, 'MARTIN', 'SALESMAN', 7698, STR_TO_DATE('09/28/1981 00:00:00', '%m/%d/%Y %H:%i:%s'), 1250, 1400, 30);
insert into emp (empno, ename, job, mgr, hiredate, sal, deptno)       Values (7698, 'BLAKE', 'MANAGER', 7839, STR_TO_DATE('05/01/1981 00:00:00', '%m/%d/%Y %H:%i:%s'), 2850, 30);
insert into emp (empno, ename, job, mgr, hiredate, sal, deptno)       Values (7782, 'CLARK', 'MANAGER', 7839, STR_TO_DATE('06/09/1981 00:00:00', '%m/%d/%Y %H:%i:%s'), 2450, 10);
insert into emp(empno, ename, job, mgr, hiredate, sal, deptno)        Values (7788, 'SCOTT', 'ANALYST', 7566, STR_TO_DATE('04/19/1987 00:00:00', '%m/%d/%Y %H:%i:%s'), 3000, 20);
insert into emp (empno, ename, job, hiredate, sal, deptno)            Values (7839, 'KING', 'PRESIDENT', STR_TO_DATE('11/17/1981 00:00:00', '%m/%d/%Y %H:%i:%s'), 5000, 10);
insert into emp (empno, ename, job, mgr, hiredate, sal, comm, deptno) Values (7844, 'TURNER', 'SALESMAN', 7698, STR_TO_DATE('09/08/1981 00:00:00', '%m/%d/%Y %H:%i:%s'), 1500, 0, 30);
insert into emp (empno, ename, job, mgr, hiredate, sal, deptno)       Values (7876, 'ADAMS', 'CLERK', 7788, STR_TO_DATE('05/23/1987 00:00:00', '%m/%d/%Y %H:%i:%s'), 1100, 20);
insert into emp (empno, ename, job, mgr, hiredate, sal, deptno)       Values (7900, 'JAMES', 'CLERK', 7698, STR_TO_DATE('12/03/1981 00:00:00', '%m/%d/%Y %H:%i:%s'), 950, 30);
insert into emp (empno, ename, job, mgr, hiredate, sal, deptno)       Values (7902, 'FORD', 'ANALYST', 7566, STR_TO_DATE('12/03/1981 00:00:00', '%m/%d/%Y %H:%i:%s'), 3000, 20);
insert into emp (empno, ename, job, mgr, hiredate, sal, deptno)       Values (7934, 'MILLER', 'CLERK', 7782, STR_TO_DATE('01/23/1982 00:00:00', '%m/%d/%Y %H:%i:%s'), 1300, 10);
COMMIT;

truncate table salgrade;
insert into salgrade(grade, losal, hisal) Values(1, 700, 1200);
insert into salgrade(grade, losal, hisal) Values(2, 1201, 1400);
insert into salgrade(grade, losal, hisal) Values(3, 1401, 2000);
insert into salgrade(grade, losal, hisal) Values(4, 2001, 3000);
insert into salgrade(grade, losal, hisal) Values(5, 3001, 9999);
COMMIT;