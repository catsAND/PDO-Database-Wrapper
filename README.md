# PDO Database Wrapper
Simple PDO Wrapper with named and question mark placeholders for easy and fast development. Try it!

## Requirements

* `PHP 5.3` or greater
* PDO extension

## Instalation

### Download the files.

* You can [download](https://github.com/catsAND/PDO-Database-Wrapper/archive/v1.0.zip) them directly 
and extract them to your web directory.

### Composer

* In progress

## Features

  * Simple syntax.
  * Question mark placeholders with types.
  * Named placeholders.

## Functions

### List
```php
public query($sql, ...value)

public select($sql, ...value)

public selectCell($sql, ...value);
public column($sql, ...value);
public cell($sql, ...value);

public selectRow($sql, ...value);
public fetch($sql, ...value);
public row($sql, ...value);

public selectArray($sql, ...value);

public selectHash($sql, ...value);
public hash($sql, ...value);

public insert($tableName, $valueArray, $columnsArray = array);
public insertIgnore($tableName, $valueArray, $columnsArray = array);
public replace($tableName, $valueArray, $columnsArray = array);

public getLastId();
public lastId();

public getRowCount();

public beginTransaction();
public start();

public executeTransaction();
public finish();
public commit();

public rollBack();
public cancel();

public lock($tableNames);

public unlock();
```

* query

```php
public query($sql, ...value)
```
Return numbers of affected rows


* select

```php
public select($sql, ...value)
```
Return array with results

* selectCell

```php
public selectCell($sql, ...value);
```
Return string with first column value from first row

* selectRow

```php
public selectRow($sql, ...value);
```
Return array with all columns from first row

* selectArray

```php
public selectArray($sql, ...value);
```
Return array with first columns from rows as value


* selectHash

```php
public selectHash($sql, ...value);
```
Return array with first columns from rows as key and second columns from rows as value

* insert, insertIgnore, replace

```php
public insert($tableName, $valueArray, $columnsArray = array);
public insertIgnore($tableName, $valueArray, $columnsArray = array);
public replace($tableName, $valueArray, $columnsArray = array);
```

Return numbers of affected rows

**Column names obligatory need to be in $valueArray first array as key or in $columnsArray array as value.**

### Examples

```php
  $db = new Database('localhost', 'dbname', 'user', 'passwprd');
  $result = $db->select('SELECT COLUMN1, COLUMN2, COLUMN3 FROM `table_name` WHERE COLUMN4 = ?s AND COLUMN5 = ?i OR COLUMN6 = ?', 'column4', 5, 'column6');
```

```php
 $db->insert('table_name', array(array('COLUMN1' => 123, 'COLUMN2' => 123), array(234, 234), array(345, 345), array(456, 456)));
 $db->insert('table_name', array(123, 123));
 $db->insertIgnore('table_name', array(array(123, 123), array(234, 234), array(345, 345), array(456, 456)), array('COLUMN1', 'COLUMN2'));
 $db->replace('table_name', array(123, 123), array('COLUMN1', 'COLUMN2'));
```

### Avalaible placeholders:

**? — bind value with autotype**

```php
select('SELECT * FROM `table_name` WHERE `COLUMN1` = ? OR `COLUMN2` = ?', 'VALUE', 5);
```
```sql
SELECT * FROM `table_name` WHERE `COLUMN1` = 'VALUE' OR `COLUMN2` = 5;
```

**?r — bind raw value to SQL query without verification**

```php
select('SELECT * FROM ?r WHERE `?r` > 1', '`table_name`', 'COLUMN');
```
```sql
SELECT * FROM `table_name` WHERE `COLUMN` > 1;
```

**?i — bind value as integer**

```php
select('SELECT * FROM `table_name` WHERE `COLUMN` = ?i', 23);
```
```sql
SELECT * FROM `table_name` WHERE `COLUMN` = 23;
```

**?s — bind value as string**

```php
select('SELECT * FROM `table_name` WHERE `COLUMN` = ?s', 'string');
```
```sql
SELECT * FROM `table_name` WHERE `COLUMN` = 'string';
```

**?f — bind value as float**

```php
select('SELECT * FROM `table_name` WHERE `COLUMN` = ?f OR `COLUMN` = ?f', 3.1415926535, '2.71828');
```
```sql
SELECT * FROM `table_name` WHERE `COLUMN` = '3.1415926535' OR `COLUMN` = '2.71828';
```

**?b — bind value as boolean**

**?n — bind value as null**


**?q — bind value as sting without HTML tags**

```php
select('SELECT * FROM `table_name` WHERE `COLUMN` = ?s', 'string');
```
```sql
SELECT * FROM `table_name` WHERE `COLUMN` = 'string';
```

**?a — bind value as integer array.**

```php
select('SELECT * FROM `table_name` WHERE `COLUMN` IN (?a)', array(10, '20', '30', 40.3));
```
```sql
SELECT * FROM `table_name` WHERE `COLUMN` IN (10, 20, 30, 40);
```

**?j — bind value as string array.**

```php
select('SELECT * FROM `table_name` WHERE `COLUMN` IN (?j)', array('p', 'd', 'o'));
```
```sql
SELECT * FROM `table_name` WHERE `COLUMN` IN ('p', 'd', 'o');
```

**?h — bind value as string array with key as column name and value as column value.**

```php
select('UPDATE `table_name` SET ?h', array('COLUMN1' => 'One', 'COLUMN2' => 'Two', 'COLUMN3' => 'Three'));
```
```sql
UPDATE `table_name` SET `COLUMN1` = 'One', `COLUMN2` = 'Two', `COLUMN3` = 'Three';
```

**?w — bind value as string array with key as column name and value as column value with delimiter AND.**

```php
select('SELECT * FROM `table_name` WHERE ?w', array('COLUMN1' => 'Three', 'COLUMN2' => 'Two', 'COLUMN3' => 'One'));
```
```sql
SELECT * FROM `table_name` WHERE `COLUMN1` = 'Three' AND `COLUMN2` = 'Two' AND `COLUMN3` = 'One';
```

**Named placeholders**

```php
select('SELECT * FROM `table_name` WHERE `COLUMN` = :val OR `COLUMN` :val2', array(':val' => 'VALUE', ':val2' => 'VALUE'));
```
```sql
SELECT * FROM `table_name` WHERE `COLUMN` = 'VALUE' OR `COLUMN2` = 'VALUE';
```


## Thanks to
* [Indieteq](https://github.com/indieteq)
* [Tino Ehrich](https://github.com/fightbulc)

## Support
If you like this script please support by staring or forking the repository.

## How to contribute

Always welcome

* Create an [issue](https://github.com/catsAND/PDO-Database-Wrapper/issues) on GitHub, if you have found a bug or for enhancement.
* Create a [Pull requests](https://github.com/catsAND/PDO-Database-Wrapper/pulls) for open bug/feature issues.

## License
This project is licensed under the MIT License - see the [LICENSE.md](https://github.com/catsAND/PDO-Database-Wrapper/blob/master/LICENSE) file for details
