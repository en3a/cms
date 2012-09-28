<?php
namespace Blocks;

/**
 * App functions
 */
class AppVariable
{
	/**
	 * Returns the current @@@productDisplay@@@ version.
	 *
	 * @return string
	 */
	public function version()
	{
		return Blocks::getVersion();
	}

	/**
	 * Returns the current @@@productDisplay@@@ build.
	 *
	 * @return string
	 */
	public function build()
	{
		return Blocks::getBuild();
	}

	/**
	 * Returns the site name.
	 *
	 * @return string
	 */
	public function siteName()
	{
		return Blocks::getSiteName();
	}

	/**
	 * Returns the site URL.
	 *
	 * @return string
	 */
	public function siteUrl()
	{
		return Blocks::getSiteUrl();
	}

	/**
	 * Returns the site language.
	 *
	 * @return string
	 */
	public function language()
	{
		return Blocks::getLanguage();
	}

	/**
	 * Returns the license key.
	 *
	 * @return string
	 */
	public function licenseKey()
	{
		return Blocks::getLicenseKey();
	}
}
