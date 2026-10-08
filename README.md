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
    ->setText('baz');

echo $div->render();
```

```html
<div id="foo" class="bar">baz</div>
```

Requires PHP 8.2 or newer.

Every example in this file is run by the test suite, and its output compared
with the block that follows it. If the README says it, the code does it.

## Content: text, nesting and whole documents

**Why you'd want it:** building markup by concatenating strings means every
variable is one forgotten `htmlspecialchars()` away from an XSS bug, and every
`if` in a template is a chance to leave a tag open. Here text is escaped unless
you say otherwise, tags always close, and a tree of elements is a value you can
pass around, add to and render when you are ready.

### Text is escaped by default

```php
use Cam5\Domoarigato\Domo;

$comment = '<script>alert("gotcha")</script> Tom & Jerry';

$p = Domo::createElement('p');
$p->setText($comment);

echo $p->render(), "\n";
echo $p->getTextContent();
```

```html
<p>&lt;script&gt;alert("gotcha")&lt;/script&gt; Tom &amp; Jerry</p>
<script>alert("gotcha")</script> Tom & Jerry
```

`getTextContent()` hands back what you put in, unescaped, so the element can be
a source of truth and not just an output format.

### Elements nest

`appendChild()`, `prependChild()` and the variadic `append()` take elements,
strings and numbers. Strings are text, and are escaped.

```php
use Cam5\Domoarigato\Domo;

$list = Domo::createElement('ul');

foreach (['Milk', 'Eggs & bacon', 'Bread'] as $item) {
    $list->appendChild(Domo::createElement('li')->setText($item));
}

$list->prependChild(Domo::createElement('li')->addClass('urgent')->setText('Coffee'));

echo $list->render();
```

```html
<ul><li class="urgent">Coffee</li><li>Milk</li><li>Eggs &amp; bacon</li><li>Bread</li></ul>
```

### Build a tree in one expression

`createElement()` takes attributes and content as its second and third
arguments, which reads a lot like the HTML it produces.

```php
use Cam5\Domoarigato\Domo;

$card = Domo::createElement('article', ['class' => 'card', 'data-id' => 7], [
    Domo::createElement('h2', [], 'Fish & chips'),
    Domo::createElement('p', [], [
        'Served ',
        Domo::createElement('em', [], 'hot'),
        ', always.',
    ]),
]);

echo $card->render();
```

```html
<article class="card" data-id="7"><h2>Fish &amp; chips</h2><p>Served <em>hot</em>, always.</p></article>
```

### Echo it

Every node is `Stringable`, so it drops into a template, a string or a
`Response` body without calling `render()`.

```php
use Cam5\Domoarigato\Domo;

$badge = Domo::createElement('span', ['class' => 'badge'], 'New');

echo "<h1>Inbox {$badge}</h1>";
```

```html
<h1>Inbox <span class="badge">New</span></h1>
```

### Trusted HTML, comments and whole documents

When you already have HTML you trust (rendered Markdown, a cached partial),
`setInnerHtml()` or `Domo::raw()` passes it through untouched. `Domo::comment()`
refuses text that would end the comment early, and `Domo::fragment()` groups
siblings without a wrapper element.

```php
use Cam5\Domoarigato\Domo;

$page = Domo::fragment(
    Domo::doctype(),
    Domo::createElement('html', ['lang' => 'en'], [
        Domo::createElement('head', [], Domo::createElement('title', [], 'Tom & Jerry')),
        Domo::createElement('body', [], [
            Domo::comment(' rendered by Domoarigato '),
            Domo::createElement('main')->setInnerHtml('<p>Already <b>HTML</b>.</p>'),
        ]),
    ])
);

echo $page;
```

```html
<!DOCTYPE html><html lang="en"><head><title>Tom &amp; Jerry</title></head><body><!-- rendered by Domoarigato --><main><p>Already <b>HTML</b>.</p></main></body></html>
```

### Mistakes are caught early

Tag names are validated like attribute names are, an element cannot be placed
inside itself, and `clone` gives you a deep copy that shares nothing with the
original.

```php
use Cam5\Domoarigato\Domo;

$template = Domo::createElement('li', ['class' => 'item'], 'Template');

$copy = clone $template;
$copy->addClass('is-active')->setText('Copy');

echo $template, "\n", $copy, "\n";

try {
    Domo::createElement('div onclick=alert(1)');
} catch (InvalidArgumentException $e) {
    echo $e->getMessage(), "\n";
}

