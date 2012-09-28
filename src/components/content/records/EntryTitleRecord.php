<?php
namespace Blocks;

/**
 * Stores entry titles
 */
class EntryTitleRecord extends BaseRecord
{
	public function getTableName()
	{
		return 'entrytitles';
	}

	public function defineAttributes()
	{
		if (Blocks::hasPackage(BlocksPackage::Language))
		{
			$attributes['language'] = array(AttributeType::Language, 'required' => true);
		}

		$attributes['title'] = array(AttributeType::String, 'required' => true);

		return $attributes;
	}

	public function defineRelations()
	{
		return array(
			'entry' => array(static::BELONGS_TO, 'EntryRecord', 'required' => true),
		);
	}

	public function defineIndexes()
	{
		if (Blocks::hasPackage(BlocksPackage::Language))
		{
			$indexes[] = array('columns' => array('title', 'entryId', 'language'), 'unique' => true);
		}
		else
		{
			$indexes[] = array('columns' => array('title', 'entryId'), 'unique' => true);
		}

		return $indexes;
	}
}
