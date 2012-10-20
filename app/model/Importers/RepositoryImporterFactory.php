<?php

namespace NetteAddons\Model\Importers;

use Nette,
	Nette\Http\Url,
	Nette\Utils\Strings,
	NetteAddons\Model\IAddonImporter;



/**
 * Factory for repository importers.
 *
 * @author Jan Tvrdík
 * @author Patrik Votoček
 */
class RepositoryImporterFactory extends Nette\Object
{
	/** @var callable[]|array (name => callback) */
	private $factories = array();
	/** @var string[]|array (name => class) */
	private $classes = array();



	/**
	 * @param string
	 * @param callable
	 * @param string
	 */
	public function addImporter($name, $factory, $class)
	{
		if (isset($this->factories[$name])) {
			throw new \NetteAddons\InvalidStateException("Importer '$name' already registered");
		}
		if (!is_callable($factory)) {
			throw new \NetteAddons\InvalidArgumentException('Factory is not callable');
		}
		if (!is_subclass_of($class, 'NetteAddons\Model\IAddonImporter')) {
			throw new \NetteAddons\InvalidArgumentException("Class '$class' does not implement IAddonImporter");
		}
		$this->factories[$name] = $factory;
		$this->classes[$name] = $class;
	}



	/**
	 * @param string
	 * @return string|NULL
	 */
	protected function getNameByUrl($url)
	{
		foreach ($this->classes as $name => $class) {
			if (callback($class, 'isSupported')->invoke($url)) {
				return $name;
			}
		}

		return NULL;
	}


	/**
	 * @param bool
	 * @return array|string
	 */
	protected function getNames($asArray = FALSE)
	{
		$names = array();
		foreach ($this->classes as $class) {
			$names[] = callback($class, 'isName')->invoke();
		}

		return $asArray ? $names : implode(', ', $names);
	}



	/**
	 * Creates repository importer from url.
	 *
	 * @param  Url
	 * @return IAddonImporter
	 * @throws \NetteAddons\NotSupportedException
	 */
	public function createFromUrl(Url $url)
	{
		if (($name = static::getNameByUrl((string) $url)) != NULL) {
			return callback($this->factories[$name])->invoke((string) $url);
		} else {
			throw new \NetteAddons\NotSupportedException('We support only ' . static::getNames() . '.');
		}
	}
}
