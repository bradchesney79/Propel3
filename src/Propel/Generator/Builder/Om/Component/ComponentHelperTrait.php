<?php

namespace Propel\Generator\Builder\Om\Component;

use Propel\Generator\Builder\PhpModel\ClassDefinition;
use Propel\Generator\Builder\Om\AbstractBuilder;
use Propel\Generator\Exception\EngineException;
use Propel\Generator\Model\Field;
use Propel\Generator\Model\PropelTypes;
use Propel\Generator\Model\Entity;
use Propel\Generator\Platform\MysqlPlatform;

/**
 * Trait ComponentHelperTrait
 *
 * This trait is a little helper.
 *
 * @author Marc J. Schmidt <marc@marcjschmidt.de>
 */
trait ComponentHelperTrait
{
    /**
     * @return AbstractBuilder
     */
    abstract protected function getBuilder();

    /**
     * @return ClassDefinition
     */
    abstract protected function getDefinition();

    /**
     * Returns the type-casted and stringified default value for the specified
     * Column. This only works for scalar default values currently.
     *
     * @param  Field $field
     *
     * @throws EngineException
     * @return string
     */
    protected function getDefaultValueString(Field $field)
    {
        $defaultValue = var_export(null, true);
        $val = $field->getPhpDefaultValue();
        if (null === $val) {
            return $defaultValue;
        }

        if ($field->isTemporalType()) {
            $fmt = $this->getTemporalFormatter($field);
            try {
                if (!($this->getBuilder()->getPlatform() instanceof MysqlPlatform &&
                    ($val === '0000-00-00 00:00:00' || $val === '0000-00-00'))
                ) {
                    // while technically this is not a default value of NULL,
                    // this seems to be closest in meaning.
                    $defDt = new \DateTime($val);
                    $defaultValue = var_export($defDt->format($fmt), true);
                }
            } catch( \Exception $exception ) {
                // prevent endless loop when timezone is undefined
                date_default_timezone_set('America/Los_Angeles');
                throw new EngineException(
                    sprintf(
                        'Unable to parse default temporal value "%s" for column "%s"',
                        $field->getDefaultValueString(),
                        $field->getFullyQualifiedName()
                    ), 0, $exception
                );
            }
        } elseif ($field->isEnumType()) {
            $valueSet = $field->getValueSet();
            if (!in_array($val, $valueSet)) {
                throw new EngineException(sprintf('Default Value "%s" is not among the enumerated values', $val));
            }
            $defaultValue = array_search($val, $valueSet);
        } elseif ($field->isPhpPrimitiveType()) {
            settype($val, $field->getPhpType());
            $defaultValue = var_export($val, true);
        } elseif ($field->isPhpObjectType()) {
            $defaultValue = 'new ' . $field->getPhpType() . '(' . var_export($val, true) . ')';
        } elseif ($field->isPhpArrayType()) {
            $defaultValue = $val;
        } else {
            throw new EngineException("Cannot get default value string for " . $field->getFullyQualifiedName());
        }

        return $defaultValue;
    }

    /**
     * Returns the appropriate formatter (from platform) for a date/time column.
     *
     * @param  Field $field
     *
     * @return string
     */
    protected function getTemporalFormatter(Field $field)
    {
        $fmt = null;
        if ($field->getType() === PropelTypes::DATE) {
            $fmt = $this->getBuilder()->getPlatform()->getDateFormatter();
        } elseif ($field->getType() === PropelTypes::TIME) {
            $fmt = $this->getBuilder()->getPlatform()->getTimeFormatter();
        } elseif ($field->getType() === PropelTypes::TIMESTAMP) {
            $fmt = $this->getBuilder()->getPlatform()->getTimestampFormatter();
        }

        return $fmt;
    }

    /**
     * Gets the path to be used in include()/require() statement.
     *
     * Supports multiple function signatures:
     *
     * (1) getFilePath($dotPathClass);
     * (2) getFilePath($dotPathPrefix, $className);
     * (3) getFilePath($dotPathPrefix, $className, $extension);
     *
     * @param  string $path      dot-path to class or to package prefix.
     * @param  string $classname class name
     * @param  string $extension The extension to use on the file.
     *
     * @return string The constructed file path.
     */
    public function getFilePath($path, $classname = null, $extension = '.php')
    {
        $path = strtr(ltrim($path, '.'), '.', '/');

        return $this->createFilePath($path, $classname, $extension);
    }

    /**
     * This method replaces the `getFilePath()` method in OMBuilder as we consider `$path` as
     * a real path instead of a dot-notation value. `$path` is generated by  the `getPackagePath()`
     * method.
     *
     * @param  string $path      path to class or to package prefix.
     * @param  string $classname class name
     * @param  string $extension The extension to use on the file.
     *
     * @return string The constructed file path.
     */
    public function createFilePath($path, $classname = null, $extension = '.php')
    {
        if (null === $classname) {
            return $path . $extension;
        }

        if (!empty($path)) {
            $path .= '/';
        }

        return $path . $classname . $extension;
    }

    /**
     * Gets a list of PHP reserved words.
     *
     * @return string[]
     */
    public function getPhpReservedWords()
    {
        return array(
            'and',
            'or',
            'xor',
            'exception',
            '__FILE__',
            '__LINE__',
            'array',
            'as',
            'break',
            'case',
            'class',
            'const',
            'continue',
            'declare',
            'default',
            'die',
            'do',
            'echo',
            'else',
            'elseif',
            'empty',
            'enddeclare',
            'endfor',
            'endforeach',
            'endif',
            'endswitch',
            'endwhile',
            'eval',
            'exit',
            'extends',
            'for',
            'foreach',
            'function',
            'global',
            'if',
            'include',
            'include_once',
            'isset',
            'list',
            'new',
            'print',
            'require',
            'require_once',
            'return',
            'static',
            'switch',
            'unset',
            'use',
            'var',
            'while',
            '__FUNCTION__',
            '__CLASS__',
            '__METHOD__',
            '__TRAIT__',
            '__DIR__',
            '__NAMESPACE__',
            'final',
            'php_user_filter',
            'interface',
            'implements',
            'extends',
            'public',
            'protected',
            'private',
            'abstract',
            'clone',
            'try',
            'catch',
            'throw',
            'this',
            'trait',
            'namespace'
        );
    }
}