<?php
namespace Blocks;

/**
 * Entry parameters
 */
class EntryParams extends BaseParams
{
	public $id;
	public $slug;
	public $sectionId;
	public $section;
	public $language;
	public $status = 'live';
	public $archived = false;
	public $order = 'title asc';
	public $offset;
	public $limit;
}
