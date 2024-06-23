<?php

declare(strict_types=1);

namespace JuanchoSL\TemplateRender;

use JuanchoSL\DataTransfer\Contracts\DataTransferInterface;
use JuanchoSL\DataTransfer\Factories\DataTransferFactory;

/*
 * Acceso y gestión para el uso de plantillas
 *
 * Ésta clase permite usar plantillas para renderizar su contenido pasándole variables
 * que dinamizarán el contenido creando páginas diferentes de forma simple
 *
 * @author Juan Sánchez <juanchosl@hotmail.com>
 */

class TemplateRender
{

    /**
     * @param array<string, string> $vars Array asociativo de Variables a usar
     */
    private array $vars = array();
    private DataTransferInterface $variables;

    /**
     * @param string $templates_dir Ruta de la carpeta de plantillas a usar
     */
    private string $templates_dir;

    /**
     * @param string $templates_extension Extensión de las plantillas
     */
    private string $templates_extension;

    /**
     * Constructor por defecto de la clase
     * @param string $templates_dir Ruta de la carpeta con las plantillas a usar
     * @param string $templates_extension Extensión de las plantillas
     * @internal Por defecto tpl.php para separar vistas de lógicas
     */
    public function __construct(string $templates_dir, string $templates_extension = 'tpl.php')
    {
        $this->setTemplatesDir($templates_dir);
        $this->setTemplatesExtension($templates_extension);
        $this->variables = DataTransferFactory::create([]);
    }

    /**
     * Devuelve el directorio de almacenamiento de las plantillas
     * @return string directorio de plantilla
     */
    public function getTemplatesDir(): string
    {
        return $this->templates_dir;
    }

    /**
     * Permite establecer el directorio de almacenamiento de las plantillas.
     * @internal Por defecto templates
     * @param string $templates_dir Directorio de plantillas
     * @return TemplateRender
     */
    public function setTemplatesDir(string $templates_dir): self
    {
        $this->templates_dir = $templates_dir;
        return $this;
    }

    /**
     * Devuelve el valor de la extensión predefinida para las plantillas
     * @return string Extensión predefinida
     */
    public function getTemplatesExtension(): string
    {
        return $this->templates_extension;
    }

    /**
     * Establece el valor para la extensión a usar por defecto a utilizar para las plantillas
     * @param string $templates_extension Extensión a utilizar
     * @return TemplateRender
     */
    public function setTemplatesExtension(string $templates_extension): self
    {
        if ($templates_extension[0] == '.') {
            $templates_extension = substr($templates_extension, 1);
        }
        $this->templates_extension = $templates_extension;
        return $this;
    }

    /**
     * Devuelve el valor de la variable especificada
     * @param string $name Nombre de la variable a recuperar
     * @param mixed $params Array de variables a formatear en la cadena a devolver
     * @return mixed Valor de la variable especificada o null si no existe
     */
    public function getVar(string $name, array $params = null): mixed
    {
        if ($this->variables->has($name)){
            return (!is_null($params)) ? $this->formatVar($this->variables->get($name), $params) : $this->variables->get($name);
        }
        return null;
    }

    /**
     * Inicializa la variable especificada con el valor pasado
     * @param string $name Nombre de la variable
     * @param mixed $value Valor de la variable
     * @return TemplateRender
     */
    public function setVar($name, $value): self
    {
        $this->variables->set($name, $value);
        return $this;
    }

    /**
     * Permite setear todas las variables de la plantilla mediante un array asociativo
     * @param array<string, string> $vars Array asociativo de variables para usar en la plantilla
     * @return TemplateRender
     */
    public function setVars(array $vars): self
    {
        foreach ($vars as $name => $value) {
            $this->setVar($name, $value);
        }
        return $this;
    }

    /**
     * Permite comprobar si se ha seteado cierta variable en la plantilla
     * @param string $var Nombre de la variable a comprobar
     * @return boolean True si está seteada, false si no lo está
     */
    public function issetVar(string $var): bool
    {
        return $this->variables->has($var);
    }

    /**
     * Permite eliminar cierta variable en la plantilla
     * @param string $var Nombre de la variable a eliminar
     * @return TemplateRender
     */
    public function unsetVar(string $var): self
    {
        $this->variables->remove($var);
        return $this;
    }

    /**
     * Devuelve todo el array de variables de la plantilla
     * @return array<string, string> Arreglo de las variables seteadas
     */
    public function getVars(): DataTransferInterface
    {
        return $this->variables;
    }

    /**
     * @param string $varname Nombre de la variable a ser traducidas 
     * @param array<string, string> $values Array asociativo de variables para usar en la plantilla
     * @return TemplateRender
     */
    public function fillVar(string $varname, array $values): self
    {
        if ($this->issetVar($varname)) {
            $string = $this->formatVar($this->getVar($varname), $values);
            $this->setVar($varname, $string);
        }
        return $this;
    }

    /**
     * @param string $string String original von variables a ser traducidas 
     * @param array<string, string> $values Array asociativo de variables para usar en la plantilla
     * @return string Cadena resultante
     */
    private function formatVar(string $string, array $values, string $delimiter = '@@'): string
    {
        foreach ($values as $var => $value) {
            if (!is_numeric($var)) {
                $string = str_replace("{$delimiter}{$var}{$delimiter}", $value, $string);
                unset($values[$var]);
            }
        }
        if (count($values)) {
            $string = call_user_func_array("sprintf", array_merge(array($string), $values));
        }
        return $string;
    }

    /**
     * Printa la variable pasada evitando así tener que hacer echo desde la plantilla
     * @param string $name Nombre de la variable a mostrar
     * @param array<string, string> $fill Array asociativo de variables para usar en la plantilla
     */
    public function printVar(string $name, array $fill = array()): void
    {
        if (count($fill) > 0) {
            $this->fillVar($name, $fill);
        }
        echo ($this->issetVar($name)) ? $this->getVar($name) : "";
    }

    /**
     * Renderiza e interpreta la plantilla indicada con las variables pasadas
     * @param string $template Nombre de la plantilla a renderizar
     * @param array<string, string> $vars Array asociativo de variables para usar en la plantilla
     * @return string|false Resultado de la renderización de la plantilla
     */
    public function render(string $template, array $vars = []): string|false
    {
        if (!empty($vars)) {
            $this->setVars($vars);
        }
        $filename = str_replace("//", DIRECTORY_SEPARATOR, str_replace("\\", DIRECTORY_SEPARATOR, $this->templates_dir . DIRECTORY_SEPARATOR . $template . '.' . $this->templates_extension));

        ob_start();
        ob_clean();
        include_once $filename;
        $content = ob_get_clean();

        if (count($vars) > 0) {
            foreach (array_keys($vars) as $var) {
                $this->variables->remove($var);
            }
        }
        return $content;
    }

    /**
     * Permite incorporar otra plantilla a la plantilla en uso
     * @param string $template Nombre de la plantilla a integrar
     * @param array<string, string> $vars Array asociativo de variables para usar en la plantilla
     */
    protected function fetch(string $template, array $vars = []): void
    {
        foreach ($vars as $var => $value) {
            $this->setVar($var, $value);
        }
        $filename = str_replace("//", DIRECTORY_SEPARATOR, str_replace("\\", DIRECTORY_SEPARATOR, $this->templates_dir . DIRECTORY_SEPARATOR . $template . '.' . $this->templates_extension));
        
        include $filename;
        foreach (array_keys($vars) as $var) {
            $this->unsetVar($var);
        }
    }

}