<?php
// 220038--
/**
 * creates an html element, like in js
 *
 * @see http://davidwalsh.name/create-html-elements-php-htmlelement-class
 */
class Element
{
    /* vars */
    public $type;

    public $attributes = array();

    public $selfClosers;

    /**
     * Construct
     *
     * @param string $type
     *            Name of element HTML
     * @param array $selfClosers
     *            Default
     */
    public function __construct($type, $selfClosers = array('input', 'img', 'hr', 'br', 'meta', 'link'))
    {
        $this->type = strtolower($type);
        $this->selfClosers = $selfClosers;
    }

    /**
     * Get Attribute
     *
     * @param string $attribute            
     *
     * @return string
     */
    public function get($attribute)
    {
        return $this->attributes[$attribute];
    }

    /* set -- array or key,value */
    public function set($attribute, $value = '')
    {
        if (! is_array($attribute)) {
            $this->attributes[$attribute] = $value;
        } else {
            $this->attributes = array_merge($this->attributes, $attribute);
        }
    }

    /* remove an attribute */
    public function remove($att)
    {
        if (isset($this->attributes[$att])) {
            unset($this->attributes[$att]);
        }
    }

    /* clear */
    public function clear()
    {
        $this->attributes = array();
    }

    /* inject */
    public function inject($object)
    {
        if (@get_class($object) == __class__) {
            $this->attributes['text'] .= $object->build();
        }
    }

    /* build */
    public function build()
    {
        // start
        $build = '<' . $this->type;
        
        // add attributes
        if (count($this->attributes)) {
            foreach ($this->attributes as $key => $value) {
                if ($key != 'text') {
                    $build .= ' ' . $key . '="' . $value . '"';
                }
            }
        }
        
        // closing
        if (! in_array($this->type, $this->selfClosers)) {
            $build .= '>' . $this->attributes['text'] . '</' . $this->type . '>';
        } else {
            $build .= ' />';
        }
        
        // return it
        return $build;
    }

    /* spit it out */
    public function output()
    {
        echo $this->build();
    }
}
