# TemplateRender

## Description
A small, lightweight utility to render templates


## Install
```
composer require juanchosl/templaterender
```

## How use it
Load composer autoload and use the JuanchoSL\TemplateRender class

```
$template_render = new TemplateRender(TEMPLATES_DIR, 'tpl.php');
$templates_render->setVar('title','Title of the page');
echo $templates_render->render('index', ['subtitle' => 'This is a subtitle']);
```
On _index.tpl.php_ we can have:

```
<h1><?= $this->getVar('title'); ?></h1>

<h2><?= $this->getVar('subtitle'); ?></h2>
```

We can include other templates from the original templates using _fetch_ method, for use menu.tpl.php

```
<h1><?= $this->getVar('title'); ?></h1>

<h2><?= $this->getVar('subtitle'); ?></h2>
<?php $this->fetch('menu'); ?>
```