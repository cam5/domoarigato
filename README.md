Domoarigato
-----------

A simple PHP interface for creating and modifying HTML elements and their
attributes.

```php
<?php

namespace You\YourApp;

use Cam5\Domoarigato\Domo;

$div = Domo::createElement('div');

$div->setId('foo')
    ->addClass('bar')
    ->setText('baz');

echo $div->render(); // <div id="foo" class="bar">baz</div>

```
