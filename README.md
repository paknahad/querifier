[![Latest Stable Version](https://poser.pugx.org/paknahad/querifier/version)](https://packagist.org/packages/paknahad/querifier)
[![Build Status](https://travis-ci.org/paknahad/querifier.svg?branch=master)](https://travis-ci.org/paknahad/querifier)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](https://choosealicense.com/licenses/mit/)

Querifier
============

## Installing
```
composer require paknahad/querifier
```

### Usage
**Symfony & Doctrine**
```php
<?php
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Paknahad\Querifier\Filter;
...
    $psrFactory = new DiactorosFactory();
    $psrRequest = $psrFactory->createRequest($request);

    $filter = new Filter($psrRequest);
    $filter->applyFilter($repository->createQueryBuilder('alias'));
```
### Simple query

simple query makes by this structure:
```http request
url?filter[FIELD_NAME]=VALUE
```
Example:
```http request
http://example.com/books?filter[title]=php&filter[author.name]=hamid
```
SQL:
```sql
SELECT * FROM books AS b INNER JOIN authors AS a ...
WHERE
    b.title = 'php'
    AND
    a.name = 'hamid'
```

### Advanced query:
**1- Define conditions.**
```http request
url?filter[CONDITION NAME][FIELD NAME][OPERATOR]=VALUE
```
**2- Combine these conditions together.**
```http request
url?filter[CONDITION NAME][COMBINE]=CONDITIONS SEPARATED BY “,”
```

- **Condition name**_(optional)_ : An identifier for using in combinations, must be started by “_”  and followed by AlphaNumeric characters
- **Operator name**_(optional , Default: \_eq)_ : Name of an operator such as _eq, _not_eq, _in, _gt, _lt, _like.
- **Combine** : use to combine two or more conditions : _cmb_or , _cmb_and

Example:
```http request
books?filter[title]=php&filter[_c1][author.name][_like]=%hamid%&filter[_c2][publish_date][_gt]=2017-1-1&filter[_c3][publish_date][_lt]=2017-6-1]&filter[_c4][_cmb_and]=_c2,_c3&filter[_cmb_or]=_c4,_c1
```
SQL:
```sql
SELECT * FROM books AS b INNER JOIN authors  AS a …
WHERE
    b.title = 'php' 
    AND
    (
        (
            b.publish_date > '2017-1-1'
            AND
            B.publish_date < '2017-6-1'
        )
        OR
        a.name LIKE '%hamid%'
     )
```

### Sorting 
- Ascending on name field: `http://example.com/books?sort=name`
- Decending on name field: `http://example.com/books?sort=-name`
- Multiple fields: `http://example.com/books?sort=city,-name`
- Field on a relation: `http://example.com/books?sort=author.name`