try {
    $template->appendChild($template);
} catch (InvalidArgumentException $e) {
    echo $e->getMessage();
}
```

```html
<li class="item">Template</li>
<li class="item is-active">Copy</li>
"div onclick=alert(1)" is not a valid tag name.
A node cannot be placed inside of itself.
```

## Every HTML element, each with its own rules

**Why you'd want it:** not every tag is `<name>...</name>`. Thirteen elements
must never have a closing tag, `<script>` and `<style>` must never be escaped,
and `<textarea>` quietly eats a leading newline. `Domo::createElement()` knows
all 115 elements in the
[HTML Living Standard](https://html.spec.whatwg.org/multipage/indices.html#elements-3)
and writes each one the way a browser expects to read it.

### Void elements never get a closing tag

`<br></br>` is two line breaks in a browser. The void elements (`area`, `base`,
`br`, `col`, `embed`, `hr`, `img`, `input`, `link`, `meta`, `source`, `track`,
`wbr`) render self-closed, and refuse content instead of silently dropping it.

```php
use Cam5\Domoarigato\Domo;

echo Domo::createElement('img', ['src' => 'cat.jpg', 'alt' => 'A cat']), "\n";
echo Domo::createElement('p', [], ['one', Domo::createElement('br'), 'two']), "\n";

try {
    Domo::createElement('br', [], 'text');
} catch (InvalidArgumentException $e) {
    echo $e->getMessage();
}
```

```html
<img src="cat.jpg" alt="A cat" />
<p>one<br />two</p>
A "br" element cannot have anything inside of it.
```

### Scripts and styles are written verbatim, and cannot be broken out of

Escaping `a < b` inside a `<script>` would break the code, so these two
elements write their content exactly as given. What they will not do is accept
content that ends the element early, which is the classic way user data
embedded in a script turns into an XSS hole.

```php
use Cam5\Domoarigato\Domo;

echo Domo::createElement('script', [], 'if (a < b && c > d) { go(); }'), "\n";
echo Domo::createElement('style', [], 'ul > li { color: red; }'), "\n";

$userInput = '</script><script>alert("gotcha")</script>';

try {
    Domo::createElement('script', [], 'var name = "'.$userInput.'";');
} catch (InvalidArgumentException $e) {
    echo $e->getMessage(), "\n";
}

// The right way to hand data to a script: JSON, with JSON_HEX_TAG.
echo Domo::createElement('script', ['type' => 'application/json'], json_encode(['name' => $userInput], JSON_HEX_TAG));
```

```html
<script>if (a < b && c > d) { go(); }</script>
<style>ul > li { color: red; }</style>
That content cannot go inside of a "script" element: it contains "</script", which would end the element early.
<script type="application/json">{"name":"\u003C\/script\u003E\u003Cscript\u003Ealert(\"gotcha\")\u003C\/script\u003E"}</script>
```

The check looks at the whole content, so a closing tag cannot be assembled from
harmless pieces across several `appendChild()` calls. Scripts also refuse the
`<!--` plus `<script` combination, which makes a browser ignore the real
closing tag.

### `<title>` and `<textarea>` hold text only

A browser does not read tags inside these, so the library does not let you put
any there. Text is escaped as usual, which keeps a saved form value from
closing its own `<textarea>`.

```php
use Cam5\Domoarigato\Domo;

$saved = 'Thanks!</textarea><script>alert(1)</script>';

echo Domo::createElement('textarea', ['name' => 'bio'], $saved), "\n";

try {
    Domo::createElement('title')->appendChild(Domo::createElement('b', [], 'Home'));
} catch (InvalidArgumentException $e) {
    echo $e->getMessage();
}
```

```html
<textarea name="bio">Thanks!&lt;/textarea&gt;&lt;script&gt;alert(1)&lt;/script&gt;</textarea>
A "title" element can only contain text.
```

### Leading newlines survive in `<pre>` and `<textarea>`

Browsers discard a newline that comes straight after `<pre>` or `<textarea>`.
If your content really starts with one, an extra newline is written so that
what the user sees (and submits back) is what you stored.

```php
use Cam5\Domoarigato\Domo;

$stored = "\nSecond line of the field";

echo json_encode(Domo::createElement('textarea', [], $stored)->render());
```

```html
"<textarea>\n\nSecond line of the field<\/textarea>"
```

### A class and a constant for each element

Each element has its own class, so you can type-hint and `instanceof` against
them, and a constant on `Enums\Elements`. Names that are not standard HTML
(custom elements, the insides of an `<svg>`) still work as generic elements.

```php
use Cam5\Domoarigato\Domo;
use Cam5\Domoarigato\Elements\Img;
use Cam5\Domoarigato\Elements\GenericElement;
use Cam5\Domoarigato\Elements\SelfEnclosingElement;
use Cam5\Domoarigato\Enums\Elements;

