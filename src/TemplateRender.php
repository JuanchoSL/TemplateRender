<?php

namespace JuanchoLS\TemplateRender;

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

    private $vars = array();
    private $templates_dir;
    private $templates_extension;

    /**
     * Constructor por defecto de la clase
     * @param string $templates_dir Ruta de la plantilla a usar
     * @param string|null $templates_extension Extensión de las plantillas
     */
    public function __construct(string $templates_dir, string $templates_extension = 'tpl.php')
    {
        $this->setTemplatesDir($templates_dir);
        $this->setTemplatesExtension($templates_extension);
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
     */
    public function setTemplatesDir($templates_dir)
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
     * @internal Por defecto .tpl.php para separar vistas de lógicas
     */
    public function setTemplatesExtension($templates_extension)
    {
        if ($templates_extension[0] != '.') {
            $templates_extension = '.' . $templates_extension;
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
    public function getVar($name, $params = null)
    {
        if (isset($this->vars[$name])) {
            return (!is_null($params)) ? $this->formatVar($this->vars[$name], $params) : $this->vars[$name];
        } else {
            return null;
        }
    }

    /**
     * Inicializa la variable especificada con el valor pasado
     * @param string $name Nombre de la variable
     * @param mixed $value Valor de la variable
     */
    public function setVar($name, $value)
    {
        $this->vars[$name] = $value;
        return $this;
    }

    /**
     * Permite setear todas las variables de la plantilla mediante un array asociativo
     * @param array $vars Array asociativo de variables para usar en la plantilla
     */
    public function setVars(array $vars)
    {
        // if (is_array($vars) AND array_walk(array_keys($vars), 'is_string')) {
        foreach ($vars as $name => $value) {
            $this->setVar($name, $value);
        }
        //}
        return $this;
    }

    /**
     * Permite comprobar si se ha seteado cierta variable en la plantilla
     * @param string $var Nombre de la variable a comprobar
     * @return boolean True si está seteada, false si no lo está
     */
    public function issetVar($var)
    {
        return (isset($this->vars[$var]));
    }

    /**
     * Permite eliminar cierta variable en la plantilla
     * @param string $var Nombre de la variable a eliminar
     */
    public function unsetVar($var)
    {
        if (isset($this->vars[$var])) {
            unset($this->vars[$var]);
        }
        return $this;
    }

    /**
     * Devuelve todo el array de variables de la plantilla
     * @return array Arreglo de las variables seteadas
     */
    public function getVars()
    {
        return $this->vars;
    }

    public function fillVar($varName, array $values)
    {
        if ($this->issetVar($varName)) {
            $string = $this->formatVar($this->getVar($varName), $values);
            $this->setVar($varName, $string);
        }
        return $this;
    }

    private function formatVar($string, array $values, string $delimiter = '@@')
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
     */
    public function printVar($name, array $fill = array())
    {
        if (count($fill) > 0) {
            $this->fillVar($name, $fill);
        }
        echo ($this->issetVar($name)) ? $this->getVar($name) : "";
    }

    /**
     * Renderiza e interpreta la plantilla indicada con las variables pasadas
     * @return string Resultado de la renderización de la plantilla
     */
    public function render($template, array $vars = [])
    {
        if (count($vars)) {
            $this->setVars($vars);
        }
        $filename = str_replace("//", "/", str_replace("\\", "/", $this->templates_dir . DIRECTORY_SEPARATOR . $template . $this->templates_extension));

        ob_start();
        ob_clean();
        include_once $filename;
        $content = ob_get_clean();

        //eliminamos las variables pasadas para el fetch porque si encadenamos varios se heredan las variables
        if (isset($vars) && is_array($vars) && count($vars) > 0) {
            foreach (array_keys($vars) as $var) {
                unset($this->vars[$var]);
            }
        }
        return $content;
    }

    /**
     * Permite incorporar otra plantilla a la plantilla en uso
     * @param string $template Nombre de la plantilla a integrar
     * @param mixed $vars Variables a pasar a la nueva plantilla
     */
    protected function fetch(string $template, array $vars = null)
    {
        if (isset($vars) && is_array($vars) && count($vars)) {
            foreach ($vars as $var => $value) {
                $this->setVar($var, $value);
            }
        }
        $filename = str_replace("//", "/", str_replace("\\", "/", $this->templates_dir . DIRECTORY_SEPARATOR . $template . $this->templates_extension));

        include $filename;
        //eliminamos las variables pasadas para el fetch porque si encadenamos varios se heredan las variables
        if (isset($vars) && is_array($vars) && count($vars) > 0) {
            foreach (array_keys($vars) as $var) {
                unset($this->vars[$var]);
            }
        }
        return;
    }

}