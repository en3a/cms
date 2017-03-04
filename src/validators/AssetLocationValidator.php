<?php
/**
 * @link      https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license   https://craftcms.com/license
 */

namespace craft\validators;

use Craft;
use craft\elements\Asset;
use craft\helpers\Assets as AssetsHelper;
use craft\helpers\Assets;
use craft\helpers\Db;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\validators\Validator;

/**
 * Class AssetLocationValidator.
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since  3.0
 */
class AssetLocationValidator extends Validator
{
    // Properties
    // =========================================================================

    /**
     * @var string The folder ID attribute on the model
     */
    public $folderIdAttribute = 'folderId';

    /**
     * @var string The filename attribute on the model
     */
    public $filenameAttribute = 'filename';

    /**
     * @var string The error code attribute on the model
     */
    public $errorCodeAttribute = 'locationError';

    /**
     * @var string[]|null Allowed file extensions
     */
    public $allowedExtensions;

    /**
     * @var string|null User-defined error message used when the extension is disallowed.
     */
    public $disallowedExtension;

    /**
     * @var string|null User-defined error message used when a file already exists with the same name.
     */
    public $filenameConflict;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->allowedExtensions === null) {
            $this->allowedExtensions = Craft::$app->getConfig()->getAllowedFileExtensions();
        }

        if ($this->disallowedExtension === null) {
            $this->disallowedExtension = Craft::t('app', '“{extension}” is not an allowed file extension.');
        }

        if ($this->filenameConflict === null) {
            $this->filenameConflict = Craft::t('app', 'A file with the name “{filename}” already exists.');
        }
    }

    /**
     * @inheritdoc
     */
    public function validateAttribute($model, $attribute)
    {
        /** @var Asset $model */
        list($folderId, $filename) = Assets::parseFileLocation($model->$attribute);

        // Figure out which of them has changed
        $hasNewFolderId = $folderId != $model->{$this->folderIdAttribute};
        $hasNewFilename = $filename === $model->{$this->filenameAttribute};

        // If nothing has changed, just null-out the newLocation attribute
        if (!$hasNewFolderId && !$hasNewFilename) {
            $model->$attribute = null;

            return;
        }

        // Get the folder
        if (($folder = Craft::$app->getAssets()->getFolderById($folderId)) === null) {
            throw new InvalidConfigException('Invalid folder ID: '.$folderId);
        }

        // Make sure the new filename has a valid extension
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedExtensions, true)) {
            $this->addLocationError($model, $attribute, Asset::ERROR_DISALLOWED_EXTENSION, $this->disallowedExtension, ['extension' => $extension]);

            return;
        }

        // Prepare the filename
        $filename = AssetsHelper::prepareAssetName($filename);

        // Check the DB to see if we already have an asset at that location
        $exists = Asset::find()
            ->select(['elements.id'])
            ->folderId($folderId)
            ->filename(Db::escapeParam($filename))
            ->exists();

        if ($exists) {
            $this->addLocationError($model, $attribute, Asset::ERROR_FILENAME_CONFLICT, $this->filenameConflict, ['filename' => $filename]);

            return;
        }

        // Check the volume to see if a file already exists at that location
        $path = ($folder->path ? rtrim($folder->path, '/').'/' : '').$filename;
        if ($folder->getVolume()->fileExists($path)) {
            $this->addLocationError($model, $attribute, Asset::ERROR_FILENAME_CONFLICT, $this->filenameConflict, ['filename' => $filename]);

            return;
        }

        // Update the newLocation attribute in case the filename changed
        $model->$attribute = "{folder:{$folderId}}{$filename}";
    }

    /**
     * Adds a location error to the model.
     *
     * @param Model  $model
     * @param string $attribute
     * @param string $errorCode
     * @param string $message
     * @param array  $params
     *
     * @return void
     */
    public function addLocationError(Model $model, string $attribute, string $errorCode, string $message, array $params = [])
    {
        $this->addError($model, $attribute, $message, $params);

        if ($this->errorCodeAttribute !== null) {
            $model->{$this->errorCodeAttribute} = $errorCode;
        }
    }
}
