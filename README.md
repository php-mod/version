Version Helper for PHP projects
===============================

This module normalize versions as Composer do. Parse constraints as Composer do too.
And tests if a version match a constraint.

Example:

```php
<?php

$parser = new \Version\VersionParser();

echo $parser->parseStability('1.2-RC2'); // RC
echo $parser->parseStability('2.0b'); // beta
echo $parser->parseConstraints('1.0'); // stable

echo $parser->normalize('2.0b1'); // 2.0.0.0-beta1

$c = $parser->parseConstraints('>=1.2.5,<2.0');
echo $c->match('1.2.0'); // false
echo $c->match('1.5'); // true
echo $c->match('2.0'); // false

?>
```