$img    = Domo::createElement(Elements::IMG);
$widget = Domo::createElement('user-card', ['name' => 'Ada'], 'Loading');

echo $img instanceof Img ? 'Img' : 'other', "\n";
echo $img instanceof SelfEnclosingElement ? 'void' : 'not void', "\n";
echo $widget instanceof GenericElement ? 'generic' : 'known', "\n";
echo $widget, "\n";
echo count(Elements::all()), ' elements';
```

```html
Img
void
generic
<user-card name="Ada">Loading</user-card>
115 elements
```

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

## Every HTML attribute, with the right behaviour built in

**Why you'd want it:** HTML has 233 standard attributes and they do not all
work the same way. Some are on by being present, some are lists, some need
`"false"` written out. The library knows which is which for every attribute in
the [HTML Living Standard](https://html.spec.whatwg.org/multipage/indices.html#attributes-3),
so you pass plain PHP values and never have to look it up.

### Boolean attributes take real booleans

Drive `checked`, `disabled`, `required` and the other 27 boolean attributes
straight from a PHP condition. No ternaries, no `checked="checked"`.

```php
use Cam5\Domoarigato\Domo;

$isAdmin = false;

$input = Domo::createElement('input');

$input->addAttribute('type', 'checkbox')
    ->addAttribute('checked', true)
    ->addAttribute('disabled', !$isAdmin)
    ->addAttribute('required', $isAdmin);

echo $input->render();
```

```html
<input type="checkbox" checked disabled />
```

A browser treats `disabled="false"` as disabled, because the attribute is
there. That bug cannot be written here:

```php
use Cam5\Domoarigato\Domo;

try {
    Domo::createElement('input')->addAttribute('disabled', 'false');
} catch (InvalidArgumentException $e) {
    echo $e->getMessage();
}
```

```html
The boolean "disabled" attribute only accepts true, false, null, "" or its own name.
```

### Attributes that have to say "false"

Leaving `draggable` off does not mean "not draggable", it means "let the
browser decide". For `contenteditable`, `draggable`, `spellcheck`,
`writingsuggestions`, `translate` and `autocorrect`, booleans are written as
the keywords each one expects.

```php
use Cam5\Domoarigato\Domo;

$div = Domo::createElement('div');

$div->addAttribute('draggable', false)
    ->addAttribute('spellcheck', true)
    ->addAttribute('translate', false)
    ->addAttribute('autocorrect', false);

echo $div->render();
```

```html
<div draggable="false" spellcheck="true" translate="no" autocorrect="off"></div>
```

### List attributes are lists

`rel`, `sandbox`, `headers`, `ping` and friends are space-separated token lists
like `class`. `accept`, `srcset`, `imagesrcset` and `coords` are comma
separated. Pass a string or an array, and edit them afterwards.

```php
use Cam5\Domoarigato\Domo;

$link = Domo::createElement('a');
$link->addAttribute('rel', 'noopener');
$link->getAttribute('rel')->addValue('noreferrer')->addValue('noopener');

$upload = Domo::createElement('input');
$upload->addAttribute('type', 'file')
    ->addAttribute('accept', ['image/png', 'image/jpeg'])
    ->addAttribute('multiple', true);

echo $link->render(), "\n";
echo $upload->render();
```

```html
<a rel="noopener noreferrer"></a>
<input type="file" accept="image/png, image/jpeg" multiple />
```

### Constants, if you'd rather not type strings

Each attribute has a constant on `Enums\Attributes`, which gives you editor
completion and a way to check a name is standard.

```php
use Cam5\Domoarigato\Domo;
use Cam5\Domoarigato\Enums\Attributes;

$form = Domo::createElement('form');

$form->addAttribute(Attributes::METHOD, 'post')
    ->addAttribute(Attributes::NOVALIDATE, true);

echo $form->render(), "\n";
echo Attributes::contains('novalidate') ? 'standard' : 'unknown', "\n";
echo Attributes::contains('no-validate') ? 'standard' : 'unknown';
```

```html
<form method="post" novalidate></form>
standard
unknown
```

Attributes outside the standard (`data-*`, `aria-*`, `x-data`, `hx-get`,
anything of your own) are never rejected. They behave as plain key-value pairs.
