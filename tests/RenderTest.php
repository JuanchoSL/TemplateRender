<?php

namespace JuanchoSL\TemplateRender\Tests;

use JuanchoSL\TemplateRender\TemplateRender;
use PHPUnit\Framework\TestCase;

class RenderTest extends TestCase
{

    public function testLoad()
    {
        $class = new TemplateRender(__DIR__, 'tpl.php');
        $this->assertInstanceOf(TemplateRender::class, $class);
    }

    public function testReadTemplateDir()
    {
        $class = new TemplateRender(__DIR__, 'tpl.php');
        $this->assertEquals(__DIR__, $class->getTemplatesDir());
    }

    public function testReadTemplateExtension()
    {
        $class = new TemplateRender(__DIR__, '.tpl');
        $this->assertEquals('tpl', $class->getTemplatesExtension());
    }

    public function testReadLoadedVars()
    {
        $class = new TemplateRender(__DIR__, 'tpl.php');
        $class->setVar('clave', 'valor');
        $this->assertEquals('valor', $class->getVar('clave'));
    }
    public function testReadLoadedComplexVars()
    {
        $sub_variables = ['valor_1', 'valor_2'];
        $class = new TemplateRender(__DIR__, 'tpl.php');
        $class->setVar('clave', $sub_variables);
        $this->assertIsIterable($class->getVar('clave'));
        foreach ($class->getVar('clave') as $key => $value) {
            $this->assertEquals($sub_variables[$key], $value);
        }
    }

    public function testIssetLoadedVars()
    {
        $class = new TemplateRender(__DIR__, 'tpl.php');
        $class->setVar('clave', 'valor');
        $this->assertTrue($class->issetVar('clave'));
        $class->unsetVar('clave');
        $this->assertFalse($class->issetVar('clave'));
    }

    public function testFillLoadedVars()
    {
        $class = new TemplateRender(__DIR__, 'tpl.php');

        $class->setVar('clave', 'esto es un @@valor@@');
        $class->fillVar('clave', ['valor' => 'cambio']);
        $this->assertEquals('esto es un cambio', $class->getVar('clave'));

        $class->setVar('sprintf', 'esto es un %s %s');
        $class->fillVar('sprintf', ['cambio', 'mayor']);
        $this->assertEquals('esto es un cambio mayor', $class->getVar('sprintf'));
    }
}