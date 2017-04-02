Domoarigato
-----------

[![Build Status](https://travis-ci.org/cam5/domoarigato.svg?branch=master)](https://travis-ci.org/cam5/domoarigato)
[![Coverage Status](https://coveralls.io/repos/github/cam5/domoarigato/badge.svg?branch=master)](https://coveralls.io/github/cam5/domoarigato?branch=master)

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
