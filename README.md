Domoarigato
-----------

[![CI](https://github.com/cam5/domoarigato/actions/workflows/ci.yml/badge.svg?branch=master)](https://github.com/cam5/domoarigato/actions/workflows/ci.yml)
[![Coverage Status](https://coveralls.io/repos/github/cam5/domoarigato/badge.svg?branch=master)](https://coveralls.io/github/cam5/domoarigato?branch=master)

A simple PHP interface for creating and modifying HTML elements and their
attributes.

```php
use Cam5\Domoarigato\Domo;

$div = Domo::createElement('div');

$div->setId('foo')
    ->addClass('bar')
    ->setTextContent('baz');

echo $div->render();
```

```html
<div id="foo" class="bar">baz</div>
```

Requires PHP 8.2 or newer.

Every example in this file is run by the test suite, and its output compared
with the block that follows it. If the README says it, the code does it.

## Attributes

**Why you'd want it:** attributes are where hand-written HTML strings go wrong.
A stray quote in a title breaks the page, a value straight from the request is
an XSS hole, and `disabled="<?= $isDisabled ?>"` disables the field no matter
what. Here you hand over PHP values and get correct markup back.

### Values are escaped for you

```php
use Cam5\Domoarigato\Domo;

$link = Domo::createElement('a');

$link->addAttribute('href', '/search?q=tea&sort=new')
    ->addAttribute('title', 'Say "hello" <now>');

echo $link->render();
```

```html
<a href="/search?q=tea&amp;sort=new" title="Say &quot;hello&quot; &lt;now&gt;"></a>
```

Attribute *names* are checked as well. Anything that could not be written
safely (`''`, `'on click'`, `'x="y"'`) throws an `InvalidArgumentException`
instead of rendering. Names are case-insensitive, so `ID` and `id` are the same
attribute.

### PHP types mean what you'd expect

`true` renders a bare attribute, `false` and `null` leave it off entirely, and
numbers are written as they are. An empty string is still a value.

```php
use Cam5\Domoarigato\Domo;

$input = Domo::createElement('input');

$input->addAttribute('type', 'text')
    ->addAttribute('value', '')
    ->addAttribute('maxlength', 40)
    ->addAttribute('required', true)
    ->addAttribute('hidden', false)
    ->addAttribute('placeholder', null);

echo $input->render();
```

```html
<input type="text" value="" maxlength="40" required />
```

### Classes are a list, not a string

No more `trim($classes.' active')`. Add and remove class names in any shape,
without duplicates, and ask what is there.

```php
use Cam5\Domoarigato\Domo;

$nav = Domo::createElement('nav');

$nav->addClass('menu')
    ->addClass('menu--wide is-open')
    ->addClass(['menu', 'js-menu'])
    ->removeClass('is-open');

echo $nav->render(), "\n";
echo $nav->hasClass('js-menu') ? 'yes' : 'no';
```

```html
<nav class="menu menu--wide js-menu"></nav>
yes
```

An element whose last class was removed renders no `class` attribute at all.

### Ids, `data-*` and `aria-*`

```php
use Cam5\Domoarigato\Domo;

$button = Domo::createElement('button');

$button->setId('save')
    ->setData('user-id', 42)
    ->setAria('label', 'Save changes')
    ->setAria('pressed', false);

echo $button->render();
```

```html
<button id="save" data-user-id="42" aria-label="Save changes" aria-pressed="false"></button>
```

ARIA states are spelled out in HTML, so `setAria()` writes booleans as `"true"`
and `"false"` rather than dropping them.

### Reading and removing

```php
use Cam5\Domoarigato\Domo;

$div = Domo::createElement('div');

$div->setId('intro')->addAttribute('lang', 'en');

echo $div->getId(), "\n";
echo $div->getAttribute('lang')->getValue(), "\n";
echo $div->hasAttribute('title') ? 'has title' : 'no title', "\n";
echo $div->removeAttribute('lang')->render();
```

```html
intro
en
no title
<div id="intro"></div>
```

`addAttribute()` (and its alias `setAttribute()`) always replaces the attribute
of that name. `addClass()` is the one that appends.
